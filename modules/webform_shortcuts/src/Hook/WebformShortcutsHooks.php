<?php

namespace Drupal\webform_shortcuts\Hook;

use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_shortcuts.
 */
class WebformShortcutsHooks {
  use StringTranslationTrait;

  /**
   * Implements hook_webform_libraries_info().
   */
  #[Hook('webform_libraries_info')]
  public function webformLibrariesInfo() {
    $libraries = [];
    $libraries['jquery.hotkeys'] = [
      'title' => $this->t('jQuery.Hotkeys'),
      'description' => $this->t('jQuery Hotkeys is a plug-in that lets you easily add and remove handlers for keyboard events anywhere in your code supporting almost any key combination.'),
      'notes' => $this->t('jQuery Hotkeys is used by the form builder to quickly add and save elements.'),
      'homepage_url' => Url::fromUri('https://github.com/jeresig/jquery.hotkeys'),
      'download_url' => Url::fromUri('https://github.com/jeresig/jquery.hotkeys/archive/refs/tags/0.2.0.zip'),
      'version' => '0.2.0',
      'license' => 'MIT',
      'optional' => FALSE,
    ];
    return $libraries;
  }

}
