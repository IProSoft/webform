<?php

namespace Drupal\webform_demo_application_evaluation\Hook;

use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_demo_application_evaluation.
 */
class WebformDemoApplicationEvaluationHooks
{
    /**
     * Implements hook_ENTITY_TYPE_presave() for webform_submission entities.
     */
    #[Hook('webform_submission_presave')]
    public function webformSubmissionPresave(WebformSubmissionInterface $webform_submission)
    {
        if ($webform_submission->getWebform()->id() !== 'demo_application') {
            return;
        }
        // Get original data (with default state) and current data.
        $original_data = $webform_submission->getOriginalData() + ['state' => NULL];
        $current_data = $webform_submission->getData();
        // If submission is completed and state is not set then set state to completed.
        if ($webform_submission->isCompleted() && empty($current_data['state'])) {
            $current_data['state'] = 'completed';
        }
        // If the current state has changed then update the related element's
        // datetime. For example, if the current state is 'completed' the related
        // datetime element is called 'completed_date'.
        // @see /admin/structure/webform/manage/demo_application
        if ($original_data['state'] !== $current_data['state']) {
            /** @var \Drupal\Core\Datetime\DateFormatterInterface $date_formatter */
            $date_formatter = \Drupal::service('date.formatter');
            $current_data[$current_data['state'] . '_date'] = $date_formatter->format(time(), 'html_datetime');
            $webform_submission->setData($current_data);
        }
    }
    /**
     * Implements hook_ENTITY_TYPE_insert() for webform_submission entities.
     */
    #[Hook('webform_submission_insert')]
    public function webformSubmissionInsert(WebformSubmissionInterface $webform_submission)
    {
        _webform_demo_application_evaluation_calculate_evaluation_rating($webform_submission);
    }
    /**
     * Implements hook_ENTITY_TYPE_update() for webform_submission entities.
     */
    #[Hook('webform_submission_update')]
    public function webformSubmissionUpdate(WebformSubmissionInterface $webform_submission)
    {
        _webform_demo_application_evaluation_calculate_evaluation_rating($webform_submission);
    }
    /**
     * Implements hook_ENTITY_TYPE_delete() for webform_submission entities.
     */
    #[Hook('webform_submission_delete')]
    public function webformSubmissionDelete(WebformSubmissionInterface $webform_submission)
    {
        _webform_demo_application_evaluation_calculate_evaluation_rating($webform_submission);
    }
}
