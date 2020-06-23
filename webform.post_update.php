<?php

/**
 * @file
 * Post update hooks for the Webform module.
 */

/**
 * Install jQuery UI modules needed for Drupal 9.x.
 */
function webform_post_update_install_jquery_ui_modules(&$sandbox) {
  \Drupal::service('module_installer')->install([
    'jquery_ui',
    'jquery_ui_datepicker',
    'jquery_ui_tabs',
    'jquery_ui_tooltip',
  ]);
}
