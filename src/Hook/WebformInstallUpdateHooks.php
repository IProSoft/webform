<?php

namespace Drupal\webform\Hook;

use Drupal\Component\Uuid\Php as Uuid;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Database;
use Drupal\Core\Database\SchemaObjectExistsException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\views\Entity\View;
use Drupal\webform\Element\WebformSignature as WebformSignatureElement;
use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformOptions;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Plugin\WebformHandler\EmailWebformHandler;
use Drupal\webform\Plugin\WebformHandler\RemotePostWebformHandler;
use Drupal\webform\Utility\WebformArrayHelper;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\webform\Utility\WebformOptionsHelper;
use Drupal\webform\Utility\WebformReflectionHelper;
use Drupal\webform\Utility\WebformYaml;
use Drupal\webform\WebformInterface;
use Drupal\Component\Uuid\Php as Uuid;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform.
 */
class WebformInstallUpdateHooks
{
    /**
     * Implements hook_update_dependencies().
     */
    #[Hook('update_dependencies')]
    public function updateDependencies()
    {
        // Ensure that system_update_8501() runs before the webform update, so that
        // the new revision_default field is installed in the correct table.
        // @see https://www.drupal.org/project/webform/issues/2958102
        $dependencies['webform'][8099]['system'] = 8501;
        // Ensure that system_update_8805() runs before the webform update, so that
        // the 'path_alias' module is enabled and configured correctly.
        // @see https://www.drupal.org/project/webform/issues/3166248
        $dependencies['webform']['8158']['system'] = 8805;
        return $dependencies;
    }
}
