/**
 * @file
 * JavaScript behaviors for confirmation modal.
 */

(function ($, Drupal, once) {
  // @see http://api.jqueryui.com/dialog/
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.confirmationModal = Drupal.webform.confirmationModal || {};
  Drupal.webform.confirmationModal.options =
    Drupal.webform.confirmationModal.options || {};

  /**
   * Display confirmation message in a modal.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformConfirmationModal = {
    attach(context) {
      $(
        once(
          'webform-confirmation-modal',
          '.js-webform-confirmation-modal',
          context,
        ),
      ).each(function () {
        const $element = $(this);

        const $dialog = $element.find('.webform-confirmation-modal--content');

        let options = {
          dialogClass: 'webform-confirmation-modal',
          minWidth: 600,
          resizable: false,
          title: $element.find('.webform-confirmation-modal--title').text(),
          close(event) {
            Drupal.dialog(event.target).close();
            Drupal.detachBehaviors(event.target, null, 'unload');
            $(event.target).remove();
          },
        };

        options = $.extend(options, Drupal.webform.confirmationModal.options);

        const dialog = Drupal.dialog($dialog, options);

        // Use setTimeout to prevent dialog.position.js
        // Uncaught TypeError: Cannot read property 'settings' of undefined
        setTimeout(function () {
          dialog.showModal();

          // Close any open webform submission modals.
          const $modal = $('#drupal-modal');
          if ($modal.find('.webform-submission-form').length) {
            Drupal.dialog($modal.get(0)).close();
          }
        }, 1);
      });
    },
  };
})(jQuery, Drupal, once);
