<?php

namespace Drupal\Tests\webform\Functional\Block;

use Drupal\Tests\webform\Functional\WebformBrowserTestBase;

/**
 * Tests for webform block.
 *
 * @group webform
 */
class WebformBlockTest extends WebformBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'webform', 'node'];

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = ['test_confirmation_inline', 'test_confirmation_message'];

  /**
   * Tests webform block.
   */
  public function testBlock() {
    $assert_session = $this->assertSession();

    // Place block.
    $block = $this->drupalPlaceBlock('webform_block', [
      'webform_id' => 'contact',
    ]);

    // Place block a second time.
    $block_duplicate = $this->drupalPlaceBlock('webform_block', [
      'webform_id' => 'contact',
    ]);

    // Sanity check.
    $this->assertNotEquals($block->id(), $block_duplicate->id());

    // Check that both contact webforms are present, and their form ids differ.
    $this->drupalGet('<front>');
    $assert_session->responseContains('webform-submission-contact-block-' . $block->id() . '-form');
    $assert_session->responseContains('webform-submission-contact-block-' . $block_duplicate->id() . '-form');

    // Check contact webforms with default data that differ.
    $block->getPlugin()->setConfigurationValue('default_data', "name: 'John Smith'");
    $block->save();
    $block_duplicate->getPlugin()->setConfigurationValue('default_data', "name: 'John Doe'");
    $block_duplicate->save();
    $this->drupalGet('<front>');

    $assert_session->responseContains('webform-submission-contact-block-' . $block->id() . '-form');
    $form = $assert_session->elementExists('css', '#webform-submission-contact-block-' . $block->id() . '-add-form');
    $assert_session->elementExists('xpath', "//input[@type='text'][@value='John Smith']", $form);

    $assert_session->responseContains('webform-submission-contact-block-' . $block_duplicate->id() . '-form');
    $form_duplicate = $assert_session->elementExists('css', '#webform-submission-contact-block-' . $block_duplicate->id() . '-add-form');
    $assert_session->elementExists('xpath', "//input[@type='text'][@value='John Doe']", $form_duplicate);

    // Check confirmation inline webform.
    $block_duplicate->getPlugin()->setConfigurationValue('webform_id', 'test_confirmation_inline');
    $block_duplicate->save();
    $this->drupalGet('<front>');
    $this->submitForm([], 'Submit', 'webform-submission-test-confirmation-inline-block-' . $block_duplicate->id() . '-add-form');
    $assert_session->elementTextContains('css', '#webform-submission-test-confirmation-inline-block-' . $block_duplicate->id() . '-add-form', 'This is a custom inline confirmation message.');

    // Check confirmation message webform displayed on front page.
    $block_duplicate->getPlugin()->setConfigurationValue('webform_id', 'test_confirmation_message');
    $block_duplicate->save();
    $this->drupalGet('<front>');
    $this->submitForm([], 'Submit', 'webform-submission-test-confirmation-message-block-' . $block_duplicate->id() . '-add-form');
    $assert_session->responseContains('This is a <b>custom</b> confirmation message.');
    $assert_session->addressEquals('/user/login');

    // Check confirmation message webform display on webform URL.
    $block->getPlugin()->setConfigurationValue('webform_id', 'test_confirmation_message');
    $block->getPlugin()->setConfigurationValue('redirect', TRUE);
    $block->save();
    $this->drupalGet('<front>');
    $this->submitForm([], 'Submit', 'webform-submission-test-confirmation-message-block-' . $block->id() . '-add-form');
    $assert_session->responseContains('This is a <b>custom</b> confirmation message.');
    $assert_session->addressEquals('webform/test_confirmation_message');

    // Check confirmation message webform display on webform URL with node present.
    $this->drupalCreateContentType(['type' => 'page']);
    $node = $this->drupalCreateNode();
    \Drupal::configFactory()
      ->getEditable('system.site')
      ->set('page.front', '/node/' . $node->id())
      ->save();
    $this->drupalGet('<front>');
    $this->submitForm([], 'Submit', 'webform-submission-test-confirmation-message-block-' . $block->id() . '-add-form');
    $assert_session->responseContains('This is a <b>custom</b> confirmation message.');
    $assert_session->addressEquals('webform/test_confirmation_message');

  }

}
