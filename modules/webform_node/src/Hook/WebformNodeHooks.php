<?php

namespace Drupal\webform_node\Hook;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\webform\Element\WebformMessage;
use Drupal\webform\Entity\Webform;
use Drupal\webform\WebformInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_node.
 */
class WebformNodeHooks
{
    /**
     * Implements hook_entity_type_alter().
     */
    #[Hook('entity_type_alter')]
    public function entityTypeAlter(array &$entity_types)
    {
        if (isset($entity_types['webform'])) {
            /** @var \Drupal\Core\Entity\ContentEntityTypeInterface $webform_entity_type */
            $webform_entity_type = $entity_types['webform'];
            $webform_entity_type->setLinkTemplate('references', '/admin/structure/webform/manage/{webform}/references');
        }
    }
    /**
     * Implements hook_entity_operation().
     */
    #[Hook('entity_operation')]
    public function entityOperation(\Drupal\Core\Entity\EntityInterface $entity)
    {
        $operations = [];
        if ($entity instanceof \Drupal\webform\WebformInterface && $entity->access('update')) {
            $operations['references'] = [
                'title' => t('References'),
                'url' => $entity->toUrl('references'),
                'weight' => 40,
            ];
        }
        return $operations;
    }
    /**
     * Implements hook_node_access().
     */
    #[Hook('node_access')]
    public function nodeAccess(\Drupal\node\NodeInterface $node, $operation, \Drupal\Core\Session\AccountInterface $account)
    {
        if (strpos($operation, 'webform_submission_') !== 0) {
            return \Drupal\Core\Access\AccessResult::neutral();
        } else {
            /** @var \Drupal\webform\WebformEntityReferenceManagerInterface $entity_reference_manager */
            $entity_reference_manager = \Drupal::service('webform.entity_reference_manager');
            // Check that the node has a webform field that has been populated.
            $webform = $entity_reference_manager->getWebform($node);
            if (!$webform) {
                return \Drupal\Core\Access\AccessResult::forbidden();
            }
            // Check administer webform submissions.
            if ($account->hasPermission('administer webform submission')) {
                return \Drupal\Core\Access\AccessResult::allowed();
            }
            // Change access to ANY submission.
            $operation = str_replace('webform_submission_', '', $operation);
            $any_permission = "{$operation} webform submissions any node";
            if ($account->hasPermission($any_permission)) {
                return \Drupal\Core\Access\AccessResult::allowed();
            }
            // Change access to submission associated with the node's webform.
            $own_permission = "{$operation} webform submissions own node";
            if ($account->hasPermission($own_permission) && $node->getOwnerId() === $account->id()) {
                return \Drupal\Core\Access\AccessResult::allowed();
            }
            return \Drupal\Core\Access\AccessResult::neutral();
        }
    }
    /**
     * Implements hook_webform_submission_query_access_alter().
     */
    #[Hook('webform_submission_query_access_alter')]
    public function webformSubmissionQueryAccessAlter(\Drupal\Core\Database\Query\AlterableInterface $query, array $webform_submission_tables)
    {
        $route_name = \Drupal::routeMatch()->getRouteName();
        if (!preg_match('/entity\.([^.]+)\.webform\.results_submissions/', $route_name, $match)) {
            return;
        }
        $entity_type = $match[1];
        $account = $query->getMetaData('account') ?: \Drupal::currentUser();
        if ($account->hasPermission('view webform submissions any node')) {
            foreach ($webform_submission_tables as $table) {
                $table['condition']->condition($table['alias'] . '.entity_type', $entity_type);
            }
        } elseif ($account->hasPermission('view webform submissions own node')) {
            $entity_id = \Drupal::routeMatch()->getRawParameter($entity_type);
            foreach ($webform_submission_tables as $table) {
                /** @var \Drupal\Core\Database\Query\SelectInterface $query */
                $condition = $query->andConditionGroup();
                $condition->condition($table['alias'] . '.entity_type', $entity_type);
                $condition->condition($table['alias'] . '.entity_id', $entity_id);
                $table['condition']->condition($condition);
            }
        }
    }
    /**
     * Implements hook_node_prepare_form().
     *
     * Prepopulate a node's webform field target id.
     *
     * @see \Drupal\webform_node\Controller\WebformNodeReferencesListController::render
     */
    #[Hook('node_prepare_form')]
    public function nodePrepareForm(\Drupal\node\NodeInterface $node, $operation, \Drupal\Core\Form\FormStateInterface $form_state)
    {
        // Only prepopulate new nodes.
        if (!$node->isNew()) {
            return;
        }
        /** @var \Drupal\webform\WebformEntityReferenceManagerInterface $entity_reference_manager */
        $entity_reference_manager = \Drupal::service('webform.entity_reference_manager');
        // Make the node has a webform (entity reference) field.
        $field_name = $entity_reference_manager->getFieldName($node);
        if (!$field_name) {
            return;
        }
        // Populate the node's title, webform field id and default data.
        $query = \Drupal::request()->query->all();
        $webform_id = $query['webform_id'] ?? NULL;
        if ($webform_id && ($webform = \Drupal\webform\Entity\Webform::load($webform_id))) {
            $node->{$field_name}->target_id = $webform_id;
            $node->title->value = $query['webform_title'] ?? $webform->label();
            $webform_default_data = $query['webform_default_data'] ?? NULL;
            if ($webform_default_data) {
                $node->{$field_name}->default_data = is_array($webform_default_data) ? \Drupal\Component\Serialization\Yaml::encode($webform_default_data) : $webform_default_data;
            }
        }
    }
    /**
     * Implements hook_node_delete().
     *
     * Remove user specified entity references.
     */
    #[Hook('node_delete')]
    public function nodeDelete(\Drupal\node\NodeInterface $node)
    {
        /** @var \Drupal\webform\WebformEntityReferenceManagerInterface $entity_reference_manager */
        $entity_reference_manager = \Drupal::service('webform.entity_reference_manager');
        $entity_reference_manager->deleteUserWebformId($node);
    }
    /**
     * Implements hook_field_widget_single_element_WIDGET_TYPE_form_alter().
     */
    #[Hook('field_widget_single_element_webform_entity_reference_autocomplete_form_alter')]
    public function fieldWidgetSingleElementWebformEntityReferenceAutocompleteFormAlter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context)
    {
        static $once;
        if (!empty($once)) {
            return;
        }
        $once = TRUE;
        // Make sure the 'target_id' is included.
        if (!isset($element['target_id'])) {
            return;
        }
        // Display a warning message if webform query string parameter is missing.
        if (empty($element['target_id']['#default_value'])) {
            $element['target_id']['#attributes']['class'][] = 'js-target-id-webform-node-references';
            $element['webform_node_references'] = [
                '#type' => 'webform_message',
                '#message_type' => 'info',
                '#message_close' => TRUE,
                '#message_id' => 'webform_node.references',
                '#message_storage' => \Drupal\webform\Element\WebformMessage::STORAGE_USER,
                '#message_message' => t('Webforms must first be <a href=":href">created</a> before referencing them.', [
                    ':href' => \Drupal\Core\Url::fromRoute('entity.webform.collection')->toString(),
                ]),
                '#cache' => [
                    'max-age' => 0,
                ],
                '#weight' => -10,
                '#states' => [
                    'visible' => [
                        '.js-target-id-webform-node-references' => [
                            'value' => '',
                        ],
                    ],
                ],
            ];
        }
    }
    /**
     * Implements hook_field_widget_single_element_WIDGET_TYPE_form_alter().
     */
    #[Hook('field_widget_single_element_webform_entity_reference_select_form_alter')]
    public function fieldWidgetSingleElementWebformEntityReferenceSelectFormAlter(&$element, \Drupal\Core\Form\FormStateInterface $form_state, $context)
    {
        webform_node_field_widget_single_element_webform_entity_reference_autocomplete_form_alter($element, $form_state, $context);
    }
}
