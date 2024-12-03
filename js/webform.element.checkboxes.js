/**
 * @file
 * JavaScript behaviors for checkboxes.
 */

(function ($, Drupal, once) {
  /**
   * Adds check all or none checkboxes support.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/project/webform/issues/3068998
   */
  Drupal.behaviors.webformCheckboxesAllorNone = {
    attach(context) {
      $(
        once(
          'webform-checkboxes-all-or-none',
          '[data-options-all], [data-options-none]',
          context,
        ),
      ).each(function () {
        const $element = $(this);

        const optionsAllValue = $element.data('options-all');
        const optionsNoneValue = $element.data('options-none');

        // Get all checkboxes.
        const $checkboxes = $element.find('input[type="checkbox"]');

        // Get all options/checkboxes.
        const $options = $checkboxes
          .not(`[value="${optionsAllValue}"]`)
          .not(`[value="${optionsNoneValue}"]`);

        // Get options all and none checkboxes.
        const $optionsAll = $element.find(
          `:checkbox[value="${optionsAllValue}"]`,
        );
        const $optionsNone = $element.find(
          `:checkbox[value="${optionsNoneValue}"]`,
        );

        // All of the above.
        if ($optionsAll.length) {
          $optionsAll.on('click', toggleCheckAllEventHandler);
          if ($optionsAll.prop('checked')) {
            toggleCheckAllEventHandler();
          }
        }

        // None of the above.
        if ($optionsNone.length) {
          $optionsNone.on('click', toggleCheckNoneEventHandler);
          toggleCheckNoneEventHandler();
        }

        $options.on('click', toggleCheckboxesEventHandler);
        toggleCheckboxesEventHandler();

        /**
         * Toggle check all checkbox checked state.
         */
        function toggleCheckAllEventHandler() {
          if ($optionsAll.prop('checked')) {
            // Uncheck options none.
            if ($optionsNone.is(':checked')) {
              $optionsNone
                .prop('checked', false)
                .trigger('change', ['webform.states']);
            }
            // Check check all unchecked options.
            $options
              .not(':checked')
              .prop('checked', true)
              .trigger('change', ['webform.states']);
          } else {
            // Check uncheck all checked options.
            $options
              .filter(':checked')
              .prop('checked', false)
              .trigger('change', ['webform.states']);
          }
        }

        /**
         * Toggle check none checkbox checked state.
         */
        function toggleCheckNoneEventHandler() {
          if ($optionsNone.prop('checked')) {
            $checkboxes
              .not(`[value="${optionsNoneValue}"]`)
              .filter(':checked')
              .prop('checked', false)
              .trigger('change', ['webform.states']);
          }
        }

        /**
         * Toggle check all checkbox checked state.
         */
        function toggleCheckboxesEventHandler() {
          const isAllChecked =
            $options.filter(':checked').length === $options.length;
          if (
            $optionsAll.length &&
            $optionsAll.prop('checked') !== isAllChecked
          ) {
            $optionsAll
              .prop('checked', isAllChecked)
              .trigger('change', ['webform.states']);
          }
          const isOneChecked = $options.is(':checked');
          if ($optionsNone.length && isOneChecked) {
            $optionsNone
              .prop('checked', false)
              .trigger('change', ['webform.states']);
          }
        }
      });
    },
  };
})(jQuery, Drupal, once);
