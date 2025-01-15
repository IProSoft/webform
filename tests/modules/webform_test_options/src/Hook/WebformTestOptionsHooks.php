<?php

namespace Drupal\webform_test_options\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_test_options.
 */
class WebformTestOptionsHooks
{
    /**
     * @file
     * Support module for webform that provides form alter hook for wizard cause tests.
     */
    /**
     * Implements hook_webform_options_WEBFORM_OPTIONS_ID_alter() for webform options test.
     */
    #[Hook('webform_options_test_alter')]
    public function webformOptionsTestAlter(array &$options, array &$element)
    {
        $options += ['four' => t('Four'), 'five' => t('Five'), 'six' => t('Six')];
    }
    /**
     * Implements hook_webform_options_alter().
     */
    #[Hook('webform_options_alter')]
    public function webformOptionsAlter(array &$options, array &$element, $id)
    {
        if ($id === 'custom') {
            $options = ['one' => t('One'), 'two' => t('Two'), 'three' => t('Three')];
            // Set the default value to one of the added options.
            $element['#default_value'] = 'one';
        }
    }
}
