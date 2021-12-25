<?php

namespace Drupal\Tests\webform\Functional;

/**
 * Tests for webform libraries.
 *
 * @group webform
 */
class WebformLibrariesTest extends WebformBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['webform_ui'];

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = ['test_libraries_optional'];

  /**
   * Tests webform libraries.
   */
  public function testLibraries() {
    $assert_session = $this->assertSession();

    $optional_properties = [
      'input_mask' => 'properties[input_mask][select]',
      'international_telephone' => 'properties[international]',
      'international_telephone_composite' => 'properties[phone__international]',
      'word_counter' => 'properties[counter_type]',
      'select2' => 'properties[select2]',
    ];

    $this->drupalLogin($this->rootUser);

    // Enable choices and jquery.chosen.
    $this->drupalGet('/admin/structure/webform/config/libraries');
    $edit = [
      'excluded_libraries[choices]' => TRUE,
      'excluded_libraries[jquery.chosen]' => TRUE,
    ];
    $this->submitForm($edit, 'Save configuration');

    // Check optional libraries are included.
    $this->drupalGet('/webform/test_libraries_optional');
    $assert_session->responseContains('/select2.min.js');
    $assert_session->responseContains('/choices.min.js');
    $assert_session->responseContains('/chosen.jquery.min.js');
    $assert_session->responseContains('/textcounter.min.js');
    $assert_session->responseContains('/intlTelInput.min.js');
    $assert_session->responseContains('/jquery.inputmask.min.js');
    $assert_session->responseContains('/codemirror.js');
    $assert_session->responseContains('/jquery.timepicker.min.js');

    // Check optional libraries are properties accessible (#access = TRUE).
    foreach ($optional_properties as $element_name => $input_name) {
      $this->drupalGet("/admin/structure/webform/manage/test_libraries_optional/element/$element_name/edit");
      $assert_session->fieldExists($input_name);
    }

    // Exclude optional libraries.
    $this->drupalGet('/admin/structure/webform/config/libraries');
    $edit = [
      'excluded_libraries[ckeditor.fakeobjects]' => FALSE,
      'excluded_libraries[ckeditor.image]' => FALSE,
      'excluded_libraries[ckeditor.link]' => FALSE,
      'excluded_libraries[codemirror]' => FALSE,
      'excluded_libraries[choices]' => FALSE,
      'excluded_libraries[jquery.inputmask]' => FALSE,
      'excluded_libraries[jquery.intl-tel-input]' => FALSE,
      'excluded_libraries[jquery.select2]' => FALSE,
      'excluded_libraries[jquery.chosen]' => FALSE,
      'excluded_libraries[jquery.timepicker]' => FALSE,
      'excluded_libraries[jquery.textcounter]' => FALSE,
    ];
    $this->submitForm($edit, 'Save configuration');

    // Check optional libraries are excluded.
    $this->drupalGet('/webform/test_libraries_optional');
    $assert_session->responseNotContains('/select2.min.js');
    $assert_session->responseNotContains('/choices.min.js');
    $assert_session->responseNotContains('/chosen.jquery.min.js');
    $assert_session->responseNotContains('/textcounter.min.js');
    $assert_session->responseNotContains('/intlTelInput.min.js');
    $assert_session->responseNotContains('/jquery.inputmask.min.js');
    $assert_session->responseNotContains('/codemirror.js');
    $assert_session->responseNotContains('/jquery.timepicker.min.js');

    // Check optional libraries are properties hidden (#access = FALSE).
    foreach ($optional_properties as $element_name => $input_name) {
      $this->drupalGet("admin/structure/webform/manage/test_libraries_optional/element/$element_name/edit");
      $assert_session->fieldNotExists($input_name);
    }

    // Check that status report excludes optional libraries.
    $this->drupalGet('/admin/reports/status');
    $assert_session->pageTextNotContains('CKEditor: Fakeobjects library ');
    $assert_session->pageTextNotContains('CKEditor: Image library ');
    $assert_session->pageTextNotContains('CKEditor: Link library ');
    $assert_session->pageTextNotContains('Code Mirror library ');
    $assert_session->pageTextNotContains('jQuery: iCheck library ');
    $assert_session->pageTextNotContains('jQuery: Input Mask library ');
    $assert_session->pageTextNotContains('jQuery: Select2 library ');
    $assert_session->pageTextNotContains('jQuery: Choices library ');
    $assert_session->pageTextNotContains('jQuery: Chosen library ');
    $assert_session->pageTextNotContains('jQuery: Timepicker library ');
    $assert_session->pageTextNotContains('jQuery: Text Counter library ');
    // phpcs:disable
    // Issue #2934542: Fix broken Webform.Drupal\webform\Tests\WebformLibrariesTest
    // @see https://www.drupal.org/project/webform/issues/2934542
    /*
    // Exclude element types that require libraries.
    $edit = [
      'excluded_elements[webform_rating]' => FALSE,
      'excluded_elements[webform_signature]' => FALSE,
    ];
    $this->drupalGet('/admin/structure/webform/config/elements');
    $this->submitForm($edit, 'Save configuration');

    // Check that status report excludes libraries required by element types.
    $this->drupalGet('/admin/reports/status');
    $assert_session->pageTextNotContains('jQuery: Image Picker library');
    $assert_session->pageTextNotContains('jQuery: RateIt library');
    $assert_session->pageTextNotContains('Signature Pad library');
    */
    // phpcs:enable

    // Issue #3110478: [Webform 8.x-6.x] Track the D9 readiness state of the
    // Webform module's (optional) dependencies.
    // @see https://www.drupal.org/project/webform/issues/3110478
    // Check that choices, chosen, and select2 using webform's CDN URLs.
    // Check that choices, chosen, and select2 using webform's CDN URLs.
    $this->drupalGet('/admin/structure/webform/config/libraries');
    $edit = [
      'excluded_libraries[jquery.select2]' => TRUE,
      'excluded_libraries[jquery.chosen]' => TRUE,
    ];
    $this->submitForm($edit, 'Save configuration');
    $this->drupalGet('/webform/test_libraries_optional');
    $assert_session->responseContains('https://cdnjs.cloudflare.com/ajax/libs/chosen');
    $assert_session->responseContains('https://cdnjs.cloudflare.com/ajax/libs/select2');

    // Install chosen and select2 modules.
    \Drupal::service('module_installer')->install(['chosen', 'chosen_lib', 'select2']);
    \Drupal::service('module_installer')->install(['select2']);
    drupal_flush_all_caches();

    // Check that chosen and select2 using module's path and not CDN.
    $this->drupalGet('/webform/test_libraries_optional');
    $assert_session->responseNotContains('https://cdnjs.cloudflare.com/ajax/libs/chosen');
    $assert_session->responseNotContains('https://cdnjs.cloudflare.com/ajax/libs/select2');
    $assert_session->responseContains('/modules/contrib/chosen/css/chosen-drupal.css');
  }

}
