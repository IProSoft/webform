/**
 * @file
 * JavaScript behaviors for terms of service.
 */

(function ($, Drupal, once) {
  // @see http://api.jqueryui.com/dialog/
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.termsOfServiceModal = Drupal.webform.termsOfServiceModal || {};
  Drupal.webform.termsOfServiceModal.options =
    Drupal.webform.termsOfServiceModal.options || {};

  /**
   * Initialize terms of service element.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformTermsOfService = {
    attach(context) {
      $(
        once(
          'webform-terms-of-service',
          '.js-form-type-webform-terms-of-service',
          context,
        ),
      ).each(function () {
        const $element = $(this);
        const $a = $element.find('label a');
        const $details = $element.find('.webform-terms-of-service-details');

        const type = $element.attr('data-webform-terms-of-service-type');

        // Initialize the modal.
        if (type === 'modal') {
          // Move details title to attribute.
          const $title = $element.find(
            '.webform-terms-of-service-details--title',
          );
          if ($title.length) {
            $details.attr('title', $title.text());
            $title.remove();
          }

          const options = $.extend(
            {
              modal: true,
              autoOpen: false,
              minWidth: 600,
              maxWidth: 800,
            },
            Drupal.webform.termsOfServiceModal.options,
          );
          $details.dialog(options);
        }

        // Add aria-* attributes.
        if (type !== 'modal') {
          $a.attr({
            'aria-expanded': false,
            'aria-controls': $details.attr('id'),
          });
        }

        // Set event handlers.
        $a.on('click', openDetails).on('keydown', function (event) {
          // Space or Return.
          if (event.which === 32 || event.which === 13) {
            openDetails(event);
          }
        });

        function openDetails(event) {
          if (type === 'modal') {
            $details.dialog('open');
          } else {
            const expanded = $a.attr('aria-expanded') === 'true';

            // Toggle `aria-expanded` attributes on link.
            $a.attr('aria-expanded', !expanded);

            // Toggle details.
            $details[expanded ? 'slideUp' : 'slideDown']();
          }
          event.preventDefault();
        }
      });
    },
  };
})(jQuery, Drupal, once);
