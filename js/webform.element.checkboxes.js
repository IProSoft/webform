/**
 * @file
 * JavaScript behaviors for checkboxes.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Adds check all or none checkboxes support.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/project/webform/issues/3068998
   */
  Drupal.behaviors.webformCheckboxesAllorNone = {
    attach: function (context) {
      $('[data-options-all], [data-options-none]', context)
        .once('webform-checkboxes-all-or-none')
        .each(function () {
          var $element = $(this);

          var options_all_value = $element.data('options-all');
          var options_none_value = $element.data('options-none');

          // Get all checkboxes.
          var $checkboxes = $element.find('input[type="checkbox"]');

          // Get all options/checkboxes.
          var $options = $checkboxes
            .not('[value="' + options_all_value + '"]')
            .not('[value="' + options_none_value + '"]');

          // Get options all and none checkboxes.
          var $options_all = $element
            .find(':checkbox[value="' + options_all_value + '"]');
          var $options_none = $element
            .find(':checkbox[value="' + options_none_value + '"]');

          // All of the above.
          if ($options_all.length) {
            $options_all.on('click', toggleCheckAllEventHandler);
            toggleCheckAllEventHandler();
          }

          // None of the above.
          if ($options_none.length) {
            $options_none.on('click', toggleCheckNoneEventHandler);
            toggleCheckNoneEventHandler();
          }

          $options.on('click', toggleCheckboxesEventHandler);
          toggleCheckboxesEventHandler();

          /**
           * Toggle check all checkbox checked state.
           */
          function toggleCheckAllEventHandler() {
            if ($options_all.prop('checked')) {
              // Uncheck options none.
              if ($options_none.is(':checked')) {
                $options_none
                  .prop('checked', false)
                  .trigger('change', ['webform.states']);
              }
              // Check check all unchecked options.
              $options.not(':checked')
                .prop('checked', true)
                .trigger('change', ['webform.states']);
            }
            else {
              // Check uncheck all checked options.
              $options.filter(':checked')
                .prop('checked', false)
                .trigger('change', ['webform.states']);
            }
          }

          /**
           * Toggle check none checkbox checked state.
           */
          function toggleCheckNoneEventHandler() {
            if ($options_none.prop('checked')) {
              $checkboxes
                .not('[value="' + options_none_value + '"]')
                .filter(':checked')
                .prop('checked', false)
                .trigger('change', ['webform.states']);
            }
          }

          /**
           * Toggle check all checkbox checked state.
           */
          function toggleCheckboxesEventHandler() {
            var checkOne = false;
            var checkedAll = true;
            $options.each(function () {
              if (this.checked) {
                checkOne = true;
              }
              else {
                checkedAll = false;
              }
            });
            if ($options_all.length
              && $options_all.prop('checked') !== checkedAll) {
              $options_all
                .prop('checked', checkedAll)
                .trigger('change', ['webform.states']);
            }
            if ($options_none.length
              && $options_none.prop('checked') !== checkOne) {
              $options_none
                .prop('checked', checkedOne)
                .trigger('change', ['webform.states']);
            }
          }
        });
    }
  };

  /**
   * Adds HTML5 validation to required checkboxes.
   *
   * @type {Drupal~behavior}
   *
   * @see https://www.drupal.org/project/webform/issues/3068998
   */
  Drupal.behaviors.webformCheckboxesRadiosRequired = {
    attach: function (context) {
      $('.js-webform-type-checkboxes.required, .js-webform-type-webform-radios-other.checkboxes', context)
        .once('webform-checkboxes-required')
        .each(function () {
          var $element = $(this);
          var $firstCheckbox = $element
            .find('input[type="checkbox"]')
            .first();

          // Copy clientside_validation.module's message to the checkboxes.
          if ($element.attr('data-msg-required')) {
            $firstCheckbox.attr('data-msg-required', $element.attr('data-msg-required'));
          }

          $element.find('input[type="checkbox"]').on('click', required);
          required();

          function required() {
            var isChecked = $element.find('input[type="checkbox"]:checked');
            if (isChecked.length) {
              $firstCheckbox
                .removeAttr('required')
                .removeAttr('aria-required');
            }
            else {
              $firstCheckbox.attr({
                'required': 'required',
                'aria-required': 'true'
              });
            }
          }
        });
    }
  };

})(jQuery, Drupal);
