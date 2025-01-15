<?php

namespace Drupal\webform_example_variant\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_example_variant.
 */
class WebformExampleVariantHooks
{
    /**
     * @file
     * Provides an example of a webform variant.
     */
    /**
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme()
    {
        return ['webform_variant_example_summary' => ['variables' => ['settings' => NULL, 'variant' => []]]];
    }
}
