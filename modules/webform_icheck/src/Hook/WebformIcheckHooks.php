<?php

namespace Drupal\webform_icheck\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_icheck.
 */
class WebformIcheckHooks {

  /**
   * Implements hook_webform_libraries_info().
   */
  #[Hook('webform_libraries_info')]
  public function webformLibrariesInfo() {
    $libraries = [];
    $libraries['jquery.icheck'] = [
      'title' => t('jQuery: iCheck'),
      'description' => t('Highly customizable checkboxes and radio buttons.'),
      'notes' => t('iCheck is used to optionally enhance checkboxes and radio buttons.'),
      'homepage_url' => Url::fromUri('http://icheck.fronteed.com/'),
      'download_url' => Url::fromUri('https://github.com/dargullin/icheck/archive/refs/tags/1.0.2.zip'),
      'version' => '1.0.2 ',
      'optional' => FALSE,
      'deprecated' => t('The iCheck library is not being maintained. It has been <a href=":href">deprecated</a> and will be removed in Webform 7.0.', [
        ':href' => 'https://www.drupal.org/project/webform/issues/2931154',
      ]),
      'license' => 'MIT',
    ];
    return $libraries;
  }

  /**
   * Implements hook_webform_element_default_properties_alter().
   */
  #[Hook('webform_element_default_properties_alter')]
  public function webformElementDefaultPropertiesAlter(array &$properties, array &$definition) {
    if (_webform_icheck_is_supported($definition['id'])) {
      $properties['icheck'] = '';
    }
  }

  /**
   * Implements hook_webform_element_configuration_form_alter().
   */
  #[Hook('webform_element_configuration_form_alter')]
  public function webformElementConfigurationFormAlter(&$form, FormStateInterface $form_state) {
    /** @var \Drupal\webform_ui\Form\WebformUiElementEditForm $form_object */
    $form_object = $form_state->getFormObject();
    $element_plugin = $form_object->getWebformElementPlugin();
    $element_type = $element_plugin->getTypeName();
    if (!_webform_icheck_is_supported($element_type)) {
      return;
    }
    /** @var \Drupal\webform\WebformThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('webform.third_party_settings_manager');
    $default_icheck = $third_party_settings_manager->getThirdPartySetting('webform_icheck', 'default_icheck');
    $form['form']['icheck'] = [
      '#type' => 'select',
      '#title' => t('Enhance using iCheck'),
      '#description' => t('Replaces @type element with jQuery <a href=":href">iCheck</a> boxes.', [
        '@type' => mb_strtolower($element_plugin->getPluginLabel()),
        ':href' => 'http://icheck.fronteed.com/',
      ]),
      '#empty_option' => t('- Default -'),
      '#options' => _webform_icheck_get_options(),
    ];
    if ($default_icheck) {
      $icheck_options = OptGroup::flattenOptions($form['form']['icheck']['#options']);
      $form['form']['icheck']['#description'] .= '<br /><br />' . t("Leave blank to use the default iCheck style. Select 'None' to display the default HTML element.");
      $form['form']['icheck']['#description'] .= '<br /><br />' . t('Defaults to: %value', ['%value' => $icheck_options[$default_icheck]]);
      $form['form']['icheck']['#options']['none'] = t('None');
    }
  }

  /**
   * Implements hook_webform_element_alter().
   */
  #[Hook('webform_element_alter')]
  public function webformElementAlter(array &$element, FormStateInterface $form_state, array $context) {
    $element_type = $element['#type'] ?? '';
    if (!_webform_icheck_is_supported($element_type)) {
      return;
    }
    /** @var \Drupal\webform\WebformThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('webform.third_party_settings_manager');
    $default_icheck = $third_party_settings_manager->getThirdPartySetting('webform_icheck', 'default_icheck');
    $icheck = NULL;
    $icheck_skin = NULL;
    if (isset($element['#icheck'])) {
      if ($element['#icheck'] !== 'none') {
        $icheck = $element['#icheck'];
        $icheck_skin = strtok($element['#icheck'], '-');
      }
    }
    elseif ($default_icheck) {
      $icheck = $default_icheck;
      $icheck_skin = strtok($default_icheck, '-');
    }
    if ($icheck) {
      $element['#attributes']['data-webform-icheck'] = $icheck;
      $element['#attached']['library'][] = 'webform_icheck/webform_icheck.element';
      $element['#attached']['library'][] = 'webform_icheck/libraries.jquery.icheck.' . $icheck_skin;
    }
  }

  /**
   * Implements hook_webform_admin_third_party_settings_form_alter().
   */
  #[Hook('webform_admin_third_party_settings_form_alter')]
  public function webformAdminThirdPartySettingsFormAlter(&$form, FormStateInterface $form_state) {
    /** @var \Drupal\webform\WebformThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('webform.third_party_settings_manager');
    $default_icheck = $third_party_settings_manager->getThirdPartySetting('webform_icheck', 'default_icheck');
    // iCheck.
    $form['third_party_settings']['webform_icheck'] = ['#type' => 'details', '#title' => t('iCheck'), '#open' => TRUE];
    $form['third_party_settings']['webform_icheck']['default_icheck'] = [
      '#type' => 'select',
      '#title' => t('Enhance checkboxes/radio buttons using iCheck'),
      '#description' => t('If set, all checkboxes/radio buttons with be enhanced using jQuery <a href=":href">iCheck</a> boxes.', [
        ':href' => 'http://icheck.fronteed.com/',
      ]),
      '#empty_option' => t('- Default -'),
      '#options' => _webform_icheck_get_options(),
      '#default_value' => $default_icheck,
    ];
  }

}
