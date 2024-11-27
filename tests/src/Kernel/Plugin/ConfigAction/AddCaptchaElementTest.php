<?php

declare(strict_types=1);

namespace Drupal\Tests\webform\Kernel\Plugin\ConfigAction;

use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the "addCaptchaElement" config action.
 *
 * @group webform
 */
class AddCaptchaElementTest extends KernelTestBase {

  const CONFIG_NAME = 'webform.webform.contact';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['captcha', 'system', 'webform'];

  /**
   * The config action manager.
   *
   * @var \Drupal\Core\Config\Action\ConfigActionManager
   */
  private readonly ConfigActionManager $configActionManager;

  /**
   * The config manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  private readonly ConfigManagerInterface $configManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->configActionManager = $this->container->get('plugin.manager.config_action');
    $this->configManager = $this->container->get(ConfigManagerInterface::class);
  }

  /**
   * Tests adding a CAPTCHA element to a webform.
   */
  public function testAddCaptchaElement(): void {
    // Apply the config action
    $this->configActionManager->applyAction(
      'addCaptchaElement',
      self::CONFIG_NAME,
      1,
    );

    $entity = $this->configManager->loadConfigEntityByName(self::CONFIG_NAME);

    $elements = $entity->getElementsDecoded();
    $this->assertArrayHasKey('captcha', $elements);
    $this->assertEqual($elements['captcha'], ['#type' => 'captcha']);
  }

  /**
   * Tests a non-webform config.
   */
  public function testAddCaptchaElementWithInvalidConfigName(): void {
    $this->expectException(ConfigActionException::class);
    $this->configActionManager->applyAction(
      'addCaptchaElement',
      'webform.webform_options.state_codes',
      1,
    );
  }

}