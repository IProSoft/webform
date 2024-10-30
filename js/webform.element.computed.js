/**
 * @file
 * JavaScript behaviors for computed elements.
 */

(function ($, Drupal, once) {
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.computed = Drupal.webform.computed || {};
  Drupal.webform.computed.delay = Drupal.webform.computed.delay || 500;

  const computedElements = [];

  /**
   * Initialize computed elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformComputed = {
    attach(context) {
      // Find computed elements and build trigger selectors.
      $(once('webform-computed', '.js-webform-computed', context)).each(
        function () {
          // Get computed element and form.
          const $element = $(this);
          const $form = $element.closest('form');

          // Get unique id for computed element based on the element name
          // and form id.
          const id = `${$form.attr('id')}-${$element.find('input[type="hidden"]').attr('name')}`;

          // Get elements that are used by the computed element.
          const elementKeys = $(this).data('webform-element-keys').split(',');
          if (!elementKeys) {
            return;
          }

          // Get computed element trigger selectors.
          const inputs = [];
          $.each(elementKeys, function (i, key) {
            // Exact input match.
            inputs.push(`:input[name="${key}"]`);
            // Sub inputs. (aka #tree)
            inputs.push(`:input[name^="${key}["]`);
          });
          const triggers = inputs.join(',');

          // Track computed elements.
          computedElements.push({
            id,
            element: $element,
            form: $form,
            triggers,
          });

          // Clear computed last values to ensure that a computed element is
          // always re-computed on page load.
          $element.attr('data-webform-computed-last', '');
        },
      );

      // Initialize triggers for each computed element.
      $.each(computedElements, function (index, computedElement) {
        // Get trigger from the current context.
        let $triggers = $(context).find(computedElement.triggers);
        // Make sure current context has triggers.
        if (!$triggers.length) {
          return;
        }

        // Make sure triggers are within the computed element's form
        // and only initialized once.
        $triggers = $(
          once(
            `webform-computed-triggers-${computedElement.id}`,
            computedElement.form.find($triggers),
          ),
        );
        // Double check that there are triggers which need to be initialized.
        if (!$triggers.length) {
          return;
        }

        initializeTriggers(computedElement.element, $triggers);
      });

      /**
       * Initialize computed element triggers.
       *
       * @param {jQuery} $element
       *   An jQuery object containing the computed element.
       * @param {jQuery} $triggers
       *   An jQuery object containing the computed element triggers.
       */
      function initializeTriggers($element, $triggers) {
        // Add event handler to computed element triggers.
        $triggers.on('keyup change', queueUpdate);

        // Add event handler to computed element tabledrag.
        const $draggable = $triggers.closest('tr.draggable');
        if ($draggable.length) {
          $draggable
            .find('.tabledrag-handle')
            .on('mouseup pointerup touchend', queueUpdate);
        }

        // Queue an update to make sure trigger values are computed.
        queueUpdate();

        // Queue computed element updates using a timer.
        let timer = null;
        function queueUpdate() {
          if (timer) {
            window.clearTimeout(timer);
            timer = null;
          }
          timer = window.setTimeout(
            triggerUpdate,
            Drupal.webform.computed.delay,
          );
        }

        function triggerUpdate() {
          // Get computed element wrapper.
          const $wrapper = $element.find('.js-webform-computed-wrapper');

          // If computed element is loading, requeue the update and wait for
          // the computed element to be updated.
          if ($wrapper.hasClass('webform-computed-loading')) {
            queueUpdate();
            return;
          }

          // Prevent duplicate computations.
          // @see Drupal.behaviors.formSingleSubmit
          const formValues = $triggers.serialize();
          const previousValues = $element.attr('data-webform-computed-last');
          if (previousValues === formValues) {
            return;
          }
          $element.attr('data-webform-computed-last', formValues);

          // Add loading class to computed wrapper.
          $wrapper.addClass('webform-computed-loading');

          // Trigger computation.
          $element.find('.js-form-submit').trigger('mousedown');
        }
      }
    },
  };
})(jQuery, Drupal, once);
