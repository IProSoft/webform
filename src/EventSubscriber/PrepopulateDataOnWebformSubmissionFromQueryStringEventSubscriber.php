<?php

declare(strict_types=1);

namespace Drupal\webform\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\webform\Event\PrepopulateDataOnWebformSubscriptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Prepopulates data on Webform submissions from query string.
 */
class PrepopulateDataOnWebformSubmissionFromQueryStringEventSubscriber implements EventSubscriberInterface {

  /**
   * Constructs a new object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(
    protected readonly RequestStack $requestStack,
    protected readonly ConfigFactoryInterface $configFactory
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [PrepopulateDataOnWebformSubscriptionEvent::class => ['prepopulate']];
  }

  /**
   * Prepopulate data on Webform submission from query string.
   *
   * @param \Drupal\webform\Event\PrepopulateDataOnWebformSubscriptionEvent $event
   *   The event.
   */
  public function prepopulate(PrepopulateDataOnWebformSubscriptionEvent $event): void {
    $should_prepopulate_all_elements = $event->getWebformSubmission()->getWebform()->getSetting('form_prepopulate') || ($this->configFactory->get('webform.settings')->get('settings.default_form_prepopulate') ?: FALSE);
    if ($should_prepopulate_all_elements) {
      $event->data += $this->requestStack->getCurrentRequest()->query->all();
    }
    else {
      $event->data += array_intersect_key($this->requestStack->getCurrentRequest()->query->all(), $event->getWebformSubmission()->getWebform()->getElementsPrepopulate());
    }
  }

}
