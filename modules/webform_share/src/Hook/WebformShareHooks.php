<?php

namespace Drupal\webform_share\Hook;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\Markup;
use Drupal\webform\WebformInterface;
use Drupal\webform_share\WebformShareHelper;
use Drupal\webform_share\WebformSharePreRender;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform_share.
 */
class WebformShareHooks
{
    /**
     * Implements hook_webform_help_info().
     */
    #[Hook('webform_help_info')]
    public function webformHelpInfo()
    {
        $help = [];
        $help['webform_share_embed'] = [
            'group' => 'share',
            'title' => t('Share embed'),
            'content' => t("The <strong>Share embed</strong> page provides code snippets that are used to embedded a webform in any website, webpage, and application."),
            'routes' => [
                // @see /admin/structure/webform/manage/{webform}/share
                'entity.webform.share_embed',
                // @see /node/{node}/webform/share
                'entity.node.webform.share_embed',
            ],
        ];
        $help['webform_share_preview'] = [
            'group' => 'share',
            'title' => t('Share preview'),
            'content' => t("The <strong>Share preview</strong> page allows site builders to preview an embedded webform."),
            'routes' => [
                // @see /admin/structure/webform/manage/{webform}/share/preview
                'entity.webform.share_preview',
                // @see /node/{node}/webform/share/preview
                'entity.node.webform.share_preview',
            ],
        ];
        $help['webform_share_test'] = [
            'group' => 'share',
            'title' => t('Share test'),
            'content' => t("The <strong>Share test</strong> page allows site builders to test an embedded webform."),
            'routes' => [
                // @see /admin/structure/webform/manage/{webform}/share/test
                'entity.webform.share_test',
                // @see /node/{node}/webform/share/test
                'entity.node.webform.share_test',
            ],
        ];
        return $help;
    }
    /**
     * Implements hook_local_tasks_alter().
     */
    #[Hook('local_tasks_alter')]
    public function localTasksAlter(&$local_tasks)
    {
        // Remove webform share if the webform_node.module
        // is not installed.
        if (!\Drupal::moduleHandler()->moduleExists('webform_node')) {
            unset($local_tasks['entity.node.webform.share'], $local_tasks['entity.node.webform.share_embed'], $local_tasks['entity.node.webform.share_preview'], $local_tasks['entity.node.webform.share_test']);
        }
    }
    /**
     * Implements hook_menu_local_tasks_alter().
     */
    #[Hook('menu_local_tasks_alter')]
    public function menuLocalTasksAlter(&$data, $route_name, RefinableCacheableDependencyInterface $cacheability)
    {
        // Allow webform query string parameters to be transferred
        // from canonical to test URL.
        $route_names = [
            'entity.webform.share_embed',
            'entity.webform.share_preview',
            'entity.webform.share_test',
        ];
        if (in_array($route_name, $route_names)) {
            if ($query = \Drupal::request()->query->all()) {
                foreach ($route_names as $route_name) {
                    if (isset($data['tabs'][1][$route_name])) {
                        $url =& $data['tabs'][1][$route_name]['#link']['url'];
                        $url->setOption('query', $query);
                    }
                }
            }
            // Query string to cache context webform canonical and test routes.
            $cacheability->addCacheContexts(['url.query_args']);
        }
    }
    /**
     * Implements hook_element_info_alter().
     */
    #[Hook('element_info_alter')]
    public function elementInfoAlter(&$type)
    {
        $type['page']['#pre_render'][] = [\Drupal\webform_share\WebformSharePreRender::class, 'page'];
    }
    /**
     * Implements hook_entity_type_alter().
     */
    #[Hook('entity_type_alter')]
    public function entityTypeAlter(array &$entity_types)
    {
        if (isset($entity_types['webform'])) {
            /** @var \Drupal\Core\Entity\ContentEntityTypeInterface $webform_entity_type */
            $webform_entity_type = $entity_types['webform'];
            // Add 'share-embed',  'share-preview', and 'share-test' to link templates.
            $webform_entity_type->setLinkTemplate('share-embed', '/admin/structure/webform/manage/{webform}/share/embed');
            $webform_entity_type->setLinkTemplate('share-preview', '/admin/structure/webform/manage/{webform}/share/preview');
            $webform_entity_type->setLinkTemplate('share-test', '/admin/structure/webform/manage/{webform}/share/test');
        }
    }
    /**
     * Implements hook_entity_operation().
     */
    #[Hook('entity_operation')]
    public function entityOperation(EntityInterface $entity)
    {
        $operations = [];
        if ($entity instanceof \Drupal\webform\WebformInterface && $entity->access('update') && $entity->getSetting('share', TRUE)) {
            $operations['share'] = ['title' => t('Share'), 'url' => $entity->toUrl('share-embed'), 'weight' => 80];
        }
        return $operations;
    }
    /**
     * Implements hook_page_top().
     */
    #[Hook('page_top')]
    public function pageTop(array &$page_top)
    {
        if (!\Drupal\webform_share\WebformShareHelper::isPage()) {
            return;
        }
        // Remove the toolbar from the webform share page.
        unset($page_top['toolbar']);
    }
    /* ************************************************************************** */
    // Theme functions.
    /* ************************************************************************** */
    /**
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme($existing, $type, $theme, $path)
    {
        return [
            // Using dedicated html and page template ensures the shared webforms
            // output is as simple as possible.
            'html__webform_share' => [
                'render element' => 'html',
            ],
            'page__webform_share' => [
                'render element' => 'page',
            ],
            'webform_share_iframe' => [
                'render element' => 'element',
            ],
            'webform_share_script' => [
                'render element' => 'element',
            ],
        ];
    }
}
