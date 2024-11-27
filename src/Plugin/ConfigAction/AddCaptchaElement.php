<?php

declare(strict_types=1);

namespace Drupal\webform\Plugin\ConfigAction;

use Drupal\Core\Config\Action\Attribute\ConfigAction;
use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\webform\WebformInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add a CAPTCHA form element to a webform.
 */
#[ConfigAction(
  id: 'addCaptchaElement',
  entity_types: ['webform'],
  admin_label: new TranslatableMarkup('Add CAPTCHA form element'),
)]
final class AddCaptchaElement implements ConfigActionPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Constructs a SimpleConfigUpdate object.
   *
   * @param \Drupal\Core\Config\ConfigManagerInterface $configManager
   *   The config factory.
   */
  public function __construct(
    protected readonly ConfigManagerInterface $configManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    return new static($container->get(ConfigManagerInterface::class),);
  }

  /**
   * {@inheritdoc}
   */
  public function apply(string $configName, mixed $value): void {
    assert(is_string($value));
    // Convert the value to a boolean.
    $selection = (bool) $value;
    if ($selection === FALSE) {
      // No need to throw an exception, just return as if it was successful.
      return;
    }

    // Load the webform entity.
    $entity = $this->configManager->loadConfigEntityByName($configName);
    if (!$entity instanceof WebformInterface) {
      throw new ConfigActionException(sprintf("Cannot determine webform from %s", $configName));
    }

    $element = $entity->getElement('captcha');
    // Guard a duplicate CAPTCHA element.
    if (!is_null($element)) {
      // No need to throw an exception, just return as if it was successful.
      return;
    }

    $elements = $entity->getElementsDecoded();
    $elements['captcha'] = [
      '#type' => 'captcha',
    ];
    $entity->setElements($elements);
    $entity->save();
  }

}
