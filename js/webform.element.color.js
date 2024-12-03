/**
 * @file
 * JavaScript behaviors for color element integration.
 */

(function ($, Drupal, once) {
  /**
   * Enhance HTML5 color element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformColor = {
    attach(context) {
      $(
        once('webform-color', '.form-color:not(.form-color-output)', context),
      ).each(function () {
        const $element = $(this);
        // Display color input's output w/ visually-hidden label to
        // the end user.
        const $output = $(
          `<input class="form-color-output ${$element.attr(
            'class',
          )} js-webform-input-mask" data-inputmask-mask="\\#######" />`,
        ).uniqueId();
        const $label = $element
          .parent('.js-form-type-color')
          .find('label')
          .clone();
        const id = $output.attr('id');
        $label.attr({ for: id, class: 'visually-hidden' });
        if ($.fn.inputmask) {
          $output.inputmask();
        }
        $output[0].value = $element[0].value;
        $element.after($output).after($label).css({ float: 'left' });

        // Sync $element and $output.
        $element.on('input', function () {
          $output[0].value = $element[0].value;
        });
        $output.on('input', function () {
          $element[0].value = $output[0].value;
        });
      });
    },
  };
})(jQuery, Drupal, once);
