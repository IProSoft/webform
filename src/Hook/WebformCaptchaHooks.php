<?php

namespace Drupal\webform\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Utility\WebformElementHelper;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform.
 */
class WebformCaptchaHooks {

  /**
   * Implements hook_webform_admin_third_party_settings_form_alter().
   */
  #[Hook('webform_admin_third_party_settings_form_alter', module: 'captcha')]
  public function captchaWebformAdminThirdPartySettingsFormAlter(&$form, FormStateInterface $form_state) {
    /** @var \Drupal\webform\WebformThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('webform.third_party_settings_manager');
    $replace_administration_mode = $third_party_settings_manager->getThirdPartySetting('captcha', 'replace_administration_mode');
    $t_args = [':href' => Url::fromRoute('captcha_settings')->toString(), '@from' => t('Place a CAPTCHA here for untrusted users.'), '@to' => t('Add CAPTCHA element to this webform for untrusted users.')];
    $form['third_party_settings']['captcha'] = ['#type' => 'details', '#title' => t('CAPTCHA'), '#description' => t('Provides the <a href=":href">CAPTCHA</a> for adding challenges to forms.', [':href' => 'https://en.wikipedia.org/wiki/CAPTCHA']), '#open' => TRUE];
    $form['third_party_settings']['captcha']['replace_administration_mode'] = ['#type' => 'checkbox', '#title' => t('Replace <em>Add CAPTCHA administration links to forms</em> with CAPTCHA webform element'), '#description' => t('If checked and <a href=":href">Add CAPTCHA administration links to forms</a> is enabled, the CAPTCHA fieldset added to every form will create a new CAPTCHA webform element instead of tracking each webform\'s id.', $t_args) . '<br/><br/>' . t('It changes the "@from" link label and behavior to "@to"', $t_args), '#default_value' => $replace_administration_mode, '#return_value' => TRUE];
  }

  /**
   * Implements hook_webform_submission_form_alter().
   */
  #[Hook('webform_submission_form_alter', module: 'captcha')]
  public function captchaWebformSubmissionFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    // Make sure CAPTCHA admin mode is enabled and available to the current user.
    $config = \Drupal::config('captcha.settings');
    if (!$config->get('administration_mode') || !\Drupal::currentUser()->hasPermission('administer CAPTCHA settings')) {
      return;
    }
    // Make sure the CAPTCHA webform element is enabled.
    /** @var \Drupal\webform\Plugin\WebformElementManagerInterface $element_manager */
    $element_manager = \Drupal::service('plugin.manager.webform.element');
    if ($element_manager->isExcluded('captcha')) {
      return;
    }
    // Make sure replace administrative mode is enabled.
    /** @var \Drupal\webform\WebformThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('webform.third_party_settings_manager');
    $replace_administration_mode = $third_party_settings_manager->getThirdPartySetting('captcha', 'replace_administration_mode');
    if (!$replace_administration_mode) {
      return;
    }
    // If the webform already has a CAPTCHA point is already configured, do not
    // do anything.
    /** @var \Drupal\captcha\CaptchaPointInterface $captcha_point */
    $captcha_point = \Drupal::entityTypeManager()->getStorage('captcha_point')->load($form_id);
    if ($captcha_point) {
      return;
    }
    $form['#after_build'][] = '_captcha_webform_submission_form_after_build';
  }

}
