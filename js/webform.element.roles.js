/**
 * @file
 * JavaScript behaviors for roles element integration.
 */

(function ($, Drupal, once) {

  'use strict';

  /**
   * Enhance roles element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformRoles = {
    attach: function (context) {
      $(once('webform-roles', '.js-webform-roles-role[value="authenticated"]', context)).each(function () {
        var $authenticated = $(this);
        var $checkboxes = $authenticated.parents('.form-checkboxes').find('.js-webform-roles-role').filter(function () {
          return ($(this).val() !== 'anonymous' && $(this).val() !== 'authenticated');
        });

        $authenticated.on('click', function () {
          if ($authenticated.is(':checked')) {
            $checkboxes.prop('checked', TRUE).attr('disabled', TRUE);
          }
          else {
            $checkboxes.prop('checked', FALSE).removeAttr('disabled');
          }
        });

        if ($authenticated.is(':checked')) {
          $checkboxes.prop('checked', TRUE).attr('disabled', TRUE);
        }
      });
    }
  };

})(jQuery, Drupal, once);
