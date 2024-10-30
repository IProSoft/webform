/**
 * @file
 * JavaScript behaviors for multiple element.
 */

(function ($, Drupal, once) {
  /**
   * Move show weight to after the table.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformMultipleTableDrag = {
    attach(context, settings) {
      for (const base in settings.tableDrag) {
        if (settings.tableDrag.hasOwnProperty(base)) {
          $(
            once(
              'webform-multiple-table-drag',
              `.js-form-type-webform-multiple #${base}`,
              context,
            ),
          ).each(function () {
            const $tableDrag = $(this);
            const $toggleWeight = $tableDrag
              .prev()
              .prev('.tabledrag-toggle-weight-wrapper');
            if ($toggleWeight.length) {
              $toggleWeight.addClass(
                'webform-multiple-tabledrag-toggle-weight',
              );
              $tableDrag.after($toggleWeight);
            }
          });
        }
      }
    },
  };

  /**
   * Submit multiple add number input value when enter is pressed.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformMultipleAdd = {
    attach(context, settings) {
      $(once('webform-multiple-add', '.js-webform-multiple-add', context)).each(
        function () {
          const $submit = $(this).find('input[type="submit"], button');
          const $number = $(this).find('input[type="number"]');
          $number.keyup(function (event) {
            if (event.which === 13) {
              // Note: Mousedown is the default trigger for Ajax events.
              // @see Drupal.Ajax.
              $submit.trigger('mousedown');
            }
          });
        },
      );
    },
  };
})(jQuery, Drupal, once);
