/**
 * @file
 * JavaScript behaviors for webform access.
 */

(function ($, Drupal) {
  /**
   * Initialize webform access group administer permission toggle.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformAccessGroupPermissions = {
    attach(context) {
      $(
        once('webform-access-group-permissions', '#edit-permissions', context),
      ).each(function () {
        const $permissions = $(this);
        const $checkbox = $permissions.find(
          'input[name="permissions[administer]"]',
        );

        $checkbox.on('click', toggleAdminister);
        if ($checkbox.prop('checked')) {
          toggleAdminister();
        }

        function toggleAdminister() {
          const checked = $checkbox.prop('checked');
          $permissions.find(':checkbox').prop('checked', checked);
          $permissions
            .find(':checkbox:not([name="permissions[administer]"])')
            .attr('disabled', checked);
        }
      });
    },
  };
})(jQuery, Drupal);
