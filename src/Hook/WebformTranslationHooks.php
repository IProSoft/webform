<?php

namespace Drupal\webform\Hook;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\webform\Utility\WebformYaml;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform.
 */
class WebformTranslationHooks
{
    /* ************************************************************************** */
    // Lingotek integration.
    /* ************************************************************************** */
    /**
     * Implements hook_lingotek_config_entity_document_upload().
     */
    #[Hook('lingotek_config_entity_document_upload')]
    public function lingotekConfigEntityDocumentUpload(array &$source_data, \Drupal\Core\Config\Entity\ConfigEntityInterface &$entity, &$url)
    {
        /** @var \Drupal\webform\WebformTranslationLingotekManagerInterface $translation_lingotek_manager */
        $translation_lingotek_manager = \Drupal::service('webform.translation_lingotek_manager');
        $translation_lingotek_manager->configEntityDocumentUpload($source_data, $entity, $url);
    }
    /**
     * Implements hook_lingotek_config_entity_translation_presave().
     */
    #[Hook('lingotek_config_entity_translation_presave')]
    public function lingotekConfigEntityTranslationPresave(\Drupal\Core\Config\Entity\ConfigEntityInterface &$translation, $langcode, &$data)
    {
        /** @var \Drupal\webform\WebformTranslationLingotekManagerInterface $translation_lingotek_manager */
        $translation_lingotek_manager = \Drupal::service('webform.translation_lingotek_manager');
        $translation_lingotek_manager->configEntityTranslationPresave($translation, $langcode, $data);
    }
    /**
     * Implements hook_lingotek_config_object_document_upload().
     */
    #[Hook('lingotek_config_object_document_upload')]
    public function lingotekConfigObjectDocumentUpload(array &$data, $config_name)
    {
        /** @var \Drupal\webform\WebformTranslationLingotekManagerInterface $translation_lingotek_manager */
        $translation_lingotek_manager = \Drupal::service('webform.translation_lingotek_manager');
        $translation_lingotek_manager->configObjectDocumentUpload($data, $config_name);
    }
    /**
     * Implements hook_lingotek_config_object_translation_presave().
     */
    #[Hook('lingotek_config_object_translation_presave')]
    public function lingotekConfigObjectTranslationPresave(array &$data, $config_name)
    {
        /** @var \Drupal\webform\WebformTranslationLingotekManagerInterface $translation_lingotek_manager */
        $translation_lingotek_manager = \Drupal::service('webform.translation_lingotek_manager');
        $translation_lingotek_manager->configObjectTranslationPresave($data, $config_name);
    }
}
