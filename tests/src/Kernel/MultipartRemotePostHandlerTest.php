<?php

declare(strict_types=1);

namespace Drupal\Tests\webform\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\WebformInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

/**
 * Tests the RemotePostWebformHandler.
 *
 * @group webform
 */
final class MultipartRemotePostHandlerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'webform',
  ];

  protected ClientInterface $httpClient;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installSchema('webform', ['webform']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('webform');
    $this->installEntitySchema('webform_submission');

    // Mock the HttpClient.
    $this->httpClient = $this->createMock(Client::class);
    $this->container->set('http_client', $this->httpClient);
  }

  /**
   * Tests the handler does a post request with the expected multipart format.
   */
  public function testHandler() {
    $webform = $this->buildWebform();
    $submission = WebformSubmission::create([
      'webform_id' => $webform->id(),
      'data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@example.com',
        'role' => 'Developer',
        'groups' => [33, 35],
        'lists' => [34, 197],
      ],
    ]);
    $this->httpClient->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('http://example.com'),
        $this->equalTo([
          'multipart' => [
            ['name' => 'first_name', 'contents' => 'John'],
            ['name' => 'last_name', 'contents' => 'Doe'],
            ['name' => 'email', 'contents' => 'john.doe@example.com'],
            ['name' => 'role', 'contents' => 'Developer'],
            ['name' => 'groups', 'contents' => '33'],
            ['name' => 'groups', 'contents' => '35'],
            ['name' => 'lists', 'contents' => '34'],
            ['name' => 'lists', 'contents' => '197'],
          ],
        ]),
      );
    $submission->save();
  }

  /**
   * Builds the webform.
   */
  private function buildWebform(): WebformInterface {
    $webform = Webform::create([
      'id' => 'multipart_webform',
      'title' => 'Multipart Webform',
    ]);

    $webform->setElements([
      'first_name' => [
        '#type' => 'textfield',
        '#title' => 'First name',
      ],
      'last_name' => [
        '#type' => 'textfield',
        '#title' => 'Last name',
      ],
      'email' => [
        '#type' => 'email',
        '#title' => 'Email',
        '#required' => TRUE,
      ],
      'role' => [
        '#type' => 'textfield',
        '#title' => 'Role',
      ],
      'groups' => [
        '#type' => 'checkboxes',
        '#title' => 'Groups',
        '#options' => [
          34 => $this->randomMachineName(),
          33 => $this->randomMachineName(),
          35 => $this->randomMachineName(),
          36 => $this->randomMachineName(),
          43 => $this->randomMachineName(),
          44 => $this->randomMachineName(),
          41 => $this->randomMachineName(),
          40 => $this->randomMachineName(),
          42 => $this->randomMachineName(),
        ],
      ],
      'lists' => [
        '#type' => 'checkboxes',
        '#title' => 'Lists',
        '#options' => [
          34 => $this->randomMachineName(),
          197 => $this->randomMachineName(),
          83 => $this->randomMachineName(),
          84 => $this->randomMachineName(),
          82 => $this->randomMachineName(),
          85 => $this->randomMachineName(),
          80 => $this->randomMachineName(),
          79 => $this->randomMachineName(),
        ],
      ],
    ]);

    $webform->setSetting('results_disabled', TRUE);
    $webform->addWebformHandler(\Drupal::service('plugin.manager.webform.handler')
      ->createInstance('remote_post',  [
      'handler_id' => 'remote_post',
      'settings' => [
        'type' => 'multipart/form-data',
        'completed_url' => 'http://example.com',
      ],
    ]));

    return $webform;
  }

}
