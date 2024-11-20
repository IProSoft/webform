<?php

namespace Drupal\webform_devel\Hook;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Utility\WebformYaml;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_devel.
 */
class WebformDevelHooks
{
    /**
     * Implements hook_webform_help_info().
     */
    #[Hook('webform_help_info')]
    public function webformHelpInfo()
    {
        $help = [];
        $help['webform_devel_form_api_export'] = [
            'group' => 'forms',
            'title' => t('Form API Export'),
            'content' => t("The <strong>Form API export</strong> page demonstrates how a webform's elements may be used to create custom configuration forms."),
            'routes' => [
                // @see /admin/structure/webform/manage/{webform}/fapi
                'entity.webform.fapi_export_form',
            ],
        ];
        return $help;
    }
    /**
     * Implements hook_entity_type_alter().
     */
    #[Hook('entity_type_alter')]
    public function entityTypeAlter(array &$entity_types)
    {
        if (isset($entity_types['webform'])) {
            /** @var \Drupal\Core\Entity\ContentEntityTypeInterface $entity_type */
            $entity_type = $entity_types['webform'];
            $handlers = $entity_type->getHandlerClasses();
            $handlers['form']['fapi_export'] = 'Drupal\webform_devel\Form\WebformDevelEntityFormApiExportForm';
            $handlers['form']['fapi_test'] = 'Drupal\webform_devel\Form\WebformDevelEntityFormApiTestForm';
            $entity_type->setHandlerClass('form', $handlers['form']);
        }
    }
    /**
     * Implements hook_form_FORM_ID_alter() for config single export form.
     */
    #[Hook('form_config_single_export_form_alter')]
    public function formConfigSingleExportFormAlter(&$form, FormStateInterface $form_state)
    {
        $form['export']['#type'] = 'webform_codemirror';
        $form['export']['#mode'] = 'yaml';
        $form['config_name']['#ajax']['callback'] = '_webform_devel_form_config_single_export_form_update_export';
    }
}
