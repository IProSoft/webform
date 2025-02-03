<?php

namespace Drupal\Tests\webform_scheduled_email\Functional;

use Drupal\Component\Datetime\Time;
use Drupal\Core\Database\Database;
use Drupal\Tests\webform_node\Functional\WebformNodeBrowserTestBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface;

/**
 * Tests for webform scheduled email handler.
 *
 * @group webform_scheduled_email
 */
class WebformScheduledEmailTest extends WebformNodeBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['webform', 'webform_scheduled_email', 'webform_scheduled_email_test', 'webform_node'];

  /**
   * The database connection for testing.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The time class for testing.
   */
  protected Time $time;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->connection = Database::getConnection();
    $this->time = new Time();
  }

  /**
   * Tests webform schedule email handler.
   */
  public function testWebformScheduledEmail() {
    $assert_session = $this->assertSession();

    $webform_schedule = Webform::load('test_handler_scheduled_email');

    /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $scheduled_manager */
    $scheduled_manager = \Drupal::service('webform_scheduled_email.manager');

    $yesterday = date($scheduled_manager->getDateFormat(), strtotime('-1 days'));
    $tomorrow = date($scheduled_manager->getDateFormat(), strtotime('+1 days'));

    /* ********************************************************************** */
    // Submission scheduling.
    /* ********************************************************************** */

    // Check scheduled email yesterday.
    $sid = $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $webform_submission = WebformSubmission::load($sid);
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email scheduled by Yesterday handler to be sent on $yesterday.");

    // Check scheduled email yesterday database send date.
    $scheduled_email = $scheduled_manager->load($webform_submission, 'yesterday');
    $this->assertEquals($scheduled_email->send, strtotime($yesterday));
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_SEND);

    // Check send yesterday email.
    $scheduled_manager->cron();
    $scheduled_email = $scheduled_manager->load($webform_submission, 'yesterday');
    $this->assertFalse($scheduled_email);

    // Check schedule other +14 days.
    $sid = $this->postSubmission($webform_schedule, ['send' => 'other', 'date[date]' => '2001-01-01'], 'Save Draft');
    $webform_submission = WebformSubmission::load($sid);
    $scheduled_email = $scheduled_manager->load($webform_submission, 'other');
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email scheduled by Other handler to be sent on 2001-01-15.");
    $this->assertEquals($scheduled_email->send, strtotime('2001-01-15'));
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_SEND);

    // Check reschedule other +14 days.
    $this->postSubmission($webform_schedule, ['send' => 'other', 'date[date]' => '2002-02-02'], 'Save Draft');
    $scheduled_email = $scheduled_manager->load($webform_submission, 'other');
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email rescheduled by Other handler to be sent on 2002-02-16.");
    $this->assertEquals($scheduled_email->send, strtotime('2002-02-16'));
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_SEND);

    // Check saving webform submission reschedules.
    $webform_submission->save();
    $scheduled_email = $scheduled_manager->load($webform_submission, 'other');
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_SEND);

    // Delete webform submission which deletes the scheduled email record.
    $webform_submission->delete();

    // Check delete removed scheduled email.
    $this->assertEquals($scheduled_manager->total(), 0);

    // Check schedule email for draft.
    $draft_reminder = date($scheduled_manager->getDateFormat(), strtotime('+14 days'));
    $sid = $this->postSubmission($webform_schedule, ['send' => 'draft_reminder'], 'Save Draft');
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email scheduled by Draft reminder handler to be sent on $draft_reminder.");
    $this->assertEquals($scheduled_manager->total(), 1);

    // Check unschedule email for draft.
    $this->postSubmission($webform_schedule, []);
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email unscheduled for Draft reminder handler.");
    $this->assertEquals($scheduled_manager->total(), 0);

    // Check broken/invalid date.
    $sid = $this->postSubmission($webform_schedule, ['send' => 'broken']);
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email not scheduled for Broken handler because [broken] is not a valid date/token.");
    $this->assertEquals($scheduled_manager->total($webform_schedule), 0);

    /* ********************************************************************** */
    // Submission scheduling with date/time.
    /* ********************************************************************** */

    // Change schedule type to 'datetime'.
    \Drupal::configFactory()->getEditable('webform_scheduled_email.settings')
      ->set('schedule_type', 'datetime')
      ->save();

    // Check other +14 days with time.
    $sid = $this->postSubmission($webform_schedule, ['send' => 'other', 'date[date]' => '2001-01-01', 'date[time]' => '02:00:00'], 'Save Draft');
    $webform_submission = WebformSubmission::load($sid);
    $scheduled_email = $scheduled_manager->load($webform_submission, 'other');
    $assert_session->pageTextContains("Test: Handler: Test scheduled email: Submission #$sid: Email scheduled by Other handler to be sent on 2001-01-15 02:00:00.");
    $this->assertEquals($scheduled_email->send, strtotime('2001-01-15 02:00:00'));
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_SEND);

    // Change schedule type back to 'date'.
    \Drupal::configFactory()->getEditable('webform_scheduled_email.settings')
      ->set('schedule_type', 'date')
      ->save();

    /* ********************************************************************** */
    // Webform scheduling.
    /* ********************************************************************** */

    // Purge all submissions.
    $this->purgeSubmissions();

    // Create 3 tomorrow scheduled emails.
    $this->postSubmission($webform_schedule, ['send' => 'tomorrow']);
    $this->postSubmission($webform_schedule, ['send' => 'tomorrow']);
    $this->postSubmission($webform_schedule, ['send' => 'tomorrow']);
    $this->assertEquals($scheduled_manager->total($webform_schedule), 3);

    // Create 3 yesterday scheduled emails.
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->assertEquals($scheduled_manager->total($webform_schedule), 6);

    // Send the 3 yesterday scheduled emails.
    $stats = $scheduled_manager->cron();
    $this->assertEquals($stats['sent'], 3);

    // Check on tomorrow scheduled emails remain.
    $this->assertEquals($scheduled_manager->total($webform_schedule), 3);

    // Reschedule yesterday submissions which includes all submissions.
    $scheduled_manager->schedule($webform_schedule, 'yesterday');
    $this->assertEquals($scheduled_manager->stats($webform_schedule), [
      'total' => 9,
      'waiting' => 6,
      'queued' => 3,
      'ready' => 0,
    ]);

    // Runs Reschedule yesterday submissions which includes all submissions.
    $stats = $scheduled_manager->cron();
    $this->assertNotEquals($stats['sent'], 6);
    $this->assertEquals($stats['sent'], 3);
    $this->assertEquals($scheduled_manager->stats($webform_schedule), [
      'total' => 3,
      'waiting' => 0,
      'queued' => 3,
      'ready' => 0,
    ]);

    // Reschedule tomorrow submissions.
    $scheduled_manager->schedule($webform_schedule, 'tomorrow');
    $this->assertEquals($scheduled_manager->total($webform_schedule), 6);
    $this->assertEquals($scheduled_manager->waiting($webform_schedule), 6);
    $this->assertEquals($scheduled_manager->ready($webform_schedule), 0);

    /* ********************************************************************** */
    // Webform scheduling with conditions.
    /* ********************************************************************** */

    // Purge all submissions.
    $this->purgeSubmissions();

    // Create 3 yesterday scheduled emails.
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->assertEquals($scheduled_manager->total($webform_schedule), 3);
    $this->assertEquals($scheduled_manager->stats(), [
      'total' => 3,
      'waiting' => 0,
      'queued' => 0,
      'ready' => 3,
    ]);

    // Add condition to only send yesterday email if 'value' is filled.
    /** @var \Drupal\webform\Plugin\WebformHandlerInterface $yesterday_handler */
    $yesterday_handler = $webform_schedule->getHandler('yesterday');
    $conditions = ['enabled' => [':input[name="value"]' => ['filled' => TRUE]]];
    $yesterday_handler->setConditions($conditions);
    // NOTE: Executing $webform_schedule->save() throws the below
    // unexplainable error.
    //
    // TypeError: Argument 1 passed to
    // Drupal\webform\WebformSubmissionConditionsValidator::validateConditions()
    // must be of the type array, null given
    // $webform_schedule->save() ;
    //
    // Check that 3 yesterday scheduled emails are skipped and removed.
    $stats = $scheduled_manager->cron();
    $this->assertEquals($stats['skipped'], 3);
    $this->assertEquals($scheduled_manager->stats(), [
      'total' => 0,
      'waiting' => 0,
      'queued' => 0,
      'ready' => 0,
    ]);

    // Clear yesterday conditions.
    $yesterday_handler->setConditions([]);

    /* ********************************************************************** */
    // Ignore past scheduling.
    /* ********************************************************************** */

    // Purge all submissions.
    $this->purgeSubmissions();

    // Check last year email can't be scheduled.
    $sid = $this->postSubmission($webform_schedule, ['send' => 'last_year']);
    $this->assertEquals($scheduled_manager->total($webform_schedule), 0);
    $assert_session->responseContains('<em class="placeholder">Test: Handler: Test scheduled email: Submission #' . $sid . '</em>: Email <b>ignored</b> by <em class="placeholder">Last year</em> handler to be sent on <em class="placeholder">2016-01-01</em>.');

    /* ********************************************************************** */
    // Source entity scheduling.
    /* ********************************************************************** */

    // Purge all submissions.
    $this->purgeSubmissions();

    // Create webform node.
    $webform_node = $this->createWebformNode($webform_schedule->id());
    $sids = [
      $this->postNodeSubmission($webform_node, ['send' => 'tomorrow']),
      $this->postNodeSubmission($webform_node, ['send' => 'tomorrow']),
      $this->postNodeSubmission($webform_node, ['send' => 'tomorrow']),
    ];
    $this->assertEquals($scheduled_manager->total(), 3);
    // Get first submission.
    $sid = $sids[0];
    $webform_submission = WebformSubmission::load($sid);

    // Check first submission.
    $scheduled_email = $scheduled_manager->load($webform_submission, 'tomorrow');

    // Check queued and total.
    $this->assertEquals($scheduled_manager->stats(), [
      'total' => 3,
      'waiting' => 0,
      'queued' => 3,
      'ready' => 0,
    ]);
    $this->assertEquals($scheduled_manager->stats($webform_node), [
      'total' => 3,
      'waiting' => 0,
      'queued' => 3,
      'ready' => 0,
    ]);

    // Check first submission state is send.
    $this->assertEquals($scheduled_email->send, strtotime($tomorrow));
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_SEND);

    // Check updating node reschedules emails.
    $webform_node->save();

    // Check waiting and total.
    $this->assertEquals($scheduled_manager->stats(), [
      'total' => 3,
      'waiting' => 3,
      'queued' => 0,
      'ready' => 0,
    ]);
    $this->assertEquals($scheduled_manager->stats($webform_node), [
      'total' => 3,
      'waiting' => 3,
      'queued' => 0,
      'ready' => 0,
    ]);

    // Check first submission state is reschedule.
    $scheduled_email = $scheduled_manager->load($webform_submission, 'tomorrow');
    $this->assertEquals($scheduled_email->state, $scheduled_manager::SUBMISSION_RESCHEDULE);

    // Run cron to trigger scheduling.
    $scheduled_manager->cron();

    // Check queued and total.
    $this->assertEquals($scheduled_manager->stats(), [
      'total' => 3,
      'waiting' => 0,
      'queued' => 3,
      'ready' => 0,
    ]);
    $this->assertEquals($scheduled_manager->stats($webform_node), [
      'total' => 3,
      'waiting' => 0,
      'queued' => 3,
      'ready' => 0,
    ]);

    // Check deleting node unscheduled emails.
    $webform_node->delete();
    $this->assertEquals($scheduled_manager->stats(), [
      'total' => 3,
      'waiting' => 3,
      'queued' => 0,
      'ready' => 0,
    ]);

    // Run cron to trigger unscheduling.
    $scheduled_manager->cron();
    $this->assertEquals($scheduled_manager->total(), 0);

    // Purge all submissions.
    $this->purgeSubmissions();

    /* ********************************************************************** */
    // Testing.
    /* ********************************************************************** */

    $this->drupalLogin($this->rootUser);

    // Check 'Other' email will be sent immediately message when testing.
    $this->drupalGet('/webform/test_handler_scheduled_email/test');
    $assert_session->responseContains('The <em class="placeholder">Other</em> email will be sent immediately upon submission.');

    // Check 'Other' email is sent immediately via testing.
    $this->drupalGet('/webform/test_handler_scheduled_email/test');
    $edit = ['send' => 'other', 'date[date]' => '2101-01-01'];
    $this->submitForm($edit, 'Submit');
    $this->assertEquals($scheduled_manager->total(), 0);
    $assert_session->responseContains('Webform submission from: Test: Handler: Test scheduled email</em> sent to <em class="placeholder">simpletest@example.com</em> from <em class="placeholder">Drupal</em> [<em class="placeholder">simpletest@example.com</em>');
    $assert_session->responseContains('Debug: Email: Other');

    /* ********************************************************************** */
    // Deleting a scheduled email handler should remove its scheduled emails.
    /* ********************************************************************** */

    // Purge all submissions.
    $this->purgeSubmissions();

    // Create three tomorrow scheduled emails.
    $this->postSubmission($webform_schedule, ['send' => 'tomorrow']);
    $this->postSubmission($webform_schedule, ['send' => 'tomorrow']);
    $this->postSubmission($webform_schedule, ['send' => 'tomorrow']);

    // Create three yesterday scheduled emails.
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);
    $this->postSubmission($webform_schedule, ['send' => 'yesterday']);

    // Assert that we have six scheduled emails at this point.
    $this->assertEquals(6, $scheduled_manager->total($webform_schedule));

    // Remove the tomorrow scheduled email handler.
    $tomorrow_handler = $webform_schedule->getHandler('tomorrow');
    $webform_schedule->deleteWebformHandler($tomorrow_handler);
    $webform_schedule->save();

    // Assert that we now have only three scheduled emails.
    $this->assertEquals(3, $scheduled_manager->total($webform_schedule));

    /* ********************************************************************** */
    // Orphaned webform_scheduled_email records should be removed on cron.
    /* ********************************************************************** */

    // Purge all submissions.
    $this->purgeSubmissions();

    // Simulate the creation of orphaned webform_scheduled_email records.
    $orphan_eids = [];

    // Create a submission to associate with these records.
    $orphan_sid = $this
      ->postSubmission($webform_schedule, ['send' => 'yesterday']);

    // Create an orphan with a missing handler in schedule state.
    // The $tomorrow_handler was deleted above.
    // This will get processed by WebformScheduledEmailManager::cronSchedule().
    $orphan_eids[] = $this->connection->insert('webform_scheduled_email')
      ->fields([
        'webform_id' => $webform_schedule->id(),
        'sid' => $orphan_sid,
        'handler_id' => $tomorrow_handler->getHandlerId(),
        'state' => WebformScheduledEmailManagerInterface::SUBMISSION_SCHEDULE,
        'send' => $this->time->getRequestTime() + 60,
      ])
      ->execute();
    // Create an orphan with a missing handler in send state.
    // This will get processed by WebformScheduledEmailManager::cronSend().
    $orphan_eids[] = $this->connection->insert('webform_scheduled_email')
      ->fields([
        'webform_id' => $webform_schedule->id(),
        'sid' => $orphan_sid,
        'handler_id' => $tomorrow_handler->getHandlerId(),
        'state' => WebformScheduledEmailManagerInterface::SUBMISSION_SEND,
        'send' => $this->time->getRequestTime() - 60,
      ])
      ->execute();

    // Run cron, which should clean up the orphaned records.
    $scheduled_manager->cron();

    // Assert that the orphaned records were removed.
    $count = $this->connection
      ->select('webform_scheduled_email')
      ->condition('eid', $orphan_eids, 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(0, $count);

    $orphan_eids = [];

    // Delete the submission.
    $this->purgeSubmissions();

    // Create an orphan with a missing submission in schedule state.
    // This will get processed by WebformScheduledEmailManager::cronSchedule().
    $orphan_eids[] = $this->connection->insert('webform_scheduled_email')
      ->fields([
        'webform_id' => $webform_schedule->id(),
        'sid' => $orphan_sid,
        'handler_id' => $webform_schedule->getHandler('yesterday')->getHandlerId(),
        'state' => WebformScheduledEmailManagerInterface::SUBMISSION_SCHEDULE,
        'send' => $this->time->getRequestTime() + 60,
      ])
      ->execute();
    // Create an orphan with a missing submission in send state.
    // This will get processed by WebformScheduledEmailManager::cronSend().
    $orphan_eids[] = $this->connection->insert('webform_scheduled_email')
      ->fields([
        'webform_id' => $webform_schedule->id(),
        'sid' => $orphan_sid,
        'handler_id' => $webform_schedule->getHandler('yesterday')->getHandlerId(),
        'state' => WebformScheduledEmailManagerInterface::SUBMISSION_SEND,
        'send' => $this->time->getRequestTime() - 60,
      ])
      ->execute();

    // Run cron, which should clean up the orphaned records.
    $scheduled_manager->cron();

    // Assert that the orphaned records were removed.
    $count = $this->connection
      ->select('webform_scheduled_email')
      ->condition('eid', $orphan_eids, 'IN')
      ->countQuery()
      ->execute()
      ->fetchField();
    $this->assertEquals(0, $count);

    // Assert that we have no scheduled emails.
    $this->assertEquals(0, $scheduled_manager->total($webform_schedule));
  }

  /**
   * {@inheritdoc}
   */
  protected function purgeSubmissions() {
    // Manually purge submissions to trigger deletion of scheduled emails.
    $webform_submissions = WebformSubmission::loadMultiple();
    foreach ($webform_submissions as $webform_submission) {
      $webform_submission->delete();
    }

    /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $scheduled_manager */
    $scheduled_manager = \Drupal::service('webform_scheduled_email.manager');
    $this->assertEquals($scheduled_manager->total(), 0);
  }

}
