<?php

namespace Drupal\webform\Plugin\Field\FieldFormatter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'Webform rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "webform_entity_reference_entity_view",
 *   label = @Translation("Webform"),
 *   description = @Translation("Display the referenced webform with default submission data."),
 *   field_types = {
 *     "webform"
 *   }
 * )
 */
class WebformEntityReferenceEntityFormatter extends WebformEntityReferenceFormatterBase {

  /**
   * The route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The webform message manager.
   *
   * @var \Drupal\webform\WebformMessageManagerInterface
   */
  protected $messageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    $instance->messageManager = $container->get('webform.message_manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_entity' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = $this->t('Set submission source entity: @source_entity', ['@source_entity' => $this->getSetting('source_entity') ? $this->t('Yes') : $this->t('No')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    if ($this->fieldDefinition->getTargetEntityTypeId() === 'paragraph') {
      $title = $this->t("Use this paragraph field's main entity as the webform submission's source entity.");
      $description = $this->t("If unchecked, the current page's entity will be used as the webform submission's source entity.");
    }
    else {
      $entity_type_definition = $this->entityTypeManager->getDefinition($this->fieldDefinition->getTargetEntityTypeId());
      $title = $this->t("Use this field's %entity_type entity as the webform submission's source entity.", ['%entity_type' => $entity_type_definition->getLabel()]);
      $description = $this->t("If unchecked, the current page's entity will be used as the webform submission's source entity. For example, if this webform was displayed on a node's page, the current node would be used as the webform submission's source entity.", ['%entity_type' => $entity_type_definition->getLabel()]);
    }

    $form = parent::settingsForm($form, $form_state);
    $form['source_entity'] = [
      '#title' => $title,
      '#description' => $description,
      '#type' => 'checkbox',
      '#return_type' => TRUE,
      '#default_value' => $this->getSetting('source_entity'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get source entity, which is the entity that the webform
    // is directly attached to. For Paragraphs this would be the field's
    // paragraph entity.
    $source_entity = $items->getEntity();

    // Determine if webform is previewed within a Paragraph on
    // node edit forms (via *.edit_form or .content_translation_add routes).
    $route = $this->routeMatch->getRouteName();
    $is_node_edit = (preg_match('/\.edit_form$/', $route) || preg_match('/\.content_translation_add$/', $route));
    $is_paragraph = ($source_entity && $source_entity->getEntityTypeId() === 'paragraph');
    $is_paragraph_node_edit = ($is_paragraph && $is_node_edit);

    $elements = [];
    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      if ($is_paragraph_node_edit) {
        // Webform can not be nested within node edit form because the nested
        // <form> tags will cause unexpected validation issues.
        $elements[$delta] = [
          '#type' => 'webform_message',
          '#message_message' => $this->t('%label webform can not be previewed when editing content.', ['%label' => $entity->label()]),
          '#message_type' => 'info',
        ];
      }
      else {
        $elements[$delta] = [
          '#type' => 'webform',
          '#webform' => $entity,
          '#default_data' => (!empty($items[$delta]->default_data)) ? Yaml::decode($items[$delta]->default_data) : [],
          '#entity' => ($this->getSetting('source_entity')) ? $source_entity : NULL,
        ];
      }
      $this->setCacheContext($elements[$delta], $entity, $items[$delta]);
    }
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity) {
    // Always allow access so that the Webform element can determine if the
    // Webform is accessible or an access denied message should be displayed.
    // @see \Drupal\webform\Element\Webform
    return AccessResult::allowed();
  }

}
