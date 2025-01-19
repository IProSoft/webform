<?php

namespace Drupal\webform\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Textfield;
use Drupal\webform\Entity\WebformOptions;

/**
 * Provides a one-line text field with autocompletion webform element.
 *
 * @FormElement("webform_autocomplete")
 */
class WebformAutocomplete extends Textfield {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);

    $info = parent::getInfo();
    $info['#pre_render'][] = [$class, 'preRenderWebformAutocomplete'];
    $info['#element_validate'] = [[$class, 'validateWebformAutocomplete']];
    return $info;
  }

  /**
   * Prepares a #type 'webform_autocomplete' render element for input.html.twig.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for input.html.twig.
   */
  public static function preRenderWebformAutocomplete($element) {
    static::setAttributes($element, ['webform-autocomplete']);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineTranslatableProperties() {
    return array_merge(parent::defineTranslatableProperties(), ['autocomplete_items']);
  }

  /**
   * {@inheritdoc}
   */
  public static function validateWebformAutocomplete(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $value = $element['#value'];
    $options = [];
    if ($value) {
      // Get allowed options.
      if (!empty($element['#autocomplete_items'])) {
        $element['#options'] = $element['#autocomplete_items'];
        $options = WebformOptions::getElementOptions($element);
      }
      if (in_array($value, $options)) {
        $form_state->setValueForElement($element, $options[array_search($value, $options)]);
      }
      else {
        $form_state->setError($element, t('Please select a valid option for @label.', ['@label' => $element['#title']]));
      }
    }
  }

}
