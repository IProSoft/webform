<?php

declare(strict_types=1);

namespace Drupal\webform_test_variant\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_test_variant.
 */
class WebformTestVariantHooks {
  /**
   * @file
   * Support module for webform that provides variant plugin tests.
   */

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'webform_variant_test_summary' => [
        'variables' => [
          'settings' => NULL,
          'variant' => [],
        ],
      ],
      'webform_variant_test_offcanvas_width' => [
        'variables' => [
          'settings' => NULL,
          'variant' => [],
        ],
      ],
    ];
  }

}
