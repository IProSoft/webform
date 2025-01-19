<?php

namespace Drupal\webform_test_alter_hooks\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_test_alter_hooks.
 */
class WebformTestAlterHooksHooks {
  /* ************************************************************************** */
  // Form hooks.
  /* ************************************************************************** */

  /**
   * Implements hook_form_alter().
   */
  #[Hook('form_alter')]
  public function formAlter(&$form, FormStateInterface $form_state, $form_id) {
    if (strpos($form_id, 'webform_') === 0) {
      \Drupal::messenger()->addStatus(t("@hook: '@form_id' executed.", ['@hook' => 'hook_form_alter()', '@form_id' => $form_id]), TRUE);
    }
  }

  /**
   * Implements hook_form_webform_submission_form_alter().
   */
  #[Hook('form_webform_submission_form_alter')]
  public function formWebformSubmissionFormAlter(array $form, FormStateInterface $form_state, $form_id) {
    \Drupal::messenger()->addStatus(t("@hook: '@form_id' executed.", ['@hook' => 'hook_form_webform_submission_form_alter()', '@form_id' => $form_id]), TRUE);
  }

  /**
   * Implements hook_form_webform_submission_BASE_FORM_ID_form_alter().
   *
   * @see webform_form_alter()
   * @see \Drupal\webform\WebformSubmissionForm::getBaseFormId
   * @see \Drupal\Core\Form\FormBuilder::prepareForm
   */
  #[Hook('form_webform_submission_contact_form_alter')]
  public function formWebformSubmissionContactFormAlter(array $form, FormStateInterface $form_state, $form_id) {
    \Drupal::messenger()->addStatus(t("@hook: '@form_id' executed.", [
      '@hook' => 'hook_form_webform_submission_BASE_FORM_ID_form_alter()',
      '@form_id' => $form_id,
    ]), TRUE);
  }

  /**
   * Implements hook_form_webform_submission_FORM_ID_form_alter().
   *
   * @see webform_form_alter()
   * @see \Drupal\webform\WebformSubmissionForm::getFormId
   * @see \Drupal\Core\Form\FormBuilder::prepareForm
   */
  #[Hook('form_webform_submission_contact_add_form_alter')]
  public function formWebformSubmissionContactAddFormAlter(array $form, FormStateInterface $form_state, $form_id) {
    \Drupal::messenger()->addStatus(t("@hook: '@form_id' executed.", [
      '@hook' => 'hook_form_webform_submission_FORM_ID_form_alter()',
      '@form_id' => $form_id,
    ]), TRUE);
  }

  /**
   * Implements hook_form_webform_submission_FORM_ID_form_alter().
   *
   * @see webform_form_alter()
   * @see \Drupal\webform\WebformSubmissionForm::getFormId
   * @see \Drupal\Core\Form\FormBuilder::prepareForm
   */
  #[Hook('form_webform_submission_contact_node_1_add_form_alter')]
  public function formWebformSubmissionContactNode1AddFormAlter(array $form, FormStateInterface $form_state, $form_id) {
    \Drupal::messenger()->addStatus(t("@hook: '@form_id' executed.", [
      '@hook' => 'hook_form_webform_submission_FORM_ID_form_alter()',
      '@form_id' => $form_id,
    ]), TRUE);
  }

  /**
   * Implements hook_webform_submission_form_alter().
   *
   * @see \Drupal\webform\WebformSubmissionForm::buildForm
   */
  #[Hook('webform_submission_form_alter')]
  public function webformSubmissionFormAlter(array &$form, FormStateInterface $form_state, $form_id) {
    \Drupal::messenger()->addStatus(t("@hook: '@form_id' executed.", ['@hook' => 'hook_webform_submission_form_alter()', '@form_id' => $form_id]), TRUE);
  }

  /* ************************************************************************** */
  // Element hooks.
  /* ************************************************************************** */

  /**
   * Implements hook_webform_element_alter().
   *
   * @see webform.api.php
   * @see \Drupal\webform\WebformSubmissionForm::prepareElements
   */
  #[Hook('webform_element_alter')]
  public function webformElementAlter(array &$element, FormStateInterface $form_state, array $context) {
    \Drupal::messenger()->addStatus(t("@hook: '@webform_key' executed.", [
      '@hook' => 'hook_webform_element_alter()',
      '@webform_key' => $element['#webform_key'],
    ]), TRUE);
  }

  /**
   * Implements hook_webform_element_ELEMENT_TYPE_alter().
   *
   * @see webform.api.php
   * @see \Drupal\webform\WebformSubmissionForm::prepareElements
   */
  #[Hook('webform_element_email_alter')]
  public function webformElementEmailAlter(array &$element, FormStateInterface $form_state, array $context) {
    \Drupal::messenger()->addStatus(t("@hook: '@webform_key' executed.", [
      '@hook' => 'hook_webform_element_ELEMENT_TYPE_alter()',
      '@webform_key' => $element['#webform_key'],
    ]), TRUE);
  }

}
