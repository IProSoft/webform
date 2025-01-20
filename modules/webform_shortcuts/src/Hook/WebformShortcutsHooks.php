<?php

namespace Drupal\webform_shortcuts\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Url;

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

}
