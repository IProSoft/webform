<?php

namespace Drupal\webform\Tests\Element;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\webform\Entity\Webform;

/**
 * Tests for webform datelist element.
 *
 * @group Webform
 */
class WebformElementDateListTest extends WebformElementTestBase {

  /**
   * Webforms to load.
   *
   * @var array
   */
  protected static $testWebforms = ['test_element_datelist'];

  /**
   * Test datelist element.
   */
  public function testDateListElement() {
    $webform = Webform::load('test_element_datelist');

    // Check '#format' values.
    $this->drupalGet('webform/test_element_datelist');
    $this->assertFieldByName('datelist_default[month]', '8');

    // Check '#date_abbreviate': false.
    $this->assertRaw('<select data-drupal-selector="edit-datelist-no-abbreviate-month" title="Month" id="edit-datelist-no-abbreviate-month" name="datelist_no_abbreviate[month]" class="form-select"><option value="">Month</option><option value="1">January</option>');

    // Check date year range reverse.
    $this->drupalGet('webform/test_element_datelist');
    $this->assertRaw('<select data-drupal-selector="edit-datelist-date-year-range-reverse-year" title="Year" id="edit-datelist-date-year-range-reverse-year" name="datelist_date_year_range_reverse[year]" class="form-select"><option value="" selected="selected">Year</option><option value="2010">2010</option><option value="2009">2009</option><option value="2008">2008</option><option value="2007">2007</option><option value="2006">2006</option><option value="2005">2005</option></select>');

    // Check 'datelist' and 'datetime' #default_value.
    $form = $webform->getSubmissionForm();
    $this->assert($form['elements']['datelist_default']['#default_value'] instanceof DrupalDateTime, 'datelist_default #default_value instance of \Drupal\Core\Datetime\DrupalDateTime.');

    // Check datelist #max validation.
    $edit = [
      'datelist_min_max[year]' => '2010',
      'datelist_min_max[month]' => '8',
      'datelist_min_max[day]' => '18',
    ];
    $this->drupalPostForm('webform/test_element_datelist', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">datelist_min_max</em> must be on or before <em class="placeholder">2009-12-31</em>.');

    // Check datelist #min validation.
    $edit = [
      'datelist_min_max[year]' => '2006',
      'datelist_min_max[month]' => '8',
      'datelist_min_max[day]' => '18',
    ];
    $this->drupalPostForm('webform/test_element_datelist', $edit, t('Submit'));
    $this->assertRaw('<em class="placeholder">datelist_min_max</em> must be on or after <em class="placeholder">2009-01-01</em>.');

    // Check custom required error.
    $edit = [
      'datelist_required_error[year]' => '',
      'datelist_required_error[month]' => '',
      'datelist_required_error[day]' => '',
      'datelist_required_error[hour]' => '',
      'datelist_required_error[minute]' => '',
    ];
    $this->drupalPostForm('webform/test_element_datelist', $edit, t('Submit'));
    $this->assertRaw('Custom required error');
  }

}
