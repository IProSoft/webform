<?php

namespace Drupal\webform_access\Hook;

use Drupal\webform_access\Entity\WebformAccessGroup;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Database\Query\AlterableInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for webform_access.
 */
class WebformAccessHooks {

  /**
   * Implements hook_webform_help_info().
   */
  #[Hook('webform_help_info')]
  public function webformHelpInfo() {
    $help = [];
    // Access group.
    $help['webform_access_group'] = [
      'group' => 'access',
      'title' => t('Webform Access: Group'),
      'content' => t('The <strong>Access group</strong> page lists reusable groups used to access webform source entity and users.'),
      'video_id' => 'access',
      'routes' => [
              // @see /admin/structure/webform/access/group/manage
        'entity.webform_access_group.collection',
      ],
    ];
    // Access type.
    $help['webform_access_type'] = [
      'type' => 'access',
      'title' => t('Webform Access: Type'),
      'content' => t('The <strong>Access type</strong> page lists types of groups used to send email notifications to users.'),
      'video_id' => 'access',
      'routes' => [
              // @see /admin/structure/webform/access/type/manage
        'entity.webform_access_type.collection',
      ],
    ];
    return $help;
  }

  /* ************************************************************************** */
  // Delete relationship hooks.
  /* ************************************************************************** */

  /**
   * Implements hook_user_delete().
   */
  #[Hook('user_delete')]
  public function userDelete(EntityInterface $entity) {
    \Drupal::database()->delete('webform_access_group_user')->condition('uid', $entity->id())->execute();
  }

  /**
   * Implements hook_node_delete().
   */
  #[Hook('node_delete')]
  public function nodeDelete(EntityInterface $entity) {
    \Drupal::database()->delete('webform_access_group_entity')->condition('entity_type', 'node')->condition('entity_id', $entity->id())->execute();
  }

  /**
   * Implements hook_field_config_delete().
   */
  #[Hook('field_config_delete')]
  public function fieldConfigDelete(EntityInterface $entity) {
    /** @var \Drupal\field\Entity\FieldConfig $definition */
    if ($entity->getType() === 'webform' && $entity->getEntityTypeId() === 'node') {
      $entity_ids = \Drupal::entityQuery('webform_access_group')->condition('type', $entity->getTargetBundle())->execute();
      if ($entity_ids) {
        \Drupal::database()->delete('webform_access_group_entity')->condition('entity_type', 'node')->condition('entity_id', $entity_ids, 'IN')->condition('field_name', $entity->getName())->execute();
      }
    }
  }

  /**
   * Implements hook_field_storage_config_delete().
   */
  #[Hook('field_storage_config_delete')]
  public function fieldStorageConfigDelete(EntityInterface $entity) {
    /** @var \Drupal\field\Entity\FieldStorageConfig $entity */
    if ($entity->getType() === 'webform') {
      \Drupal::database()->delete('webform_access_group_entity')->condition('entity_type', $entity->getEntityTypeId())->condition('field_name', $entity->getName())->execute();
    }
  }

  /* ************************************************************************** */
  // Access checking.
  /* ************************************************************************** */

  /**
   * Implements hook_menu_local_tasks_alter().
   *
   * Add webform access group to local task cacheability.
   *
   * @see \Drupal\Core\Menu\Plugin\Block\LocalTasksBlock::build
   */
  #[Hook('menu_local_tasks_alter')]
  public function menuLocalTasksAlter(&$data, $route_name) {
    // Change config entities 'Translate *' tab to be just label 'Translate'.
    $webform_entities = ['webform_access_group', 'webform_access_type'];
    foreach ($webform_entities as $webform_entity) {
      if (isset($data['tabs'][0]["config_translation.local_tasks:entity.{$webform_entity}.config_translation_overview"]['#link']['title'])) {
        $data['tabs'][0]["config_translation.local_tasks:entity.{$webform_entity}.config_translation_overview"]['#link']['title'] = t('Translate');
      }
    }
    $route_name = \Drupal::routeMatch()->getRouteName();
    if ($route_name !== 'entity.node.canonical' && strpos($route_name, 'entity.node.webform.') !== 0) {
      return;
    }
    /** @var \Drupal\webform\WebformRequestInterface $request_handler */
    $request_handler = \Drupal::service('webform.request');
    $account = \Drupal::currentUser();
    $webform = $request_handler->getCurrentWebform();
    $source_entity = $request_handler->getCurrentSourceEntity();
    if (!$webform || $source_entity) {
      return;
    }
    /** @var \Drupal\webform_access\WebformAccessGroupStorageInterface $webform_access_group */
    $webform_access_group_storage = \Drupal::entityTypeManager()->getStorage('webform_access_group');
    $webform_access_groups = $webform_access_group_storage->loadByEntities($webform, $source_entity, $account);
    if (empty($webform_access_groups)) {
      return;
    }
    /** @var \Drupal\Core\Cache\CacheableMetadata $cacheability */
    $cacheability = $data['cacheability'];
    foreach ($webform_access_groups as $webform_access_group) {
      $cacheability->addCacheableDependency($webform_access_group);
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_access() for webform entities.
   */
  #[Hook('webform_access')]
  public function webformAccess(WebformInterface $webform, $operation, AccountInterface $account) {
    // Prevent recursion when a webform is being passed as the source entity
    // via the URL.
    // @see \Drupal\webform\Plugin\WebformSourceEntity\QueryStringWebformSourceEntity::getSourceEntity
    if (\Drupal::request()->query->get('source_entity_type') === 'webform') {
      return AccessResult::neutral();
    }
    /** @var \Drupal\webform\WebformRequestInterface $request_handler */
    $request_handler = \Drupal::service('webform.request');
    $source_entity = $request_handler->getCurrentSourceEntity(['webform_submission']);
    if (!$source_entity) {
      return AccessResult::neutral();
    }
    /** @var \Drupal\webform_access\WebformAccessGroupStorageInterface $webform_access_group */
    $webform_access_group_storage = \Drupal::entityTypeManager()->getStorage('webform_access_group');
    $webform_access_groups = $webform_access_group_storage->loadByEntities($webform, $source_entity, $account);
    if (empty($webform_access_groups)) {
      return AccessResult::neutral();
    }
    $permission = str_replace('submission_', '', $operation);
    foreach ($webform_access_groups as $webform_access_group) {
      $permissions = $webform_access_group->get('permissions');
      if (in_array('administer', $permissions) || in_array($permission, $permissions)) {
        return AccessResult::allowed()->cachePerUser()->addCacheableDependency($webform_access_group);
      }
    }
    // No opinion.
    return AccessResult::neutral();
  }

  /**
   * Implements hook_ENTITY_TYPE_access() for webform_submission entities.
   */
  #[Hook('webform_submission_access')]
  public function webformSubmissionAccess(WebformSubmissionInterface $webform_submission, $operation, AccountInterface $account) {
    if (!in_array($operation, ['view', 'update', 'delete'])) {
      return AccessResult::neutral();
    }
    $webform = $webform_submission->getWebform();
    $source_entity = $webform_submission->getSourceEntity();
    if (!$source_entity) {
      return AccessResult::neutral();
    }
    /** @var \Drupal\webform_access\WebformAccessGroupStorageInterface $webform_access_group */
    $webform_access_group_storage = \Drupal::entityTypeManager()->getStorage('webform_access_group');
    $webform_access_groups = $webform_access_group_storage->loadByEntities($webform, $source_entity, $account);
    if (empty($webform_access_groups)) {
      return AccessResult::neutral();
    }
    foreach ($webform_access_groups as $webform_access_group) {
      $permissions = $webform_access_group->get('permissions');
      if (in_array('administer', $permissions) || in_array($operation . '_any', $permissions) || in_array($operation . '_own', $permissions) && $webform_submission->getOwnerId() === $account->id()) {
        return AccessResult::allowed()->cachePerUser()->addCacheableDependency($webform_access_group);
      }
    }
    // No opinion.
    return AccessResult::neutral();
  }

  /**
   * Implements hook_webform_submission_query_access_alter().
   */
  #[Hook('webform_submission_query_access_alter')]
  public function webformSubmissionQueryAccessAlter(AlterableInterface $query, array $webform_submission_tables) {
    $account = $query->getMetaData('account') ?: \Drupal::currentUser();
    // Collect access group ids with 'view_any' or 'administer' permissions.
    /** @var \Drupal\webform_access\WebformAccessGroupStorageInterface $access_group_storage */
    $access_group_storage = \Drupal::entityTypeManager()->getStorage('webform_access_group');
    /** @var \Drupal\webform_access\WebformAccessGroupInterface $access_group */
    $access_groups = $access_group_storage->loadByEntities(NULL, NULL, $account);
    $access_any_group_ids = [];
    $access_own_group_ids = [];
    foreach ($access_groups as $access_group) {
      $access_group_permissions = $access_group->get('permissions');
      $access_group_permissions = array_combine($access_group_permissions, $access_group_permissions);
      if (isset($access_group_permissions['view_any']) || isset($access_group_permissions['administer'])) {
        $access_any_group_ids[] = $access_group->id();
      }
      elseif (isset($access_group_permissions['view_own'])) {
        $access_own_group_ids[] = $access_group->id();
      }
    }
    if ($access_any_group_ids) {
      // Add access group entity type, entity id, and webform id to the query.
      $result = \Drupal::database()->select('webform_access_group_entity', 'ge')->fields('ge', ['entity_type', 'entity_id', 'webform_id'])->condition('group_id', $access_any_group_ids, 'IN')->execute();
      while ($record = $result->fetchAssoc()) {
        foreach ($webform_submission_tables as $table) {
          /** @var \Drupal\Core\Database\Query\SelectInterface $query */
          $condition = $query->andConditionGroup();
          $condition->condition($table['alias'] . '.entity_type', $record['entity_type']);
          $condition->condition($table['alias'] . '.entity_id', (string) $record['entity_id']);
          $condition->condition($table['alias'] . '.webform_id', $record['webform_id']);
          $table['condition']->condition($condition);
        }
      }
    }
    if ($access_own_group_ids) {
      // Add access group entity type, entity id, and webform id to the query.
      $result = \Drupal::database()->select('webform_access_group_entity', 'ge')->fields('ge', ['entity_type', 'entity_id', 'webform_id'])->condition('group_id', $access_own_group_ids, 'IN')->execute();
      while ($record = $result->fetchAssoc()) {
        foreach ($webform_submission_tables as $table) {
          /** @var \Drupal\Core\Database\Query\SelectInterface $query */
          $condition = $query->andConditionGroup();
          $condition->condition($table['alias'] . '.uid', $account->id());
          $condition->condition($table['alias'] . '.entity_type', $record['entity_type']);
          $condition->condition($table['alias'] . '.entity_id', (string) $record['entity_id']);
          $condition->condition($table['alias'] . '.webform_id', $record['webform_id']);
          $table['condition']->condition($condition);
        }
      }
    }
  }

  /* ************************************************************************** */
  // Webform access groups (node) entity.
  /* ************************************************************************** */

  /**
   * Implements hook_field_widget_single_element_form_alter().
   */
  #[Hook('field_widget_single_element_form_alter')]
  public function fieldWidgetSingleElementFormAlter(&$element, FormStateInterface $form_state, $context) {
    /** @var \Drupal\Core\Field\FieldItemListInterface $items */
    $items = $context['items'];
    $field_definition = $items->getFieldDefinition();
    if ($field_definition->getType() !== 'webform') {
      return;
    }
    // Get the target entity.
    $entity = $items->getEntity();
    // Only nodes are currently supported.
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }
    $default_value = $entity->id() ? \Drupal::database()->select('webform_access_group_entity', 'ge')->fields('ge', ['group_id'])->condition('entity_type', $entity->getEntityTypeId())->condition('entity_id', $entity->id())->condition('webform_id', $element['target_id']['#default_value'])->condition('field_name', $field_definition->getName())->execute()->fetchCol() : ($items->webform_access_group ?: []);
    $element['settings']['webform_access_group'] = _webform_access_group_build_element($default_value, [], $form_state);
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter().
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(&$form, FormStateInterface $form_state, $form_id) {
    /** @var \Drupal\webform\WebformEntityReferenceManagerInterface $entity_reference_manager */
    $entity_reference_manager = \Drupal::service('webform.entity_reference_manager');
    $node = $form_state->getFormObject()->getEntity();
    $field_names = $entity_reference_manager->getFieldNames($node);
    if ($field_names) {
      $form['actions']['submit']['#submit'][] = '_webform_access_form_node_form_submit';
    }
  }

  /* ************************************************************************** */
  // Webform access group users.
  /* ************************************************************************** */

  /**
   * Implements hook_form_FORM_ID_alter() for user form.
   *
   * Add the webform access group to an individual user's account page.
   */
  #[Hook('form_user_form_alter')]
  public function formUserFormAlter(&$form, FormStateInterface $form_state) {
    // Make sure some webform access groups exist before displaying
    // the webform access details widget.
    if (!WebformAccessGroup::loadMultiple()) {
      return;
    }
    // Only display the webform access detail widget if the current user can
    // administer webform and users.
    if (!\Drupal::currentUser()->hasPermission('administer webform') || !\Drupal::currentUser()->hasPermission('administer users')) {
      return;
    }
    $account = $form_state->getFormObject()->getEntity();
    $default_value = \Drupal::database()->select('webform_access_group_user', 'gu')->fields('gu', ['group_id'])->condition('uid', $account->id())->execute()->fetchCol();
    $form['webform_access'] = [
      '#type' => 'details',
      '#title' => t('Webform access'),
      '#open' => TRUE,
      '#weight' => 5,
    ];
    $form['webform_access']['webform_access_group'] = _webform_access_group_build_element($default_value, $form, $form_state);
    $form['actions']['submit']['#submit'][] = '_webform_access_user_profile_form_submit';
  }

}
