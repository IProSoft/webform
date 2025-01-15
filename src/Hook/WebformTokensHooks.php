<?php

namespace Drupal\webform\Hook;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\webform\Element\WebformHtmlEditor;
use Drupal\webform\Plugin\WebformElement\WebformComputedBase;
use Drupal\webform\Plugin\WebformElement\WebformMarkupBase;
use Drupal\webform\Plugin\WebformElementEntityReferenceInterface;
use Drupal\webform\Plugin\WebformElementManagerInterface;
use Drupal\webform\Utility\WebformDateHelper;
use Drupal\webform\Utility\WebformHtmlHelper;
use Drupal\webform\Utility\WebformLogicHelper;
use Drupal\webform\Utility\WebformUserHelper;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\Core\Hook\Attribute\Hook;
/**
 * Hook implementations for webform.
 */
class WebformTokensHooks
{
    /**
     * Implements hook_token_info().
     */
    #[Hook('token_info')]
    public function tokenInfo()
    {
        $types = [];
        $tokens = [];
        /* ************************************************************************ */
        // Webform submission.
        /* ************************************************************************ */
        $types['webform_submission'] = [
            'name' => t('Webform submissions'),
            'description' => t('Tokens related to webform submission.'),
            'needs-data' => 'webform_submission',
        ];
        $webform_submission = [];
        $webform_submission['serial'] = [
            'name' => t('Submission serial number'),
            'description' => t('The serial number of the webform submission.'),
        ];
        $webform_submission['sid'] = [
            'name' => t('Submission ID'),
            'description' => t('The ID of the webform submission.'),
        ];
        $webform_submission['uuid'] = ['name' => t('UUID'), 'description' => t('The UUID of the webform submission.')];
        $webform_submission['token'] = [
            'name' => t('Token'),
            'description' => t('A secure token used to look up a submission.'),
        ];
        $webform_submission['ip-address'] = [
            'name' => t('IP address'),
            'description' => t('The IP address that was used when submitting the webform submission.'),
        ];
        $webform_submission['source-type'] = ['name' => t('Source entity type'), 'description' => t('The source entity type.')];
        $webform_submission['source-id'] = ['name' => t('Source entity type'), 'description' => t('The source entity type.')];
        $webform_submission['source-title'] = [
            'name' => t('Source URL'),
            'description' => t('The Title of the source entity or webform.'),
        ];
        $webform_submission['source-url'] = [
            'name' => t('Source URL'),
            'description' => t('The URL where the user submitted the webform submission.'),
            'type' => 'url',
        ];
        $webform_submission['token-view-url'] = [
            'name' => t('View (token) URL'),
            'description' => t('The URL that can used to view the webform submission. The webform must be configured to allow users to view a submission using a secure token.'),
            'type' => 'url',
        ];
        $webform_submission['token-update-url'] = [
            'name' => t('Update (token) URL'),
            'description' => t('The URL that can used to update the webform submission. The webform must be configured to allow users to update a submission using a secure token.'),
            'type' => 'url',
        ];
        $webform_submission['token-delete-url'] = [
            'name' => t('Delete (token) URL'),
            'description' => t('The URL that can used to delete the webform submission. The webform must be configured to allow users to delete a submission using a secure token.'),
            'type' => 'url',
        ];
        $webform_submission['langcode'] = [
            'name' => t('Langcode'),
            'description' => t('The language code of the webform submission.'),
        ];
        $webform_submission['language'] = [
            'name' => t('Language'),
            'description' => t('The language name of the webform submission.'),
        ];
        $webform_submission['current-page'] = [
            'name' => t('Current page'),
            'description' => t('The current (last submitted) wizard page of the webform submission.'),
        ];
        $webform_submission['current-page:title'] = [
            'name' => t('Current page title'),
            'description' => t('The current (last submitted) wizard page title of the webform submission.'),
        ];
        $webform_submission['in-draft'] = [
            'name' => t('In draft'),
            'description' => t('Is the webform submission in draft.'),
        ];
        $webform_submission['state'] = [
            'name' => t('State (Name)'),
            'description' => t('The state of the webform submission. (unsaved, draft, completed, updated, locked, or converted)'),
        ];
        $webform_submission['state:label'] = [
            'name' => t('State (Label)'),
            'description' => t('The state raw value untranslated of the webform submission. (Unsaved, Draft, Completed, Updated, Locked, or Converted)'),
        ];
        $webform_submission['label'] = ['name' => t('Label'), 'description' => t('The label of the webform submission.')];
        // Limit: Webform.
        $webform_submission['limit:webform'] = [
            'name' => t('Total submissions limit'),
            'description' => t('The total number of submissions allowed for the webform.'),
        ];
        $webform_submission['interval:webform'] = [
            'name' => t('Total submissions limit interval'),
            'description' => t('The total submissions interval for the webform.'),
        ];
        $webform_submission['interval:webform:wait'] = [
            'name' => t('Wait time before next submission'),
            'description' => t('The amount of time before the next allowed submission for the webform.'),
        ];
        $webform_submission['total:webform'] = [
            'name' => t('Total submissions'),
            'description' => t('The current number of submissions for the webform.'),
        ];
        $webform_submission['remaining:webform'] = [
            'name' => t('Remaining number of submissions'),
            'description' => t('The remaining number of submissions for the webform.'),
        ];
        // Limit: User.
        $webform_submission['limit:user'] = [
            'name' => t('Per user submission limit'),
            'description' => t('The total number of submissions allowed per user for the webform.'),
        ];
        $webform_submission['interval:user'] = [
            'name' => t('Per user submission limit interval'),
            'description' => t('The total submissions interval per user for the webform.'),
        ];
        $webform_submission['interval:user:wait'] = [
            'name' => t('Per user wait time before next submission'),
            'description' => t('The amount of time before the next allowed submission per user for the webform.'),
        ];
        $webform_submission['total:user'] = [
            'name' => t('Per user total submissions'),
            'description' => t('The current number of submissions for the user for the webform.'),
        ];
        $webform_submission['remaining:user'] = [
            'name' => t('Per user remaining number of submissions'),
            'description' => t('The remaining number of submissions for the user for the webform.'),
        ];
        // Limit: Source entity.
        $webform_submission['limit:webform:source_entity'] = [
            'name' => t('Total submissions limit per source entity'),
            'description' => t('The total number of submissions allowed for the webform source entity.'),
        ];
        $webform_submission['interval:webform:source_entity'] = [
            'name' => t('Total submissions limit interval per source entity'),
            'description' => t('The total submissions interval for the webform source entity.'),
        ];
        $webform_submission['interval:webform:source_entity:wait'] = [
            'name' => t('Wait time before next submission for a source entity'),
            'description' => t('The amount of time before the next allowed submission for the webform source entity.'),
        ];
        $webform_submission['total:webform:source_entity'] = [
            'name' => t('Total submissions for source entity'),
            'description' => t('The current number of submissions for the webform source entity.'),
        ];
        $webform_submission['remaining:webform:source_entity'] = [
            'name' => t('Remaining number of submissions for source entity'),
            'description' => t('Remaining number of submissions for the webform source entity.'),
        ];
        // Limit: User and Source entity.
        $webform_submission['limit:user:source_entity'] = [
            'name' => t('Per user submission limit for a source entity'),
            'description' => t('The total number of submissions allowed per user for the webform source entity.'),
        ];
        $webform_submission['interval:user:source_entity'] = [
            'name' => t('Per user submission limit interval for a source entity'),
            'description' => t('The total submissions interval per user for the webform source entity.'),
        ];
        $webform_submission['interval:user:source_entity:wait'] = [
            'name' => t('Per user wait time before next submission for a source entity'),
            'description' => t('The amount of time before the next allowed submission per user for the webform source entity.'),
        ];
        $webform_submission['total:user:source_entity'] = [
            'name' => t('Per user total submissions for a source entity'),
            'description' => t('The current number of submissions for the user for the webform source entity.'),
        ];
        $webform_submission['remaining:user:source_entity'] = [
            'name' => t('Per user remaining number of submissions for a source entity'),
            'description' => t('The remaining number of submissions for the user for the webform source entity.'),
        ];
        // Dynamic tokens for webform submissions.
        $webform_submission['url'] = [
            'name' => t('URL'),
            'description' => t("The URL of the webform submission. Replace the '?' with the link template. Defaults to 'canonical' which displays the submission's data."),
            'dynamic' => TRUE,
        ];
        $webform_submission['values'] = [
            'name' => t('Submission values'),
            'description' => \Drupal\Core\Render\Markup::create(t('Webform tokens from submitted data.') . _webform_token_render_more(t('Learn about submission value tokens'), t("Omit the '?' to output all values. Output all values as HTML using [webform_submission:values:html].") . '<br />' . t("To output individual elements, replace the '?' with…") . '<br />' . '<ul>' . '<li>element_key</li>' . '<li>element_key:format</li>' . '<li>element_key:raw</li>' . '<li>element_key:format:items</li>' . '<li>element_key:delta</li>' . '<li>element_key:sub_element_key</li>' . '<li>element_key:delta:sub_element_key</li>' . '<li>element_key:sub_element_key:format</li>' . '<li>element_key:delta:sub_element_key:format</li>' . '<li>element_key:delta:format</li>' . '<li>element_key:delta:format:html</li>' . '<li>element_key:entity:*</li>' . '<li>element_key:delta:entity:*</li>' . '<li>element_key:delta:entity:field_name:*</li>' . '<li>element_key:sub_element_key:entity:*</li>' . '<li>element_key:sub_element_key:entity:field_name:*</li>' . '<li>element_key:delta:sub_element_key:entity:*</li>' . '<li>element_key:checked:option_value</li>' . '<li>element_key:selected:option_value</li>' . '</ul>' . t("All items after the 'element_key' are optional.") . '<br />' . t("The 'delta' is the numeric index for specific value") . '<br />' . t("The 'sub_element_key' is a composite element's sub element key.") . '<br />' . t("The 'checked'  or 'selected' looks to see if an 'option_value' is checked or selected for an options element (select, checkboxes, or radios)") . '<br />' . t("The 'option_value' is options value for an options element (select, checkboxes, or radios).") . '<br />' . t("The 'format' can be 'value', 'raw', or custom format specifically associated with the element") . '<br />' . t("The 'items' can be 'comma', 'semicolon', 'and', 'ol', 'ul', or custom delimiter") . '<br />' . t("The 'entity:*' applies to the referenced entity") . '<br />' . t("Add 'html' at the end of the token to return HTML markup instead of plain text.") . '<br />' . t("For example, to display the Contact webform's 'Subject' element's value you would use the [webform_submission:values:subject] token."))),
            'dynamic' => TRUE,
        ];
        // Chained tokens for webform submissions.
        $webform_submission['user'] = [
            'name' => t('Submitter'),
            'description' => t('The user that submitted the webform submission.'),
            'type' => 'user',
        ];
        $webform_submission['created'] = [
            'name' => t('Date created'),
            'description' => t('The date the webform submission was created.'),
            'type' => 'date',
        ];
        $webform_submission['completed'] = [
            'name' => t('Date completed'),
            'description' => t('The date the webform submission was completed.'),
            'type' => 'date',
        ];
        $webform_submission['changed'] = [
            'name' => t('Date changed'),
            'description' => t('The date the webform submission was most recently updated.'),
            'type' => 'date',
        ];
        $webform_submission['webform'] = [
            'name' => t('Webform', [
            ], [
                'context' => 'form',
            ]),
            'description' => t('The webform that the webform submission belongs to.'),
            'type' => 'webform',
        ];
        $webform_submission['source-entity'] = [
            'name' => t('Source entity'),
            'description' => t('The source entity that the webform submission was submitted from.'),
            'type' => 'entity',
            'dynamic' => TRUE,
        ];
        $webform_submission['source-title'] = [
            'name' => t('Source title'),
            'description' => t('The source entity title that the webform submission was submitted from, defaults to the webform title when there is no source entity.'),
            'type' => 'entity',
            'dynamic' => TRUE,
        ];
        $webform_submission['submitted-to'] = [
            'name' => t('Submitted to'),
            'description' => t('The source entity or webform that the webform submission was submitted from.'),
            'type' => 'entity',
            'dynamic' => TRUE,
        ];
        // Append link to token help to source-entity and submitted-to description.
        if (\Drupal::moduleHandler()->moduleExists('token') && \Drupal::moduleHandler()->moduleExists('help')) {
            $token_url = \Drupal\Core\Url::fromRoute('help.page', ['name' => 'token']);
            $t_args = [':href' => $token_url->toString(TRUE)->getGeneratedUrl()];
            $token_help = t('For a list of the currently available source entity related tokens, please see <a href=":href">token help</a>.', $t_args);
            $webform_submission['source-entity']['description'] = \Drupal\Core\Render\Markup::create($webform_submission['source-entity']['description'] . '<br/>' . $token_help);
            $webform_submission['submitted-to']['description'] = \Drupal\Core\Render\Markup::create($webform_submission['submitted-to']['description'] . '<br/>' . $token_help);
        }
        $tokens['webform_submission'] = $webform_submission;
        /* ************************************************************************ */
        // Webform.
        /* ************************************************************************ */
        $types['webform'] = [
            'name' => t('Webforms'),
            'description' => t('Tokens related to webforms.'),
            'needs-data' => 'webform',
        ];
        $webform = [];
        $webform['id'] = ['name' => t('Webform ID'), 'description' => t('The ID of the webform.')];
        $webform['title'] = ['name' => t('Title'), 'description' => t('The title of the webform.')];
        $webform['description'] = [
            'name' => t('Description'),
            'description' => t('The administrative description of the webform.'),
        ];
        $webform['url'] = ['name' => t('URL'), 'description' => t('The URL of the webform.')];
        $webform['author'] = ['name' => t('Author'), 'type' => 'user'];
        $webform['open'] = [
            'name' => t('Open date'),
            'description' => t('The date the webform is open to new submissions.'),
            'type' => 'date',
        ];
        $webform['close'] = [
            'name' => t('Close date'),
            'description' => t('The date the webform is closed to new submissions.'),
            'type' => 'date',
        ];
        $webform['settings'] = [
            'name' => t('Settings'),
            'description' => \Drupal\Core\Render\Markup::create(t('Webform settings tokens.') . _webform_token_render_more(t('Learn about Webform settings tokens'), '<ul>' . '<li>confirmation_title</li>' . '<li>confirmation_message</li>' . '<li>form_open_message</li>' . '<li>form_close_message</li>' . '</ul>')),
            'dynamic' => TRUE,
        ];
        $webform['element'] = [
            'name' => t('Element properties'),
            'description' => \Drupal\Core\Render\Markup::create(t('Webform element property tokens.') . _webform_token_render_more(t('Learn about element property tokens'), t("Replace the '?' with…") . '<br />' . '<ul>' . '<li>element_key:title</li>' . '<li>element_key:description</li>' . '<li>element_key:help</li>' . '<li>element_key:more</li>' . '</ul>' . t("For example, to display an email element's title (aka #title) you would use the [webform:element:email:title] token."))),
            'dynamic' => TRUE,
        ];
        $webform['handler'] = [
            'name' => t('Handler response'),
            'description' => \Drupal\Core\Render\Markup::create(t('Webform handler response tokens.') . _webform_token_render_more(t('Learn about handler response tokens'), t("Replace the '?' with…") . '<br />' . '<ul>' . '<li>handler_id:state:key</li>' . '<li>handler_id:state:key1:key2</li>' . '</ul>' . t("For example, to display a remote post's confirmation number you would use the [webform:handler:remote_post:completed:confirmation_number] token."))),
            'dynamic' => TRUE,
        ];
        $tokens['webform'] = $webform;
        /* ************************************************************************ */
        // Webform role.
        /* ************************************************************************ */
        $roles = \Drupal::config('webform.settings')->get('mail.roles');
        if ($roles) {
            $types['webform_role'] = [
                'name' => t('Webform roles'),
                'description' => t("Tokens related to user roles that can receive email. <em>This token is only available to a Webform email handler's 'To', 'CC', and 'BCC' email recipients.</em>"),
                'needs-data' => 'webform_role',
            ];
            $webform_role = [];
            $role_names = array_map('\Drupal\Component\Utility\Html::escape', \Drupal\webform\Utility\WebformUserHelper::getRoleNames(TRUE));
            if (!in_array('authenticated', $roles)) {
                $role_names = array_intersect_key($role_names, array_combine($roles, $roles));
            }
            foreach ($role_names as $role_name => $role_label) {
                $webform_role[$role_name] = [
                    'name' => $role_label,
                    'description' => t('The email addresses of all users assigned to the %title role.', [
                        '%title' => $role_label,
                    ]),
                ];
            }
            $tokens['webform_role'] = $webform_role;
        }
        /* ************************************************************************ */
        return ['types' => $types, 'tokens' => $tokens];
    }
    /**
     * Implements hook_tokens().
     */
    #[Hook('tokens')]
    public function tokens($type, $tokens, array $data, array $options, \Drupal\Core\Render\BubbleableMetadata $bubbleable_metadata)
    {
        $token_service = \Drupal::token();
        // Set URL options to generate absolute translated URLs.
        $url_options = ['absolute' => TRUE];
        if (isset($options['langcode'])) {
            $url_options['language'] = \Drupal::languageManager()->getLanguage($options['langcode']);
            $langcode = $options['langcode'];
        } else {
            $langcode = NULL;
        }
        $replacements = [];
        if ($type === 'webform_role' && !empty($data['webform_role'])) {
            $roles = $data['webform_role'];
            $any_role = in_array('authenticated', $roles) ? TRUE : FALSE;
            foreach ($tokens as $role_name => $original) {
                if ($any_role || in_array($role_name, $roles)) {
                    if ($role_name === 'authenticated') {
                        // Get all active authenticated users.
                        $query = \Drupal::database()->select('users_field_data', 'u');
                        $query->fields('u', ['mail']);
                        $query->condition('u.status', 1);
                        $query->condition('u.mail', '', '<>');
                        $query->orderBy('mail');
                        $replacements[$original] = implode(',', $query->execute()->fetchCol());
                    } else {
                        // Get all authenticated users assigned to a specified role.
                        $query = \Drupal::database()->select('user__roles', 'ur');
                        $query->distinct();
                        $query->join('users_field_data', 'u', 'u.uid = ur.entity_id');
                        $query->fields('u', ['mail']);
                        $query->condition('ur.roles_target_id', $role_name);
                        $query->condition('u.status', 1);
                        $query->condition('u.mail', '', '<>');
                        $query->orderBy('mail');
                        $replacements[$original] = implode(',', $query->execute()->fetchCol());
                    }
                }
            }
        } elseif ($type === 'webform_submission' && !empty($data['webform_submission'])) {
            /** @var \Drupal\webform\Plugin\WebformElementManagerInterface $element_manager */
            $element_manager = \Drupal::service('plugin.manager.webform.element');
            /** @var \Drupal\webform\WebformSubmissionStorageInterface $submission_storage */
            $submission_storage = \Drupal::entityTypeManager()->getStorage('webform_submission');
            // Adding webform submission, webform, source entity to bubbleable meta.
            // This reduces code duplication and easier to track.
            /** @var \Drupal\webform\WebformSubmissionInterface $webform_submission */
            $webform_submission = $data['webform_submission'];
            $bubbleable_metadata->addCacheableDependency($webform_submission);
            $webform = $webform_submission->getWebform();
            $bubbleable_metadata->addCacheableDependency($webform);
            $source_entity = $webform_submission->getSourceEntity(TRUE);
            if ($source_entity) {
                $bubbleable_metadata->addCacheableDependency($source_entity);
            }
            /** @var \Drupal\Core\Session\AccountInterface $account */
            $account = $webform_submission->getOwner() ?: \Drupal\user\Entity\User::load(0);
            $bubbleable_metadata->addCacheableDependency($account);
            foreach ($tokens as $name => $original) {
                switch ($name) {
                    case 'langcode':
                    case 'serial':
                    case 'sid':
                    case 'uuid':
                        $replacements[$original] = $webform_submission->{$name}->value;
                        break;
                    case 'ip-address':
                        $replacements[$original] = $webform_submission->remote_addr->value;
                        break;
                    case 'in-draft':
                        $replacements[$original] = $webform_submission->isDraft() ? t('Yes') : t('No');
                        break;
                    case 'state':
                        $replacements[$original] = $webform_submission->getState();
                        break;
                    case 'state:label':
                        $states = [
                            \Drupal\webform\WebformSubmissionInterface::STATE_DRAFT_CREATED => t('Draft created'),
                            \Drupal\webform\WebformSubmissionInterface::STATE_DRAFT_UPDATED => t('Draft updated'),
                            \Drupal\webform\WebformSubmissionInterface::STATE_COMPLETED => t('Completed'),
                            \Drupal\webform\WebformSubmissionInterface::STATE_CONVERTED => t('Converted'),
                            \Drupal\webform\WebformSubmissionInterface::STATE_UPDATED => t('Updated'),
                            \Drupal\webform\WebformSubmissionInterface::STATE_UNSAVED => t('Unsaved'),
                            \Drupal\webform\WebformSubmissionInterface::STATE_LOCKED => t('Locked'),
                        ];
                        $replacements[$original] = $states[$webform_submission->getState()];
                        break;
                    case 'current-page':
                    case 'current-page:title':
                        $current_page = $webform_submission->current_page->value;
                        $pages = $webform->getPages();
                        if (empty($current_page)) {
                            $page_keys = array_keys($pages);
                            $current_page = reset($page_keys);
                        }
                        $replacements[$original] = $name === 'current-page:title' && isset($pages[$current_page]) ? $pages[$current_page]['#title'] : $current_page;
                        break;
                    case 'language':
                        $replacements[$original] = \Drupal::languageManager()->getLanguage($webform_submission->langcode->value)->getName();
                        break;
                    case 'source-type':
                        $replacements[$original] = $source_entity ? $source_entity->getEntityTypeId() : '';
                        break;
                    case 'source-id':
                        $replacements[$original] = $source_entity ? $source_entity->id() : '';
                        break;
                    case 'source-title':
                        $replacements[$original] = $source_entity ? $source_entity->label() : $webform->label();
                        break;
                    case 'source-url':
                        $replacements[$original] = $webform_submission->getSourceUrl()->toString();
                        break;
                    case 'token-view-url':
                    case 'token-update-url':
                    case 'token-delete-url':
                        $replacements[$original] = $webform_submission->getTokenUrl(preg_replace('/^token-(view|update|delete)-url$/', '\1', $name))->toString();
                        break;
                    case 'token':
                        $replacements[$original] = $webform_submission->getToken();
                        break;
                    case 'label':
                        $replacements[$original] = $webform_submission->label();
                        break;
                    /* Default values for the dynamic tokens handled below. */
                    case 'url':
                        if ($webform_submission->id()) {
                            $replacements[$original] = $webform_submission->toUrl('canonical', $url_options)->toString();
                        }
                        break;
                    case 'values':
                        $replacements[$original] = _webform_token_get_submission_values($options, $webform_submission);
                        break;
                    /* Default values for the chained tokens handled below */
                    case 'user':
                        $replacements[$original] = $account->label();
                        break;
                    case 'created':
                    case 'completed':
                    case 'changed':
                        $bubbleable_metadata->addCacheableDependency(\Drupal\Core\Datetime\Entity\DateFormat::load('medium'));
                        $replacements[$original] = \Drupal\webform\Utility\WebformDateHelper::format($webform_submission->{$name}->value, 'medium', '', NULL, $langcode);
                        break;
                    case 'webform':
                        $replacements[$original] = $webform->label();
                        break;
                    case 'source-entity':
                        if ($source_entity) {
                            $replacements[$original] = $source_entity->label();
                        } else {
                            $replacements[$original] = '';
                        }
                        break;
                    case 'submitted-to':
                        $submitted_to = $source_entity ?: $webform;
                        $replacements[$original] = $submitted_to->label();
                        break;
                    case 'limit:webform':
                        $replacements[$original] = $webform->getSetting('limit_total') ?: t('None');
                        break;
                    case 'interval:webform':
                        $replacements[$original] = \Drupal\webform\Utility\WebformDateHelper::getIntervalText($webform->getSetting('limit_total_interval'));
                        break;
                    case 'interval:webform:wait':
                        $replacements[$original] = _webform_token_get_interval_wait('limit_total_interval', $bubbleable_metadata, $webform);
                        break;
                    case 'total:webform':
                        $replacements[$original] = $submission_storage->getTotal($webform);
                        break;
                    case 'remaining:webform':
                        $limit = $webform->getSetting('limit_total');
                        $total = $submission_storage->getTotal($webform);
                        if ($limit && $total !== NULL) {
                            $replacements[$original] = $limit > $total ? $limit - $total : 0;
                        }
                        break;
                    case 'limit:user':
                        $replacements[$original] = $webform->getSetting('limit_user') ?: t('None');
                        break;
                    case 'interval:user':
                        $replacements[$original] = \Drupal\webform\Utility\WebformDateHelper::getIntervalText($webform->getSetting('limit_user_interval'));
                        break;
                    case 'interval:user:wait':
                        $replacements[$original] = _webform_token_get_interval_wait('limit_user_interval', $bubbleable_metadata, $webform, NULL, $account);
                        break;
                    case 'total:user':
                        $replacements[$original] = $submission_storage->getTotal($webform, NULL, $account);
                        break;
                    case 'remaining:user':
                        $limit = $webform->getSetting('limit_user');
                        $total = $submission_storage->getTotal($webform, NULL, $account);
                        if ($limit && $total !== NULL) {
                            $replacements[$original] = $limit > $total ? $limit - $total : 0;
                        }
                        break;
                    case 'limit:webform:source_entity':
                        $replacements[$original] = $webform->getSetting('entity_limit_total') ?: t('None');
                        break;
                    case 'interval:webform:source_entity':
                        $replacements[$original] = \Drupal\webform\Utility\WebformDateHelper::getIntervalText($webform->getSetting('entity_limit_total_interval'));
                        break;
                    case 'interval:webform:source_entity:wait':
                        $replacements[$original] = $source_entity ? _webform_token_get_interval_wait('entity_limit_total_interval', $bubbleable_metadata, $webform, $source_entity) : '';
                        break;
                    case 'total:webform:source_entity':
                        $replacements[$original] = $source_entity ? $submission_storage->getTotal($webform, $source_entity) : '';
                        break;
                    case 'remaining:webform:source_entity':
                        $limit = $webform->getSetting('entity_limit_total');
                        $total = $source_entity ? $submission_storage->getTotal($webform, $source_entity) : NULL;
                        if ($limit && $total !== NULL) {
                            $replacements[$original] = $limit > $total ? $limit - $total : 0;
                        }
                        break;
                    case 'limit:user:source_entity':
                        $replacements[$original] = $webform->getSetting('entity_limit_user') ?: t('None');
                        break;
                    case 'interval:user:source_entity':
                        $replacements[$original] = \Drupal\webform\Utility\WebformDateHelper::getIntervalText($webform->getSetting('entity_limit_user_interval'));
                        break;
                    case 'interval:user:source_entity:wait':
                        $replacements[$original] = $source_entity ? _webform_token_get_interval_wait('entity_limit_user_interval', $bubbleable_metadata, $webform, $source_entity, $account) : '';
                        break;
                    case 'total:user:source_entity':
                        $replacements[$original] = $source_entity ? $submission_storage->getTotal($webform, $source_entity, $account) : '';
                        break;
                    case 'remaining:user:source_entity':
                        $limit = $webform->getSetting('entity_limit_user');
                        $total = $source_entity ? $submission_storage->getTotal($webform, $source_entity, $account) : NULL;
                        if ($limit && $total !== NULL) {
                            $replacements[$original] = $limit > $total ? $limit - $total : 0;
                        }
                        break;
                }
            }
            /* Dynamic tokens. */
            if (($url_tokens = $token_service->findWithPrefix($tokens, 'url')) && $webform_submission->id()) {
                foreach ($url_tokens as $key => $original) {
                    if ($webform_submission->hasLinkTemplate($key)) {
                        $replacements[$original] = $webform_submission->toUrl($key, $url_options)->toString();
                    }
                }
            }
            if ($value_tokens = $token_service->findWithPrefix($tokens, 'values')) {
                foreach ($value_tokens as $value_token => $original) {
                    $value = _webform_token_get_submission_value($value_token, $options, $webform_submission, $element_manager, $bubbleable_metadata);
                    if ($value !== NULL) {
                        $replacements[$original] = $value;
                    }
                }
            }
            /* Chained token relationships. */
            if (($user_tokens = $token_service->findWithPrefix($tokens, 'user')) && ($user = $webform_submission->getOwner())) {
                $replacements += $token_service->generate('user', $user_tokens, ['user' => $user], $options, $bubbleable_metadata);
            }
            if (($created_tokens = $token_service->findWithPrefix($tokens, 'created')) && ($created_time = $webform_submission->getCreatedTime())) {
                $replacements += $token_service->generate('date', $created_tokens, ['date' => $created_time], $options, $bubbleable_metadata);
            }
            if (($changed_tokens = $token_service->findWithPrefix($tokens, 'changed')) && ($changed_time = $webform_submission->getChangedTime())) {
                $replacements += $token_service->generate('date', $changed_tokens, ['date' => $changed_time], $options, $bubbleable_metadata);
            }
            if (($completed_tokens = $token_service->findWithPrefix($tokens, 'completed')) && ($completed_time = $webform_submission->getCompletedTime())) {
                $replacements += $token_service->generate('date', $completed_tokens, ['date' => $completed_time], $options, $bubbleable_metadata);
            }
            if (($webform_tokens = $token_service->findWithPrefix($tokens, 'webform')) && ($webform = $webform_submission->getWebform())) {
                $replacements += $token_service->generate('webform', $webform_tokens, ['webform' => $webform], $options, $bubbleable_metadata);
            }
            if (($source_entity_tokens = $token_service->findWithPrefix($tokens, 'source-entity')) && ($source_entity = $webform_submission->getSourceEntity(TRUE))) {
                $type = $source_entity->getEntityType()->get('token_type') ?: $source_entity->getEntityTypeId();
                $replacements += $token_service->generate($type, $source_entity_tokens, [$type => $source_entity], $options, $bubbleable_metadata);
            }
            if (($submitted_to_tokens = $token_service->findWithPrefix($tokens, 'submitted-to')) && ($submitted_to = $webform_submission->getSourceEntity(TRUE) ?: $webform_submission->getWebform())) {
                $type = $submitted_to->getEntityType()->get('token_type') ?: $submitted_to->getEntityTypeId();
                $replacements += $token_service->generate($type, $submitted_to_tokens, [$type => $submitted_to], $options, $bubbleable_metadata);
            }
            foreach (['token-view-url', 'token-update-url', 'token-delete-url', 'source-url'] as $token) {
                if ($url_tokens = $token_service->findWithPrefix($tokens, $token)) {
                    $url = NULL;
                    switch ($token) {
                        case 'token-view-url':
                        case 'token-update-url':
                        case 'token-delete-url':
                            $url = $webform_submission->getTokenUrl(preg_replace('/^token-(view|update|delete)-url$/', '\1', $token));
                            break;
                        case 'source-url':
                            $url = $webform_submission->getSourceUrl();
                            break;
                    }
                    if ($url) {
                        $replacements += $token_service->generate('url', $url_tokens, ['url' => $url], $options, $bubbleable_metadata);
                    }
                }
            }
        } elseif ($type === 'webform' && !empty($data['webform'])) {
            /** @var \Drupal\webform\WebformInterface $webform */
            $webform = $data['webform'];
            foreach ($tokens as $name => $original) {
                switch ($name) {
                    case 'id':
                        $replacements[$original] = $webform->id();
                        break;
                    case 'title':
                        $replacements[$original] = $webform->label();
                        break;
                    case 'description':
                        $replacements[$original] = $webform->getDescription();
                        break;
                    case 'open':
                    case 'close':
                        $datetime = $webform->get($name);
                        $replacements[$original] = $datetime ? \Drupal\webform\Utility\WebformDateHelper::format(strtotime($datetime), 'medium', '', NULL, $langcode) : '';
                        break;
                    /* Default values for the dynamic tokens handled below. */
                    case 'url':
                        $replacements[$original] = $webform->toUrl('canonical', $url_options)->toString();
                        break;
                    /* Default values for the chained tokens handled below. */
                    case 'author':
                        $account = $webform->getOwner() ?: \Drupal\user\Entity\User::load(0);
                        $bubbleable_metadata->addCacheableDependency($account);
                        $replacements[$original] = $account->label();
                        break;
                }
            }
            /* Dynamic tokens. */
            if ($settings_tokens = $token_service->findWithPrefix($tokens, 'settings')) {
                /** @var \Drupal\webform\WebformTokenManagerInterface $token_manager */
                $token_manager = \Drupal::service('webform.token_manager');
                foreach ($settings_tokens as $key => $original) {
                    $token_value = $webform->getSetting($key, '');
                    $replacements[$original] = !is_array($token_value) ? $token_manager->replace($token_value, NULL, $data, $options, $bubbleable_metadata) : '';
                }
            }
            if ($element_tokens = $token_service->findWithPrefix($tokens, 'element')) {
                foreach ($element_tokens as $key => $original) {
                    if (strpos($key, ':') === FALSE) {
                        $element_key = $key;
                        $element_property = 'title';
                    } else {
                        [$element_key, $element_property] = explode(':', $key);
                    }
                    $element_property = $element_property ?: 'title';
                    $element = $webform->getElement($element_key);
                    if ($element && isset($element["#{$element_property}"]) && is_string($element["#{$element_property}"])) {
                        $token_value = $element["#{$element_property}"];
                        if (in_array($element_property, ['description', 'help', 'more', 'terms_content'])) {
                            $token_value = \Drupal\webform\Element\WebformHtmlEditor::checkMarkup($token_value);
                            $token_value = \Drupal::service('renderer')->renderInIsolation($token_value);
                        } else {
                            $token_value = \Drupal\webform\Utility\WebformHtmlHelper::toHtmlMarkup($token_value);
                        }
                        $replacements[$original] = $token_value;
                    }
                }
            }
            if ($handler_tokens = $token_service->findWithPrefix($tokens, 'handler')) {
                foreach ($handler_tokens as $key => $original) {
                    $webform_handler = $data['webform_handler'] ?? [];
                    $parents = explode(':', $key);
                    $key_exists = NULL;
                    $value = \Drupal\Component\Utility\NestedArray::getValue($webform_handler, $parents, $key_exists);
                    // A handler response is always considered safe markup.
                    $replacements[$original] = $key_exists && is_scalar($value) ? \Drupal\Core\Render\Markup::create($value) : $original;
                }
            }
            if ($url_tokens = $token_service->findWithPrefix($tokens, 'url')) {
                foreach ($url_tokens as $key => $original) {
                    if ($webform->hasLinkTemplate($key)) {
                        $replacements[$original] = $webform->toUrl($key, $url_options)->toString();
                    }
                }
            }
            /* Chained token relationships. */
            if ($author_tokens = $token_service->findWithPrefix($tokens, 'author')) {
                $replacements += $token_service->generate('user', $author_tokens, ['user' => $webform->getOwner()], $options, $bubbleable_metadata);
            }
            if (($open_tokens = $token_service->findWithPrefix($tokens, 'open')) && ($open_time = $webform->get('open'))) {
                $replacements += $token_service->generate('date', $open_tokens, ['date' => strtotime($open_time)], $options, $bubbleable_metadata);
            }
            if (($close_tokens = $token_service->findWithPrefix($tokens, 'close')) && ($close_time = $webform->get('close'))) {
                $replacements += $token_service->generate('date', $close_tokens, ['date' => strtotime($close_time)], $options, $bubbleable_metadata);
            }
        }
        return $replacements;
    }
}
