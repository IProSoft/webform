<?php

namespace Drupal\Tests\webform\FunctionalJavascript\Settings;

use Drupal\Tests\webform\FunctionalJavascript\WebformWebDriverTestBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformInterface;

/**
 * Tests webform JavaScript.
 *
 * @group webform_javascript
 */
class WebformSettingsDialogJavaScriptTest extends WebformWebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['block', 'node', 'views', 'webform', 'webform_test_prepopulate_block'];

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = [
    'test_form_prepopulate',
  ];

  /**
   * Test Dialog JavaScript.
   */
  public function testDialogJs() {
    $assert_session = $this->assertSession();

    /* ********************************************************************** */
    // Test webform prepopulate source entity (form_prepopulate_source_entity)
    /* ********************************************************************** */
    $webform_prepopulate = Webform::load('test_form_prepopulate');
    // Enable prepopulated source entity.
    $webform_prepopulate->setSetting('form_prepopulate_source_entity', TRUE);
    $webform_prepopulate->setSetting('form_prepopulate_source_entity_required', FALSE);
    $webform_prepopulate->setSetting('form_title', WebformInterface::TITLE_WEBFORM_SOURCE_ENTITY);
    $webform_prepopulate->save();
    // Enable site-wide dialog support.
    \Drupal::configFactory()->getEditable('webform.settings')
      ->set('settings.dialog', TRUE)
      ->save();
    // Place the test block add the dialog link into all pages.
    $this->drupalPlaceBlock('webform_test_prepopulate_block');
    // Place the title block.
    $this->drupalPlaceBlock('page_title_block');
    // Use the node view as the source entity.
    $this->drupalget('/node');
    // The source entity type and id should be prepopulated.
    $assert_session->linkByHrefExists('/webform/test_form_prepopulate?source_entity_type=view&source_entity_id=frontpage');
    // Go to the prepopulate form as anonymouse.
    $this->drupalGet($webform_prepopulate->toUrl()->toString(), [
      'query' => [
        'source_entity_type' => 'view',
        'source_entity_id' => 'content',
      ],
    ]);
    $assert_session->responseContains('<h1>' . $webform_prepopulate->get('title') . '</h1>');
    // Login as admin.
    $this->drupalLogin($this->rootUser);
    // The form title should be prepopulated with the View lable.
    $this->drupalGet($webform_prepopulate->toUrl()->toString(), [
      'query' => [
        'source_entity_type' => 'view',
        'source_entity_id' => 'content',
      ],
    ]);
    $assert_session->responseContains('<h1>' . $webform_prepopulate->get('title') . ': Content' . '</h1>');
  }

}
