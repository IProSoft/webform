<?php

namespace Drupal\webform_group;

use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformInterface;

/**
 * Defines an interface for the webform group manager.
 */
interface WebformGroupManagerInterface {

  /**
   * Determine if the group owner token is enabled.
   *
   * @return boolean
   *   TRUE if the group owner token is enabled.
   */
  public function isGroupOwnerTokenEnable();

  /**
   * Determine if the group role token is enabled.
   *
   * @return boolean
   *   TRUE if the group role token is enabled.
   */
  public function isGroupRoleTokenEnabled($group_role_id);

  /**
   * Get the current user's group roles.
   *
   * @return array|bool
   *   An array containing the current user's group roles.
   *   FALSE if no group content is found for the current request.
   */
  public function getCurrentUserGroupRoles();

  /**
   * Get the group content for the current request.
   *
   * @return \Drupal\group\Entity\GroupContentInterface|bool
   *   The group content for the current request.
   *   FALSE if no group content is found for the current request.
   */
  public function getCurrentGroupContent();

  /**
   * Get the group webform for the current request.
   *
   * @return \Drupal\webform\WebformInterface|null
   *   Rhe group webform for the current request.
   */
  public function getCurrentGroupWebform();

  /**
   * Get group content for a webform submission.
   *
   * @return \Drupal\group\Entity\GroupContentInterface|bool
   *   The group content for the webform submission.
   *   FALSE if no group content is found for the current request.
   */
  public function getGroupContent(WebformSubmissionInterface $webform_submission);

  /**
   * Get a webform's access rules with group roles.
   *
   * @param \Drupal\webform\WebformInterface $webform
   *   A webform.
   *
   * @return array
   *   An associative array containiong a webform's access rules
   *   with group roles.
   */
  public function getAccessRules(WebformInterface $webform);

}
