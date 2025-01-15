<?php

namespace Drupal\webform_options_custom\Hook;

use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_options_custom.
 */
class WebformOptionsCustomHooks {

  /**
   * Implements hook_webform_help_info().
   */
  #[Hook('webform_help_info')]
  public function webformHelpInfo() {
    $help = [];
    $help['config_options_custom'] = [
    'group' => 'configuration',
    'title' => t('Configuration: Custom options'),
    'content' => t('The <strong>Custom options configuration</strong> page lists reusable HTML/SVG custom options elements.'),
    'video_id' => 'custom_options',
    'routes' => [
          // @see /admin/structure/webform/options/custom
      'entity.webform_options_custom.collection',
    ]
];
    return $help;
  }

  /**
   * Implements hook_menu_local_tasks_alter().
   */
  #[Hook('menu_local_tasks_alter')]
  public function menuLocalTasksAlter(&$data, $route_name) {
    // Change config entities 'Translate *' tab to be just label 'Translate'.
    if (isset($data['tabs'][0]["config_translation.local_tasks:entity.webform_options_custom.config_translation_overview"]['#link']['title'])) {
      $data['tabs'][0]["config_translation.local_tasks:entity.webform_options_custom.config_translation_overview"]['#link']['title'] = t('Translate');
    }
  }

  /**
   * Implements hook_webform_libraries_info().
   */
  #[Hook('webform_libraries_info')]
  public function webformLibrariesInfo() {
    $libraries = [];
    $libraries['svg-pan-zoom'] = ['title' => t('SVG Pan & Zoom'), 'description' => t('Simple pan/zoom solution for SVGs in HTML.'), 'notes' => t('Svg-pan-zoom is used by custom options elements.'), 'homepage_url' => Url::fromUri('https://github.com/ariutta/svg-pan-zoom'), 'download_url' => Url::fromUri('https://github.com/ariutta/svg-pan-zoom/archive/refs/tags/3.6.1.zip'), 'version' => '3.6.1', 'optional' => FALSE, 'license' => 'BSD-2-Clause'];
    return $libraries;
  }

}
