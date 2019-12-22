<?php

namespace Drupal\Tests\webform\FunctionalJavascript\Element;

use Drupal\Tests\webform\FunctionalJavascript\WebformWebDriverTestBase;
use Drupal\webform\Entity\Webform;

/**
 * Tests webform computed element Ajax support.
 *
 * @see \Drupal\Tests\ajax_example\FunctionalJavascript\AjaxWizardTest
 *
 * @group webform_javascript
 */
class WebformElementComputedJavaScriptTest extends WebformWebDriverTestBase {

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = [
    'test_element_computed_ajax',
  ];

  /**
   * Tests computed element Ajax.
   */
  public function testComputedElementAjax() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert = $this->assertSession();

    $webform = Webform::load('test_element_computed_ajax');

    // Chech computed Twig element a and b elements exist.
    $this->drupalGet($webform->toUrl());
    $assert->fieldExists('a[select]');
    $assert->fieldExists('b');
    $assert->buttonExists('webform-computed-webform_computed_twig-button');
    $assert->hiddenFieldValueEquals('webform_computed_twig', 'Please enter a value for a and b.');

    // Calculate computed Twig element.
    $page->fillField('a[select]', '1');
    $page->fillField('b', '1');
    $session->executeScript("jQuery('input[name=\"webform_computed_twig\"]').click()");
    $assert->waitForText('1 + 1 = 2');

    // Check that computed Twig was calculated.
    $assert->hiddenFieldValueNotEquals('webform_computed_twig', 'Please enter a value for a and b.');
    $assert->hiddenFieldValueEquals('webform_computed_twig', '1 + 1 = 2');
  }

}
