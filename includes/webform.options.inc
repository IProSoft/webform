<?php

/**
 * @file
 */

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\webform\Hook\WebformOptionsHooks;

/**
 * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for range options.
 *
 * @see config/install/webform.webform.example_options.yml
 */
#[LegacyHook]
function webform_webform_options_range_alter(array &$options, array $element = []) {
  \Drupal::service(WebformOptionsHooks::class)->webformOptionsRangeAlter($options, $element);
}

/**
 * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for time zones options.
 */
#[LegacyHook]
function webform_webform_options_time_zones_alter(array &$options, array $element = []) {
  \Drupal::service(WebformOptionsHooks::class)->webformOptionsTimeZonesAlter($options, $element);
}

/**
 * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for country codes options.
 */
#[LegacyHook]
function webform_webform_options_country_codes_alter(array &$options, array $element = []) {
  \Drupal::service(WebformOptionsHooks::class)->webformOptionsCountryCodesAlter($options, $element);
}

/**
 * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for country names options.
 */
#[LegacyHook]
function webform_webform_options_country_names_alter(array &$options, array $element = []) {
  \Drupal::service(WebformOptionsHooks::class)->webformOptionsCountryNamesAlter($options, $element);
}

/**
 * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for languages options.
 */
#[LegacyHook]
function webform_webform_options_languages_alter(array &$options, array $element = []) {
  \Drupal::service(WebformOptionsHooks::class)->webformOptionsLanguagesAlter($options, $element);
}

/**
 * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for translations options.
 */
#[LegacyHook]
function webform_webform_options_translations_alter(array &$options, array $element = []) {
  \Drupal::service(WebformOptionsHooks::class)->webformOptionsTranslationsAlter($options, $element);
}
