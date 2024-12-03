/**
 * @file
 * JavaScript behaviors for webform wizard.
 */

(function ($, Drupal, once) {
  /**
   * Tracks the wizard's current page in the URL.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Tracks the wizard's current page in the URL.
   */
  Drupal.behaviors.webformWizardTrackPage = {
    attach(context) {
      // Make sure on page load or Ajax refresh the browser's URL ?page= query
      // parameter is correct since conditional logic can skip pages.
      // Note: window.history is only supported by IE 10+.
      if (window.history && window.history.replaceState) {
        const $form = $(context).hasData('webform-wizard-current-page')
          ? $(context)
          : $(context).find('[data-webform-wizard-current-page]');
        // Make sure the form is visible before updating the URL.
        if ($form.length && $form.is(':visible')) {
          // Append the form's current page data attribute to the browser's URL.
          const page = $form.attr('data-webform-wizard-current-page');
          const url = setUrlPageParameter(window.location.toString(), page);
          window.history.replaceState(null, null, url);
        }
      }

      // When paging next and back update the URL so that Drupal knows what
      // the expected page name or index is going to be.
      // NOTE: If conditional wizard page logic is configured the
      // expected page name or index may not be accurate but the above code
      // uses window.history.replaceState to update the browser's URL.
      $(
        once(
          'webform-wizard-page',
          $(
            ':button[data-webform-wizard-page], :submit[data-webform-wizard-page]',
            context,
          ),
        ),
      ).on('click', function () {
        const page = $(this).attr('data-webform-wizard-page');
        this.form.action = setUrlPageParameter(this.form.action, page);
      });

      /**
       * Set URLs page parameter.
       *
       * @param {string} url
       *   A url.
       * @param {string} page
       *   The current page.
       *
       * @return {string}
       *   A URL with page parameter.
       */
      function setUrlPageParameter(url, page) {
        const regex = /([?&])page=[^?&]+/;
        if (url.match(regex)) {
          return url.replace(regex, `$1page=${page}`);
        }

        return url + (url.indexOf('?') !== -1 ? '&page=' : '?page=') + page;
      }
    },
  };
})(jQuery, Drupal, once);
