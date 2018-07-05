<?php

namespace Drupal\webform\Utility;

/**
 * Helper class webform accessiblity methods.
 */
class WebformAccessibilityHelper {

  /**
   * Visually hide text using .visually-hidden class.
   *
   * The .visually-hidden class is used to render invisible content just for
   * screen reader users.
   *
   * @param $title
   *   Text that should be visually hidden.
   *
   * @return array
   *   A renderable array with the text wrapped in
   *   <span class="visually-hidden">
   *
   * @see https://webaim.org/techniques/css/invisiblecontent/
   */
  public static function buildVisuallyHidden($title) {
    return [
      '#markup' => $title,
      '#prefix' => '<span class="visually-hidden">',
      '#suffix' => '</span>',
    ];
  }

  /**
   * Aria hide text using aria-hidden attribute.
   *
   * The aria-hidden property tells screen-readers if they
   * should ignore the element.
   *
   * @param $title
   *   Text that should be aria-hidden.
   *
   * @return array
   *   A renderable array with the text wrapped in
   *   <span aria-hidden="true">
   *
   * @see https://www.w3.org/TR/wai-aria-1.1/#aria-hidden
   */
  public static function buildAriaHidden($title) {
    return [
      '#markup' => $title,
      '#prefix' => '<span aria-hidden="true">',
      '#suffix' => '</span>',
    ];
  }
}

