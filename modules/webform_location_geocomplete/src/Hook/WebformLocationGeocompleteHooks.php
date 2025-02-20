<?php

namespace Drupal\webform_location_geocomplete\Hook;

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_location_geocomplete.
 */
class WebformLocationGeocompleteHooks {
  use StringTranslationTrait;

  /**
   * Implements hook_webform_libraries_info().
   */
  #[Hook('webform_libraries_info')]
  public function webformLibrariesInfo() {
    $libraries = [];
    $libraries['jquery.geocomplete'] = [
      'title' => $this->t('jQuery: Geocoding and Places Autocomplete Plugin'),
      'description' => $this->t("Geocomple is an advanced jQuery plugin that wraps the Google Maps API's Geocoding and Places Autocomplete services."),
      'notes' => $this->t('Geocomplete is used by the location element.'),
      'homepage_url' => Url::fromUri('http://ubilabs.github.io/geocomplete/'),
      'download_url' => Url::fromUri('https://github.com/ubilabs/geocomplete/archive/refs/tags/1.7.0.zip'),
      'version' => '1.7.0',
      'elements' => [
        'webform_location_geocomplete',
      ],
      'optional' => FALSE,
      'deprecated' => $this->t('The jQuery: Geocoding and Places Autocomplete Plugin library is not being maintained. It has been <a href=":href">deprecated</a> and will be removed in Webform 7.0.', [
        ':href' => 'https://www.drupal.org/node/2991275',
      ]),
      'license' => 'MIT',
    ];
    return $libraries;
  }

  /**
   * Implements hook_js_alter().
   */
  #[Hook('js_alter')]
  public function jsAlter(&$javascript, AttachedAssetsInterface $assets) {
    // Add Google API key required by webform/libraries.jquery.geocomplete
    // which is dependency for webform_location_geocomplete/webform_location_geocomplete.element.
    //
    // @see \Drupal\webform_location_geocomplete\Element\WebformLocationGeocomplete::processWebformComposite
    // @see webform_location_geocomplete.libraries.yml
    $settings = $assets->getSettings();
    if (!empty($settings['webform']['location']['geocomplete']['api_key']) && isset($javascript['https://maps.googleapis.com/maps/api/js?key=API_KEY&libraries=places'])) {
      $api_key = $settings['webform']['location']['geocomplete']['api_key'];
      $javascript['https://maps.googleapis.com/maps/api/js?key=API_KEY&libraries=places']['data'] = "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places";
      unset($settings['webform']['location']['geocomplete']['api_key']);
      $assets->setSettings($settings);
    }
  }

  /**
   * Implements hook_webform_admin_third_party_settings_form_alter().
   */
  #[Hook('webform_admin_third_party_settings_form_alter')]
  public function webformAdminThirdPartySettingsFormAlter(&$form, FormStateInterface $form_state) {
    /** @var \Drupal\webform\WebformThirdPartySettingsManagerInterface $third_party_settings_manager */
    $third_party_settings_manager = \Drupal::service('webform.third_party_settings_manager');
    $default_google_maps_api_key = $third_party_settings_manager->getThirdPartySetting('webform_location_geocomplete', 'default_google_maps_api_key');
    // Location geocomplete.
    $form['third_party_settings']['webform_location_geocomplete'] = ['#type' => 'details', '#title' => $this->t('Location geocomplete'), '#open' => TRUE];
    $form['third_party_settings']['webform_location_geocomplete']['default_google_maps_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Google Maps API key'),
      '#description' => $this->t('Google requires users to use a valid API key. Using the <a href="https://console.developers.google.com/apis">Google API Manager</a>, you can enable the <em>Google Maps JavaScript API</em>. That will create (or reuse) a <em>Browser key</em> which you can paste here.'),
      '#default_value' => $default_google_maps_api_key,
    ];
  }

}
