<?php

namespace Drupal\webform_toggles\Hook;

use Drupal\Core\Url;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_toggles.
 */
class WebformTogglesHooks
{
    /**
     * Implements hook_webform_libraries_info().
     */
    #[Hook('webform_libraries_info')]
    public function webformLibrariesInfo()
    {
        $libraries = [];
        $libraries['jquery.toggles'] = ['title' => t('jQuery: Toggles'), 'description' => t('Toggles is a lightweight jQuery plugin that creates easy-to-style toggle buttons.'), 'notes' => t('Toggles is used to provide a toggle element.'), 'homepage_url' => \Drupal\Core\Url::fromUri('https://github.com/simontabor/jquery-toggles/'), 'download_url' => \Drupal\Core\Url::fromUri('https://github.com/simontabor/jquery-toggles/archive/refs/tags/v4.0.0.zip'), 'version' => '4.0.0', 'elements' => ['webform_toggle', 'webform_toggles'], 'optional' => FALSE, 'deprecated' => t('The Toggles library is not being maintained and has major accessibility issues. It has been <a href=":href">deprecated</a>.', [':href' => 'https://www.drupal.org/project/webform/issues/2890861']), 'license' => 'MIT'];
        return $libraries;
    }
}
