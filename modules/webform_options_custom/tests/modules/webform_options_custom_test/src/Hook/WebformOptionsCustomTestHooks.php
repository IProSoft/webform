<?php

namespace Drupal\webform_options_custom_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_options_custom_test.
 */
class WebformOptionsCustomTestHooks {
  /**
   * @file
   * Support module for webform that provides custom options element working examples.
   */

  /**
   * Implements hook_ENTITY_TYPE_load() for webform entities.
   */
  #[Hook('webform_load')]
  public function webformLoad(array $entities) {
    if (!isset($entities['test_element_options_custom'])) {
      return;
    }
    global $base_url;
    /** @var \Drupal\webform\WebformInterface $webform */
    $webform = $entities['test_element_options_custom'];
    $elements = $webform->getElementsDecodedAndFlattened();
    foreach ($elements as $element_key => $element) {
      if (isset($element['#url']) && strpos($element['#url'], '/') === FALSE) {
        $element['#url'] = $base_url . '/' . \Drupal::service('extension.list.module')->getPath('webform_options_custom_test') . '/images/' . $element['#url'];
      }
      $webform->setElementProperties($element_key, $element);
    }
  }

}
