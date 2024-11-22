<?php

namespace Drupal\webform_example_handler\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_example_handler.
 */
class WebformExampleHandlerHooks {
  /**
   * @file
   * Provides an example of a webform handler.
   */

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'webform_handler_example_summary' => [
        'variables' => [
          'settings' => NULL,
          'handler' => [],
        ],
      ],
    ];
  }

}
