<?php

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\webform\Hook\WebformFormAlterHooks;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Element\WebformHtmlEditor;
use Drupal\webform\Element\WebformMessage;
use Drupal\webform\WebformSubmissionForm;

/**
 * Implements hook_form_alter().
 */
#[LegacyHook]
function webform_form_alter(&$form, FormStateInterface $form_state, $form_id)
{
    return \Drupal::service(WebformFormAlterHooks::class)->formAlter($form, $form_state, $form_id);
}

/**
 * Alter webform after build.
 */
function _webform_form_webform_submission_form_after_build($form, FormStateInterface $form_state) {
  $form_object = $form_state->getFormObject();

  /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
  $webform_submission = $form_object->getEntity();
  $webform = $webform_submission->getWebform();

  // Add contextual links and change theme wrapper to webform.html.twig
  // which includes 'title_prefix' and 'title_suffix' variables needed for
  // contextual links to appear.
  $form['#contextual_links']['webform'] = [
    'route_parameters' => ['webform' => $webform->id()],
  ];
  $form['#theme_wrappers'] = ['webform'];
  return $form;
}

/* ************************************************************************** */
// Update manager.
/* ************************************************************************** */

/**
 * Implements hook_form_FORM_ID_alter() for update manager update form.
 *
 * Add warnings when attempting to update the Webform module using
 * the 'Update manager'.
 *
 * @see https://www.drupal.org/project/webform/issues/2930116
 * @see https://www.drupal.org/project/webform/issues/2920095
 */
#[LegacyHook]
function webform_form_update_manager_update_form_alter(&$form, FormStateInterface $form_state)
{
    return \Drupal::service(WebformFormAlterHooks::class)->formUpdateManagerUpdateFormAlter($form, $form_state);
}

/* ************************************************************************** */
// Views.
/* ************************************************************************** */

/**
 * Implements hook_form_FORM_ID_alter() for views exposed form.
 */
#[LegacyHook]
function webform_form_views_exposed_form_alter(&$form, FormStateInterface $form_state, $form_id)
{
    \Drupal::service(WebformFormAlterHooks::class)->formViewsExposedFormAlter($form, $form_state, $form_id);
}

/* ************************************************************************** */
// SMTP.
/* ************************************************************************** */

/**
 * Implements hook_form_FORM_ID_alter() for SMTP admin settings form.
 */
#[LegacyHook]
function webform_form_smtp_admin_settings_alter(&$form, FormStateInterface $form_state) {
  \Drupal::service(WebformFormAlterHooks::class)->formSmtpAdminSettingsAlter($form, $form_state);
}

/**
 * Submit callback for SMTP admin settings.
 */
function _webform_form_smtp_admin_settings_submit(&$form, FormStateInterface $form_state) {
  // Since the SMTP module can enabled or disabled, check HTML email provider.
  /** @var \Drupal\webform\WebformEmailProviderInterface $email_provider */
  $email_provider = \Drupal::service('webform.email_provider');
  $email_provider->check();
}

/* ************************************************************************** */
// Configuration management.
/* ************************************************************************** */

/**
 * Implements hook_form_FORM_ID_alter() for config single import form.
 */
#[LegacyHook]
function webform_form_config_single_import_form_alter(&$form, FormStateInterface $form_state)
{
    \Drupal::service(WebformFormAlterHooks::class)->formConfigSingleImportFormAlter($form, $form_state);
}
