<?php

namespace Drupal\webform_test_validate\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_test_validate.
 */
class WebformTestValidateHooks {

  /**
   * Implements hook_form_validate().
   */
  #[Hook('form_validate', module: 'webform_test_validate_form_webform_submission_test_form_validate')]
  public function formWebformSubmissionTestFormValidateFormValidate($form, FormStateInterface $form_state) {
    if (!$form_state->getValue('custom')) {
      $form_state->setErrorByName('custom', t('Custom element is required.'));
    }
  }

}
