<?php

namespace Drupal\webform\Hook;

use Drupal\Component\Serialization\Yaml;
use Drupal\Component\Utility\Html;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileInterface;
use Drupal\filter\FilterFormatInterface;
use Drupal\webform\Element\WebformHtmlEditor;
use Drupal\webform\WebformInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform.
 */
class WebformEditorHooks {

  /**
   * Implements hook_ENTITY_TYPE_access().
   */
  #[Hook('filter_format_access')]
  public function filterFormatAccess(FilterFormatInterface $filter_format, $operation, AccountInterface $account) {
    if ($filter_format->id() === WebformHtmlEditor::DEFAULT_FILTER_FORMAT && $operation !== 'use') {
      return AccessResult::forbidden('This text format can only be managed by the Webform module.');
    }
    return AccessResult::neutral();
  }

  /**
   * Implements hook_ENTITY_TYPE_load().
   */
  #[Hook('filter_format_load')]
  public function filterFormatLoad($entities) {
    foreach ($entities as $entity) {
      // Hide the default webform filter format on the filter admin overview page.
      if ($entity->id() === WebformHtmlEditor::DEFAULT_FILTER_FORMAT && \Drupal::routeMatch()->getRouteName() === 'filter.admin_overview') {
        // Set status to FALSE which hides the default webform filter format.
        $entity->set('status', FALSE);
      }
    }
  }

  /**
   * Implements hook_editor_js_settings_alter().
   */
  #[Hook('editor_js_settings_alter')]
  public function editorJsSettingsAlter(array &$settings) {
    $settings['editor']['formats'][WebformHtmlEditor::DEFAULT_FILTER_FORMAT]['editorSettings']['autoGrow_minHeight'] = '80';
  }

  /* ************************************************************************** */
  // Webform entity hooks.
  /* ************************************************************************** */

  /**
   * Implements hook_webform_insert().
   *
   * @see editor_entity_insert()
   */
  #[Hook('webform_insert')]
  public function webformInsert(WebformInterface $webform) {
    $uuids = _webform_get_config_entity_file_uuids($webform);
    _webform_record_file_usage($uuids, $webform->getEntityTypeId(), $webform->id());
  }

  /**
   * Implements hook_webform_update().
   *
   * @see editor_entity_update()
   */
  #[Hook('webform_update')]
  public function webformUpdate(WebformInterface $webform) {
    $original_uuids = _webform_get_config_entity_file_uuids($webform->original);
    $uuids = _webform_get_config_entity_file_uuids($webform);
    // Detect file usages that should be incremented.
    $added_files = array_diff($uuids, $original_uuids);
    _webform_record_file_usage($added_files, $webform->getEntityTypeId(), $webform->id());
    // Detect file usages that should be decremented.
    $removed_files = array_diff($original_uuids, $uuids);
    _webform_delete_file_usage($removed_files, $webform->getEntityTypeId(), $webform->id(), 1);
  }

  /**
   * Implements hook_webform_delete().
   *
   * @see editor_entity_delete()
   */
  #[Hook('webform_delete')]
  public function webformDelete(WebformInterface $webform) {
    $uuids = _webform_get_config_entity_file_uuids($webform);
    _webform_delete_file_usage($uuids, $webform->getEntityTypeId(), $webform->id(), 0);
  }

}
