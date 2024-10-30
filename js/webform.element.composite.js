/**
 * @file
 * JavaScript behaviors for composite element builder.
 */

(function ($, Drupal, once) {
  /**
   * Initialize composite element builder.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformElementComposite = {
    attach(context) {
      $(once('webform-composite-types', '[data-composite-types]')).each(
        function () {
          const $element = $(this);
          const $type = $element
            .closest('tr')
            .find('.js-webform-composite-type');

          const types = $element.attr('data-composite-types').split(',');
          const required = $element.attr('data-composite-required');

          $type
            .on('change', function () {
              if ($.inArray($(this).val(), types) === -1) {
                $element.hide();
                if (required) {
                  $element.removeAttr('required aria-required');
                }
              } else {
                $element.show();
                if (required) {
                  $element.attr({
                    required: 'required',
                    'aria-required': 'true',
                  });
                }
              }
            })
            .trigger('change');
        },
      );
    },
  };
})(jQuery, Drupal, once);
