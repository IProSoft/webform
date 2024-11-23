<?php

declare(strict_types=1);

namespace Drupal\webform_image_select_test\Hook;

use Drupal\webform_image_select\Entity\WebformImageSelectImages;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_image_select_test.
 */
class WebformImageSelectTestHooks {

  /**
   * Implements hook_webform_image_select_images_WEBFORM_IMAGE_SELECT_IMAGES_ID_alter().
   */
  #[Hook('webform_image_select_images_animals_alter')]
  public function webformImageSelectImagesAnimalsAlter(array &$images, array &$element) {
    if ($dogs = WebformImageSelectImages::load('dogs')) {
      $images += $dogs->getImages();
    }
  }

  /**
   * Implements hook_webform_image_select_images_alter().
   */
  #[Hook('webform_image_select_images_alter')]
  public function webformImageSelectImagesAlter(array &$images, array &$element, $id) {
    if ($id === 'animals' && ($bears = WebformImageSelectImages::load('bears'))) {
      $images += $bears->getImages();
      // Set the default value to one of the added images.
      $element['#default_value'] = 'dog_1';
    }
  }

}
