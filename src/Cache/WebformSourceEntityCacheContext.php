<?php

namespace Drupal\webform\Cache;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\Context\CacheContextInterface;
use Drupal\webform\Plugin\WebformSourceEntityManagerInterface;

/**
 * Defines a cache context for deteched source entities.
 */
class WebformSourceEntityCacheContext implements CacheContextInterface {

  /**
   * Source Entity manager service.
   *
   * @var \Drupal\webform\Plugin\WebformSourceEntityManagerInterface
   */
  protected $sourceEntityManager;

  /**
   * WebformSourceEntityCacheContext constructor.
   *
   * @param \Drupal\webform\Plugin\WebformSourceEntityManagerInterface $source_entity_manager
   *    Webform Source entity manager service.
   */
  public function __construct(WebformSourceEntityManagerInterface $source_entity_manager) {
    $this->sourceEntityManager = $source_entity_manager;
  }


  /**
   * {@inheritdoc}
   */
  public static function getLabel() {
    return t("Webform Source Entity");
  }

  /**
   * {@inheritdoc}
   */
  public function getContext() {
    $source_entity = $this->sourceEntityManager->getSourceEntity(['webform']);
    return $source_entity ? $source_entity->id() : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata() {
    return new CacheableMetadata();
  }

}
