<?php

namespace Drupal\webform_shortcuts\Hook;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_shortcuts.
 */
class WebformShortcutsHooks {

  /**
   * Implements hook_webform_libraries_info().
   */
  #[Hook('webform_libraries_info')]
  public function webformLibrariesInfo() {
    $libraries = [];
    $libraries['jquery.hotkeys'] = [
      'title' => t('jQuery.Hotkeys'),
      'description' => t('jQuery Hotkeys is a plug-in that lets you easily add and remove handlers for keyboard events anywhere in your code supporting almost any key combination.'),
      'notes' => t('jQuery Hotkeys is used by the form builder to quickly add and save elements.'),
      'homepage_url' => Url::fromUri('https://github.com/jeresig/jquery.hotkeys'),
      'download_url' => Url::fromUri('https://github.com/jeresig/jquery.hotkeys/archive/refs/tags/0.2.0.zip'),
      'version' => '0.2.0',
      'license' => 'MIT',
      'optional' => FALSE,
    ];
    return $libraries;
  }

  /**
   * Implements hook_form_FORM_ID_alter() for admin config advanced form.
   */
  #[Hook('form_webform_admin_config_advanced_form_alter')]
  public function formWebformAdminConfigAdvancedFormAlter(&$form, FormStateInterface $form_state) {
    $config = \Drupal::config('webform_shortcuts.settings');
    $form['webform_shortcuts'] = [
      '#type' => 'details',
      '#title' => t('Keyboard shortcut settings'),
      '#description' => t('Enter custom keyboard shortcuts for common form builder actions. Leave blank to disable an individual shortcut.') . '<br/>' . t('<a href=":href">Learn more about configuring shortcuts using the jQuery HotKeys library</a>', [
        ':href' => 'https://github.com/jeresig/jquery.hotkeys',
      ]),
      '#open' => TRUE,
      '#tree' => TRUE,
    ];
    $form['webform_shortcuts']['add_element'] = [
      '#type' => 'textfield',
      '#title' => t('Add element'),
      '#default_value' => $config->get('add_element'),
    ];
    $form['webform_shortcuts']['add_page'] = [
      '#type' => 'textfield',
      '#title' => t('Add page'),
      '#default_value' => $config->get('add_page'),
    ];
    $form['webform_shortcuts']['add_layout'] = [
      '#type' => 'textfield',
      '#title' => t('Add layout'),
      '#default_value' => $config->get('add_layout'),
    ];
    $form['webform_shortcuts']['save_elements'] = [
      '#type' => 'textfield',
      '#title' => t('Save element or elements'),
      '#default_value' => $config->get('save_elements'),
    ];
    $form['webform_shortcuts']['reset_elements'] = [
      '#type' => 'textfield',
      '#title' => t('Reset elements'),
      '#default_value' => $config->get('reset_elements'),
    ];
    $form['webform_shortcuts']['toggle_weights'] = [
      '#type' => 'textfield',
      '#title' => t('Show/hide row weights'),
      '#default_value' => $config->get('reset_elements'),
    ];
    $form['#submit'][] = '_webform_shortcuts_form_webform_admin_config_advanced_form_submit';
  }

}
