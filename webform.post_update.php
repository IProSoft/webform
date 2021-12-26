<?php

/**
 * @file
 * Post update functions for Webform module.
 */

/**
 * #3254570: Move jQuery UI datepicker support into dedicated deprecated module.
 */
function webform_post_update_deprecate_jquery_ui_datepicker() {
  if (!\Drupal::moduleHandler()->moduleExists('jquery_ui_datepicker')) {
    return;
  }

  $config_factory = \Drupal::configFactory();
  foreach ($config_factory->listAll('webform.webform.') as $webform_config_name) {
    $webform_config = $config_factory->get($webform_config_name);
    $elements = $webform_config->get('elements');
    if (strpos($elements, 'datepicker') !== FALSE) {
      // Enable the webform_jqueryui_datepicker.module.
      \Drupal::service('module_installer')
        ->install(['webform_jqueryui_datepicker']);
      return;
    }
  }
}
