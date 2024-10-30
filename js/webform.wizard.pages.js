/**
 * @file
 * JavaScript behaviors for webform wizard pages.
 */

(function ($, Drupal, once) {
  /**
   * Link the wizard's previous pages.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Links the wizard's previous pages.
   */
  Drupal.behaviors.webformWizardPagesLink = {
    attach(context) {
      $(
        once(
          'webform-wizard-pages-links',
          '.js-webform-wizard-pages-links',
          context,
        ),
      ).each(function () {
        const $pages = $(this);
        const $form = $pages.closest('form');

        const hasProgressLink = $pages.data('wizard-progress-link');
        const hasPreviewLink = $pages.data('wizard-preview-link');

        $pages.find('.js-webform-wizard-pages-link').each(function () {
          const $button = $(this);
          const title = $button.attr('title');
          const page = $button.data('webform-page');

          // Link progress marker and title.
          if (hasProgressLink) {
            const $progress = $form.find(
              `.webform-progress [data-webform-page="${page}"]`,
            );
            $progress
              .find(
                '.progress-marker, .progress-title, .webform-progress-bar__page-title',
              )
              .attr({
                role: 'link',
                title,
                'aria-label': title,
                tabindex: '0',
              })
              .on('click', function () {
                $button.trigger('click');
              })
              .on('keydown', function (event) {
                if (event.which === 13) {
                  $button.trigger('click');
                }
              });
            // Only allow the marker to be tabbable.
            $progress
              .find('.progress-marker, .webform-progress-bar__page-title')
              .attr('tabindex', 0);
          }

          // Move button to preview page div container with [data-webform-page].
          // @see \Drupal\webform\Plugin\WebformElement\WebformWizardPage::formatHtmlItem
          if (hasPreviewLink) {
            $form
              .find(`.webform-preview [data-webform-page="${page}"]`)
              .append($button)
              .show();
          }
        });
      });
    },
  };
})(jQuery, Drupal, once);
