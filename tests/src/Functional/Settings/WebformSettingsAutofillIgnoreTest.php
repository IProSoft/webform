<?php

namespace Drupal\Tests\webform\Functional\Settings;

use Drupal\webform\Entity\Webform;
use Drupal\Tests\webform\Functional\WebformBrowserTestBase;

/**
 * Tests for webform submission form autofill ignoring source entity.
 *
 * @group webform
 */
class WebformSettingsAutofillIgnoreTest extends WebformBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['filter', 'user', 'node', 'webform'];

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = ['test_form_autofill_ignore'];

  /**
   * Test webform submission form autofill.
   */
  public function testAutofill() {
    $account = $this->drupalCreateUser();
    $this->drupalLogin($account);

    $webform = Webform::load('test_form_autofill_ignore');
    $node_1 = $this->drupalCreateNode(['title' => 'test_node 1']);
    $options_1 = ['query' => ['source_entity_type' => 'node', 'source_entity_id' => $node_1->id()]];

    $node_2 = $this->drupalCreateNode(['title' => 'test_node 2']);
    $options_2 = ['query' => ['source_entity_type' => 'node', 'source_entity_id' => $node_2->id()]];

    // Check that elements are empty.
    $this->drupalGet('/webform/test_form_autofill_ignore', $options_1);
    $this->assertSession()->responseNotContains('This submission has been autofilled with your previous submission.');


    // Check that 'textfield_autofill' is empty.
    $this->assertSession()->fieldValueEquals('textfield_autofill', '');

    // Create a submission from the first source.
    $edit = [
      'textfield_autofill' => '{textfield_autofill_1}',
    ];

    $this->postSubmission($webform, $edit, NULL, $options_1);

    // Get autofilled submission form.
    $this->drupalGet('/webform/test_form_autofill_ignore', $options_1);

    // Check that default configuration message is displayed.
    $this->assertSession()->responseContains('This submission has been autofilled with your previous submission.');

    // Check that 'textfield_autofill' is autofilled.
    $this->assertSession()->fieldValueEquals('textfield_autofill', '{textfield_autofill_1}');

    // Check that 'textfield_autofill' is not autofilled from the second source entity.
    $this->drupalGet('/webform/test_form_autofill_ignore', $options_2);
    $this->assertSession()->fieldValueNotEquals('textfield_autofill', '{textfield_autofill_1}');
    $this->assertSession()->responseNotContains('This submission has been autofilled with your previous submission.');

    // Turn on the autofill_ignore_source setting.
    $webform
      ->setSetting('autofill_ignore_source', TRUE)
      ->save();


    // Check that default configuration message is displayed from the second source.
    $this->drupalGet('/webform/test_form_autofill_ignore', $options_2);
    $this->assertSession()->fieldValueEquals('textfield_autofill', '{textfield_autofill_1}');
    $this->assertSession()->responseContains('This submission has been autofilled with your previous submission.');

    // Create a second submission from the second source.
    $edit = [
      'textfield_autofill' => '{textfield_autofill_2}',
    ];

    $this->postSubmission($webform, $edit, NULL, $options_2);

    // Check that default configuration message is displayed from the first source.
    $this->drupalGet('/webform/test_form_autofill_ignore', $options_2);
    $this->assertSession()->fieldValueEquals('textfield_autofill', '{textfield_autofill_2}');
    $this->assertSession()->responseContains('This submission has been autofilled with your previous submission.');

  }

}
