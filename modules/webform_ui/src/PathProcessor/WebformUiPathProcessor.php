<?php

namespace Drupal\webform_ui\PathProcessor;

use Drupal\Core\PathProcessor\OutboundPathProcessorInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Symfony\Component\HttpFoundation\Request;

/**
 * Path processor for webform UI.
 */
class WebformUiPathProcessor implements OutboundPathProcessorInterface {

  /**
   * {@inheritdoc}
   */
  public function processOutbound($path, &$options = [], Request $request = NULL, BubbleableMetadata $bubbleable_metadata = NULL) {
    if (is_null($request) || (strpos($path, '/webform/') === FALSE)) {
      return $path;
    }

    if (!($querystring = $request->getQueryString())) {
      return;
    }

    if (strpos($querystring, '_wrapper_format=') === FALSE) {
      return $path;
    }

    $querybag = [];
    parse_str($querystring, $querybag);
    if (empty($querybag['destination'])) {
      return $path;
    }

    $destination = $querybag['destination'];
    $options['query']['destination'] = $destination;
    return $path;
  }

}
