<?php

namespace Drupal\webform_share\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Render\PageDisplayVariantSelectionEvent;
use Drupal\Core\Render\RenderEvents;
use Drupal\webform_share\WebformShareHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Disables block rendering on Webform shared via an iframe.
 */
final class DisplayVariantNegotiatorSubscriber implements EventSubscriberInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private RouteMatchInterface $routeMatch;

  /**
   * Constructs a DisplayVariantNegotiatorSubscriber object.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * Disables block rendering on shared pages.
   *
   * @param \Drupal\Core\Render\PageDisplayVariantSelectionEvent $event
   *   The event to process.
   */
  public function onSelectPageDisplayVariant(PageDisplayVariantSelectionEvent $event): void {
    if (empty(WebformShareHelper::isPage($this->routeMatch))) {
      return;
    }

    // @see \Drupal\Core\Render\Plugin\DisplayVariant\SimplePageVariant
    $event->setPluginId('simple_page');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[RenderEvents::SELECT_PAGE_DISPLAY_VARIANT][] = ['onSelectPageDisplayVariant'];
    return $events;
  }

}
