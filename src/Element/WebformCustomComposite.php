<?php

namespace Drupal\webform\Element;

/**
 * Provides a webform custom composite element.
 *
 * @FormElement("webform_custom_composite")
 */
class WebformCustomComposite extends WebformMultiple {

  /**
   * {@inheritdoc}
   */
  protected static function setElementDefaultValue(array &$element, $default_value) {
    // Checkboxes element expects to receive flat array
    // @see Drupal\Core\Render\Element\Checkboxes::valueCallback()".
    if ($element['#type'] === 'webform_checkboxes_other') {
      $other_value = $default_value['other'];

      $default_value = array_filter($default_value['checkboxes']);
      if ($other_value !== '') {
        $default_value += [$other_value => $other_value];
      }
    }

    parent::setElementDefaultValue($element, $default_value);
  }

}
