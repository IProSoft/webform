<?php

namespace Drupal\webform\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Element\WebformMessage;
use Drupal\webform\WebformSubmissionForm;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform.
 */
class WebformFormAlterHooks
{
    /**
     * Implements hook_form_alter().
     */
    #[Hook('form_alter')]
    public function formAlter(&$form, FormStateInterface $form_state, $form_id)
    {
        if (strpos($form_id, 'webform_') === FALSE || strpos($form_id, 'node_') === 0) {
            return;
        }
        // Get form object.
        $form_object = $form_state->getFormObject();
        // Alter the webform submission form.
        if (strpos($form_id, 'webform_submission') === 0 && $form_object instanceof \Drupal\webform\WebformSubmissionForm) {
            // Make sure webform libraries are always attached to submission form.
            _webform_page_attachments($form);
            // After build.
            $form['#after_build'][] = '_webform_form_webform_submission_form_after_build';
        }
        // Display editing original language warning.
        if (\Drupal::moduleHandler()->moduleExists('config_translation') && preg_match('/^entity.webform.(?:edit|settings|assets|access|handlers|third_party_settings)_form$/', \Drupal::routeMatch()->getRouteName() ?? '')) {
            /** @var \Drupal\webform\WebformInterface $webform */
            $webform = \Drupal::routeMatch()->getParameter('webform');
            /** @var \Drupal\Core\Language\LanguageManagerInterface $language_manager */
            $language_manager = \Drupal::service('language_manager');
            // If current webform is translated, load the base (default) webform and apply
            // the translation to the elements.
            if ($webform->getLangcode() !== $language_manager->getCurrentLanguage()->getId()) {
                $original_language = $language_manager->getLanguage($webform->getLangcode());
                if ($original_language) {
                    $form['langcode_message'] = [
                        '#type' => 'webform_message',
                        '#message_type' => 'warning',
                        '#message_message' => t('You are editing the original %language language for this webform.', [
                            '%language' => $original_language->getName(),
                        ]),
                        '#message_close' => TRUE,
                        '#message_storage' => \Drupal\webform\Element\WebformMessage::STORAGE_LOCAL,
                        '#message_id' => $webform->id() . '.original_language',
                        '#weight' => -100,
                    ];
                }
            }
        }
        // Add details 'toggle all' to all webforms (except submission forms).
        if (!$form_object instanceof \Drupal\webform\WebformSubmissionForm) {
            $form['#attributes']['class'][] = 'js-webform-details-toggle';
            $form['#attributes']['class'][] = 'webform-details-toggle';
            $form['#attached']['library'][] = 'webform/webform.element.details.toggle';
            return;
        }
    }
    /* ************************************************************************** */
    // Update manager.
    /* ************************************************************************** */
    /**
     * Implements hook_form_FORM_ID_alter() for update manager update form.
     *
     * Add warnings when attempting to update the Webform module using
     * the 'Update manager'.
     *
     * @see https://www.drupal.org/project/webform/issues/2930116
     * @see https://www.drupal.org/project/webform/issues/2920095
     */
    #[Hook('form_update_manager_update_form_alter')]
    public function formUpdateManagerUpdateFormAlter(&$form, FormStateInterface $form_state)
    {
        if (!isset($form['projects']) || !isset($form['projects']['#options']['webform'])) {
            return;
        }
        // Display dismissible warning at the top of the page.
        $t_args = [
            ':href_manual' => 'https://www.drupal.org/docs/user_guide/en/extend-manual-install.html',
            ':href_drush' => 'https://www.drupal.org/docs/user_guide/en/security-update-module.html',
        ];
        $form['webform_update_manager_warning'] = [
            '#type' => 'webform_message',
            '#message_type' => 'warning',
            '#message_message' => t('The Webform module may not update properly using this administrative interface. It is strongly recommended that you update the Webform module <a href=":href_manual">manually</a> or by using <a href=":href_drush">Drush</a>.', $t_args),
            '#message_close' => TRUE,
            '#message_storage' => \Drupal\webform\Element\WebformMessage::STORAGE_SESSION,
            '#weight' => -10,
        ];
        // Display warning to backup site when webform is checked.
        $form['projects']['#options']['webform']['title']['data'] = [
            'title' => $form['projects']['#options']['webform']['title']['data'],
            'container' => [
                '#type' => 'container',
                '#states' => [
                    'visible' => [
                        ':input[name="projects[webform]"]' => [
                            'checked' => TRUE,
                        ],
                    ],
                ],
                '#attributes' => [
                    'class' => [
                        'js-form-wrapper',
                    ],
                    'style' => 'display:none',
                ],
                'message' => [
                    '#type' => 'webform_message',
                    '#message_type' => 'warning',
                    '#message_message' => t('Please make sure to backup your website before updating the Webform module.'),
                ],
            ],
        ];
    }
    /* ************************************************************************** */
    // Views.
    /* ************************************************************************** */
    /**
     * Implements hook_form_FORM_ID_alter() for views exposed form.
     */
    #[Hook('form_views_exposed_form_alter')]
    public function formViewsExposedFormAlter(&$form, FormStateInterface $form_state, $form_id)
    {
        /** @var \Drupal\views\ViewExecutable $view */
        $view = $form_state->get('view');
        // Check if this a is webform submission view.
        // @see \Drupal\webform\WebformSubmissionListBuilder::buildSubmissionViews
        if (isset($view->webform_submission_view)) {
            $form['#action'] = \Drupal\Core\Url::fromRoute(\Drupal::routeMatch()->getRouteName(), \Drupal::routeMatch()->getRawParameters()->all())->toString();
        }
    }
    /* ************************************************************************** */
    // SMTP.
    /* ************************************************************************** */
    /**
     * Implements hook_form_FORM_ID_alter() for SMTP admin settings form.
     */
    #[Hook('form_smtp_admin_settings_alter')]
    public function formSmtpAdminSettingsAlter(&$form, FormStateInterface $form_state)
    {
        $form['#submit'][] = '_webform_form_smtp_admin_settings_submit';
    }
    /* ************************************************************************** */
    // Configuration management.
    /* ************************************************************************** */
    /**
     * Implements hook_form_FORM_ID_alter() for config single import form.
     */
    #[Hook('form_config_single_import_form_alter')]
    public function formConfigSingleImportFormAlter(&$form, FormStateInterface $form_state)
    {
        $config_type = \Drupal::request()->query->get('config_type');
        if ($config_type === 'webform') {
            $form['config_type']['#default_value'] = 'webform';
        }
    }
}
