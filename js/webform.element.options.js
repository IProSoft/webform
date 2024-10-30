/**
 * @file
 * JavaScript behaviors for options elements.
 */

(function ($, Drupal) {
  /**
   * Attach handlers to options buttons element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformOptionsButtons = {
    attach(context) {
      // Place <input> inside of <label> before the label.
      $(context)
        .find(
          'label.webform-options-display-buttons-label > input[type="checkbox"], label.webform-options-display-buttons-label > input[type="radio"]',
        )
        .each(function () {
          const $input = $(this);
          const $label = $input.parent();
          $input.detach().insertBefore($label);
        });
    },
  };
})(jQuery, Drupal);
