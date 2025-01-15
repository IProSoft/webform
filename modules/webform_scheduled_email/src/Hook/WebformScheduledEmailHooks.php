<?php

namespace Drupal\webform_scheduled_email\Hook;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_scheduled_email.
 */
class WebformScheduledEmailHooks
{
    /**
     * Implements hook_config_schema_info_alter().
     */
    #[Hook('config_schema_info_alter')]
    public function configSchemaInfoAlter(&$definitions)
    {
        // Append email handler to scheduled email handler settings.
        if (isset($definitions['webform.handler.email']['mapping']) && isset($definitions['webform.handler.scheduled_email'])) {
            $definitions['webform.handler.scheduled_email']['mapping'] += $definitions['webform.handler.email']['mapping'];
        }
    }
    /**
     * Implements hook_entity_update().
     */
    #[Hook('entity_update')]
    public function entityUpdate(\Drupal\Core\Entity\EntityInterface $entity)
    {
        /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $webform_scheduled_email_manager */
        $webform_scheduled_email_manager = \Drupal::service('webform_scheduled_email.manager');
        $webform_scheduled_email_manager->reschedule($entity);
    }
    /**
     * Implements hook_entity_predelete().
     */
    #[Hook('entity_predelete')]
    public function entityPredelete(\Drupal\Core\Entity\EntityInterface $entity)
    {
        /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $webform_scheduled_email_manager */
        $webform_scheduled_email_manager = \Drupal::service('webform_scheduled_email.manager');
        $webform_scheduled_email_manager->unschedule($entity);
    }
    /**
     * Implements hook_ENTITY_TYPE_delete() for webform entities.
     */
    #[Hook('webform_delete')]
    public function webformDelete(\Drupal\webform\WebformInterface $webform)
    {
        /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $webform_scheduled_email_manager */
        $webform_scheduled_email_manager = \Drupal::service('webform_scheduled_email.manager');
        $webform_scheduled_email_manager->delete($webform);
    }
    /**
     * Implements hook_ENTITY_TYPE_delete() for webform_submission entities.
     */
    #[Hook('webform_submission_delete')]
    public function webformSubmissionDelete(\Drupal\webform\WebformSubmissionInterface $webform_submission)
    {
        /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $webform_scheduled_email_manager */
        $webform_scheduled_email_manager = \Drupal::service('webform_scheduled_email.manager');
        $webform_scheduled_email_manager->delete($webform_submission);
    }
    /**
     * Implements hook_cron().
     */
    #[Hook('cron')]
    public function cron()
    {
        /** @var \Drupal\webform_scheduled_email\WebformScheduledEmailManagerInterface $webform_scheduled_email_manager */
        $webform_scheduled_email_manager = \Drupal::service('webform_scheduled_email.manager');
        $webform_scheduled_email_manager->cron();
    }
    /**
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme()
    {
        return [
            'webform_handler_scheduled_email_summary' => [
                'variables' => [
                    'settings' => NULL,
                    'handler' => [
                    ],
                    'status' => NULL,
                ],
            ],
        ];
    }
    /**
     * Implements hook_form_FORM_ID_alter() for webform admin config handlers form.
     */
    #[Hook('form_webform_admin_config_handlers_form_alter')]
    public function formWebformAdminConfigHandlersFormAlter(&$form, \Drupal\Core\Form\FormStateInterface $form_state)
    {
        $form['webform_scheduled_email'] = [
            '#type' => 'details',
            '#title' => t('Scheduled email settings'),
            '#open' => TRUE,
            '#tree' => TRUE,
        ];
        $form['webform_scheduled_email']['schedule_type'] = [
            '#type' => 'select',
            '#title' => t('Date type'),
            '#description' => t('Scheduled emails are queued and sent via hourly <a href="@href">cron tasks</a>. To schedule an email for a specific time, site administrators must increase the cron task execution frequency.', [
                '@href' => 'https://www.drupal.org/docs/8/cron-automated-tasks/cron-automated-tasks-overview',
            ]),
            '#options' => [
                'date' => t('Date (@format)', [
                    '@format' => 'YYYY-MM-DD',
                ]),
                'datetime' => t('Date/time (@format)', [
                    '@format' => 'YYYY-MM-DD HH:MM:SS',
                ]),
            ],
            '#required' => TRUE,
            '#default_value' => \Drupal::config('webform_scheduled_email.settings')->get('schedule_type'),
        ];
        $form['#submit'][] = '_webform_scheduled_email_form_webform_admin_config_handlers_form_submit';
    }
}
