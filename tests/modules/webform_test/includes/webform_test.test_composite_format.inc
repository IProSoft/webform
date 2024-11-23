<?php

/**
 * @file
 */

declare(strict_types=1);

/**
 * @file
 */

use Drupal\webform\WebformInterface;

\Drupal::moduleHandler()->loadInclude('webform_test', 'inc', 'includes/webform_test.test_element_format');

/**
 * Generate test composite element formats.
 *
 * @return array
 *   An array containing test elements formats.
 */
function webform_test_test_composite_format(WebformInterface $webform) {
  return webform_test_test_element_format($webform, TRUE, FALSE);
}
