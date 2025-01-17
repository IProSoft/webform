<?php

use Drupal\Core\Hook\Attribute\LegacyHook;
use Drupal\webform\Hook\WebformTranslationHooks;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\webform\Utility\WebformYaml;

/**
 * Implements hook_form_FORM_ID_alter() for language content settings form.
 */
#[LegacyHook]
function webform_form_language_content_settings_form_alter(array &$form, FormStateInterface $form_state)
{
    \Drupal::service(WebformTranslationHooks::class)->formLanguageContentSettingsFormAlter($form, $form_state);
}

/**
 * Implements hook_form_FORM_ID_alter() for locale translate edit form.
 */
#[LegacyHook]
function webform_form_locale_translate_edit_form_alter(&$form, FormStateInterface $form_state) {
  \Drupal::service(WebformTranslationHooks::class)->formLocaleTranslateEditFormAlter($form, $form_state);
}

/* ************************************************************************** */
// Configuration translation.
/* ************************************************************************** */

/**
 * Implements hook_form_FORM_ID_alter() for config translation add form.
 */
#[LegacyHook]
function webform_form_config_translation_add_form_alter(&$form, FormStateInterface $form_state, $is_new = TRUE)
{
    \Drupal::service(WebformTranslationHooks::class)->formConfigTranslationAddFormAlter($form, $form_state, $is_new);
}

/**
 * Implements hook_form_FORM_ID_alter() for config translation edit form.
 */
#[LegacyHook]
function webform_form_config_translation_edit_form_alter(&$form, FormStateInterface $form_state)
{
    \Drupal::service(WebformTranslationHooks::class)->formConfigTranslationEditFormAlter($form, $form_state);
}

/* ************************************************************************** */
// Lingotek integration.
/* ************************************************************************** */

/**
 * Implements hook_lingotek_config_entity_document_upload().
 */
#[LegacyHook]
function webform_lingotek_config_entity_document_upload(array &$source_data, ConfigEntityInterface &$entity, &$url)
{
    \Drupal::service(WebformTranslationHooks::class)->lingotekConfigEntityDocumentUpload($source_data, $entity, $url);
}

/**
 * Implements hook_lingotek_config_entity_translation_presave().
 */
#[LegacyHook]
function webform_lingotek_config_entity_translation_presave(ConfigEntityInterface &$translation, $langcode, &$data)
{
    \Drupal::service(WebformTranslationHooks::class)->lingotekConfigEntityTranslationPresave($translation, $langcode, $data);
}

/**
 * Implements hook_lingotek_config_object_document_upload().
 */
#[LegacyHook]
function webform_lingotek_config_object_document_upload(array &$data, $config_name)
{
    \Drupal::service(WebformTranslationHooks::class)->lingotekConfigObjectDocumentUpload($data, $config_name);
}

/**
 * Implements hook_lingotek_config_object_translation_presave().
 */
#[LegacyHook]
function webform_lingotek_config_object_translation_presave(array &$data, $config_name)
{
    \Drupal::service(WebformTranslationHooks::class)->lingotekConfigObjectTranslationPresave($data, $config_name);
}
