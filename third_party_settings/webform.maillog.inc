<?php

/**
 * @file
 */

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\webform\Hook\WebformMaillogHooks;
use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_webform_submission_form_alter().
 */
#[LegacyHook]
function maillog_webform_submission_form_alter(array &$form, FormStateInterface $form_state, $form_id) {
  return \Drupal::service(WebformMaillogHooks::class)->maillogWebformSubmissionFormAlter($form, $form_state, $form_id);
}
