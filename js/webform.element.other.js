/**
 * @file
 * JavaScript behaviors for other elements.
 */

(function ($, Drupal, once) {
  /**
   * Toggle other input (text) field.
   *
   * @param {boolean} show
   *   TRUE will display the text field. FALSE with hide and clear the text field.
   * @param {object} $element
   *   The input (text) field to be toggled.
   * @param {string} effect
   *   Effect.
   */
  function toggleOther(show, $element, effect) {
    const $input = $element.find('input');
    const hideEffect = effect === false ? 'hide' : 'slideUp';
    const showEffect = effect === false ? 'show' : 'slideDown';

    if (show) {
      // Limit the other inputs width to the parent's container.
      // If the parent container is not visible it's width will be 0
      // and ignored.
      const width = $element.parent().width();
      if (width) {
        $element.width(width);
      }

      // Display the element.
      $element[showEffect]();
      // If not initializing, then focus the other element.
      if (effect !== false) {
        $input.trigger('focus');
      }
      // Require the input.
      $input.prop('required', true).attr('aria-required', 'true');
      // Restore the input's value.
      const value = $input.data('webform-value');
      if (typeof value !== 'undefined') {
        $input.val(value);
        const input = $input.get(0);
        // Move cursor to the beginning of the other text input.
        // @see https://stackoverflow.com/questions/21177489/selectionstart-selectionend-on-input-type-number-no-longer-allowed-in-chrome
        if (
          $.inArray(input.type, [
            'text',
            'search',
            'url',
            'tel',
            'password',
          ]) !== -1
        ) {
          input.setSelectionRange(0, 0);
        }
      }
      // Refresh CodeMirror used as other element.
      $element
        .parent()
        .find('.CodeMirror')
        .each(function (index, $element) {
          $element.CodeMirror.refresh();
        });
    } else {
      // Hide the element.
      $element[hideEffect]();
      // Save the input's value.
      if ($input.val() !== '') {
        $input.data('webform-value', $input.val());
      }
      // Empty and un-required the input.
      $input.val('').prop('required', false).removeAttr('aria-required');
    }
  }

  /**
   * Attach handlers to select other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformSelectOther = {
    attach(context) {
      $(once('webform-select-other', '.js-webform-select-other', context)).each(
        function () {
          const $element = $(this);

          const $select = $element.find('select');
          const $input = $element.find('.js-webform-select-other-input');

          $select.on('change', function () {
            const isOtherSelected = $select
              .find('option[value="_other_"]')
              .is(':selected');
            toggleOther(isOtherSelected, $input);
          });

          const isOtherSelected = $select
            .find('option[value="_other_"]')
            .is(':selected');
          toggleOther(isOtherSelected, $input, false);
        },
      );
    },
  };

  /**
   * Attach handlers to checkboxes other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformCheckboxesOther = {
    attach(context) {
      $(
        once(
          'webform-checkboxes-other',
          '.js-webform-checkboxes-other',
          context,
        ),
      ).each(function () {
        const $element = $(this);
        const $checkbox = $element.find('input[value="_other_"]');
        const $input = $element.find('.js-webform-checkboxes-other-input');

        $checkbox.on('change', function () {
          toggleOther(this.checked, $input);
        });

        toggleOther($checkbox.is(':checked'), $input, false);
      });
    },
  };

  /**
   * Attach handlers to radios other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformRadiosOther = {
    attach(context) {
      $(once('webform-radios-other', '.js-webform-radios-other', context)).each(
        function () {
          const $element = $(this);

          const $radios = $element.find('input[type="radio"]');
          const $input = $element.find('.js-webform-radios-other-input');

          $radios.on('change', function () {
            toggleOther($radios.filter(':checked').val() === '_other_', $input);
          });

          toggleOther(
            $radios.filter(':checked').val() === '_other_',
            $input,
            false,
          );
        },
      );
    },
  };

  /**
   * Attach handlers to buttons other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformButtonsOther = {
    attach(context) {
      $(
        once('webform-buttons-other', '.js-webform-buttons-other', context),
      ).each(function () {
        const $element = $(this);

        const $buttons = $element.find('input[type="radio"]');
        const $input = $element.find('.js-webform-buttons-other-input');
        const $container = $(this).find('.js-webform-webform-buttons');

        // Create set onchange handler.
        $container.on('change', function () {
          toggleOther(
            $(this).find(':radio:checked').val() === '_other_',
            $input,
          );
        });

        toggleOther(
          $buttons.filter(':checked').val() === '_other_',
          $input,
          false,
        );
      });
    },
  };
})(jQuery, Drupal, once);
