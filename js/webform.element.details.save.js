/**
 * @file
 * JavaScript behaviors for details element.
 */

(function ($, Drupal, once) {
  // Determine if local storage exists and is enabled.
  // This approach is copied from Modernizr.
  // @see https://github.com/Modernizr/Modernizr/blob/c56fb8b09515f629806ca44742932902ac145302/modernizr.js#L696-731
  const hasLocalStorage = (function () {
    try {
      localStorage.setItem('webform', 'webform');
      localStorage.removeItem('webform');
      return true;
    } catch (e) {
      return false;
    }
  })();

  /**
   * Attach handler to save details open/close state.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformDetailsSave = {
    attach(context) {
      if (!hasLocalStorage) {
        return;
      }

      // Summary click event handler.
      $(once('webform-details-summary-save', 'details > summary', context)).on(
        'click',
        function () {
          const $details = $(this).parent();

          // @see https://css-tricks.com/snippets/jquery/make-an-jquery-hasattr/
          if ($details[0].hasAttribute('data-webform-details-nosave')) {
            return;
          }

          const name = Drupal.webformDetailsSaveGetName($details);
          if (!name) {
            return;
          }

          const open = $details.attr('open') !== 'open' ? '1' : '0';
          localStorage.setItem(name, open);
        },
      );

      // Initialize details open state via local storage.
      $(once('webform-details-save', 'details', context)).each(function () {
        const $details = $(this);

        const name = Drupal.webformDetailsSaveGetName($details);
        if (!name) {
          return;
        }

        const open = localStorage.getItem(name);
        if (open === null) {
          return;
        }

        if (open === '1') {
          $details.attr('open', 'open');
        } else {
          $details.removeAttr('open');
        }
      });
    },
  };

  /**
   * Get the name used to store the state of details element.
   *
   * @param {jQuery} $details
   *   A details element.
   *
   * @return {string}
   *   The name used to store the state of details element.
   */
  Drupal.webformDetailsSaveGetName = function ($details) {
    if (!hasLocalStorage) {
      return '';
    }

    // Ignore details that are vertical tabs pane.
    if ($details.hasClass('vertical-tabs__pane')) {
      return '';
    }

    // Any details element not included a webform must have define its own id.
    const webformId = $details.attr('data-webform-element-id');
    if (webformId) {
      return `Drupal.webform.${webformId.replace('--', '.')}`;
    }

    let detailsId = $details.attr('id');
    if (!detailsId) {
      return '';
    }

    const $form = $details.parents('form');
    if (!$form.length || !$form.attr('id')) {
      return '';
    }

    let formId = $form.attr('id');
    if (!formId) {
      return '';
    }

    // ISSUE: When Drupal renders a webform in a modal dialog it appends a unique
    // identifier to webform ids and details ids. (i.e. my-form--FeSFISegTUI)
    // WORKAROUND: Remove the unique id that delimited using double dashes.
    formId = formId.replace(/--.+?$/, '').replace(/-/g, '_');
    detailsId = detailsId.replace(/--.+?$/, '').replace(/-/g, '_');
    return `Drupal.webform.${formId}.${detailsId}`;
  };
})(jQuery, Drupal, once);
