<?php

declare(strict_types=1);

namespace Drupal\Tests\webform\Kernel\Plugin\ConfigAction;

use Drupal\Core\Config\Action\ConfigActionException;
use Drupal\Core\Config\Action\ConfigActionManager;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Tests\BrowserTestBase;
use Webmozart\Assert\Assert;

/**
 * Tests the "addCaptchaElement" config action.
 *
 * @group webform
 */
class AddCaptchaElementTest extends BrowserTestBase {

  const CONFIG_NAME = 'webform.webform.contact';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['captcha', 'system', 'user', 'webform'];

  protected $defaultTheme = 'stark';

  protected $profile = 'minimal';

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
    // Apply the config action.
    $this->configActionManager->applyAction(
      'addCaptchaElement',
      self::CONFIG_NAME,
      '1',
    );

    $entity = $this->configManager->loadConfigEntityByName(self::CONFIG_NAME);

    $elements = $entity->getElementsDecoded();
    $this->assertArrayHasKey('captcha', $elements);
    $this->assertEquals($elements['captcha'], ['#type' => 'captcha']);
  }

  /**
   * Tests adding a CAPTCHA element to a with non true value.
   */
  public function testAddCaptchaElementNonTrueValue(): void {
    // Apply the config action.
    $this->configActionManager->applyAction(
      'addCaptchaElement',
      self::CONFIG_NAME,
      '',
    );

    $entity = $this->configManager->loadConfigEntityByName(self::CONFIG_NAME);

    $elements = $entity->getElementsDecoded();
    $this->assertArrayNotHasKey('captcha', $elements);
  }

  /**
   * Tests a non-webform config.
   */
  public function testAddCaptchaElementWithInvalidConfigName(): void {
    $this->expectException(ConfigActionException::class);
    $this->configActionManager->applyAction(
      'addCaptchaElement',
      'webform.webform_options.state_codes',
      '1',
    );
  }

  /**
   * Tests a non-webform config.
   *
   * @dataProvider dataProviderForNonStringValue
   */
  public function testAddCaptchaElementNonStringValue(mixed $value): void {
    $this->expectException(\AssertionError::class);
    $this->configActionManager->applyAction(
      'addCaptchaElement',
      'webform.webform_options.state_codes',
      $value,
    );
  }

  /**
   * Data provider test thet the non string values.
   *
   * @return array
   */
  public function dataProviderForNonStringValue(): array {
    return [
      'Test a boolean' => [TRUE],
      'Test an array' => [['value' => TRUE]],
    ];
  }

}
