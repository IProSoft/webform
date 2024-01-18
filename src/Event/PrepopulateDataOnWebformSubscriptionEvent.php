<?php

declare(strict_types=1);

namespace Drupal\webform\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Allows prepopulating data on Webform submission forms.
 */
class PrepopulateDataOnWebformSubscriptionEvent extends Event {

  /**
   * Constructs an object.
   *
   * @param array $data
   *   The Webform submission data.
   * @param \Drupal\webform\WebformSubmissionInterface $webform_submission
   *   The Webform submission.
   */
  public function __construct(
    public array &$data,
    protected WebformSubmissionInterface $webform_submission
  ) {}

  /**
   * Returns the Webform submission.
   *
   * @return \Drupal\webform\WebformSubmissionInterface
   *   The Webform submission.
   */
  public function getWebformSubmission(): WebformSubmissionInterface {
    return $this->webform_submission;
  }

}
