<?php

/**
 * @file
 */

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\webform_bootstrap_test_module\Hook\WebformBootstrapTestModuleHooks1;

/**
 * @file
 * Alter hooks to increase webform input and button size.
 */

/**
 * Implements hook_webform_submission_form_alter().
 */
#[LegacyHook]
function webform_bootstrap_test_module_webform_submission_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  \Drupal::service(WebformBootstrapTestModuleHooks1::class)->webformSubmissionFormAlter($form, $form_state, $form_id);
}

/**
 * Implements hook_webform_element_alter().
 */
#[LegacyHook]
function webform_bootstrap_test_module_webform_element_alter(array &$element, FormStateInterface $form_state, array $context) {
  return \Drupal::service(WebformBootstrapTestModuleHooks1::class)->webformElementAlter($element, $form_state, $context);
}

/**
 * Implements hook_preprocess_webform_actions().
 *
 * @see template_preprocess_webform_actions()
 */
function webform_bootstrap_preprocess_webform_actions(array &$variables) {
  // Add .btn-lg to all 'submit' button in $variables.
  foreach (Element::children($variables['element']) as $key) {
    $variables['element'][$key]['#attributes']['class'][] = 'btn-lg';
    $variables['element'][$key]['#attributes']['style'] = 'margin: 24px 0 0 0';

    $variables[$key]['#attributes']['class'][] = 'btn-lg';
    $variables[$key]['#attributes']['#attributes']['style'] = 'margin: 24px 0 0 0';
  }
}
