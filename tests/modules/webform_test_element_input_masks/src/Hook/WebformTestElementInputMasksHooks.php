<?php

namespace Drupal\webform_test_element_input_masks\Hook;

use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_test_element_input_masks.
 */
class WebformTestElementInputMasksHooks
{
    /**
     * @file
     * Support module for webform that provides element plugin tests.
     */
    /**
     * Implements hook_webform_element_input_masks().
     */
    #[Hook('webform_element_input_masks')]
    public function webformElementInputMasks()
    {
        $input_masks = [];
        $input_masks['999'] = ['title' => t('3-digit number'), 'example' => '999', 'pattern' => '^\d\d\d$'];
        return $input_masks;
    }
    /**
     * Implements hook_webform_element_input_masks_alter().
     */
    #[Hook('webform_element_input_masks_alter')]
    public function webformElementInputMasksAlter(array &$input_masks)
    {
        $input_masks['999']['title'] .= ' (' . t('Custom input mask') . ')';
    }
}
