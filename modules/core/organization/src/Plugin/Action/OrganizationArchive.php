<?php

declare(strict_types=1);

namespace Drupal\organization\Plugin\Action;

/**
 * Action that archives an organization.
 *
 * @Action(
 *   id = "organization_archive_action",
 *   label = @Translation("Archive an organization"),
 *   type = "organization"
 * )
 */
class OrganizationArchive extends OrganizationStateChangeBase {

  /**
   * {@inheritdoc}
   */
  protected $targetState = 'archived';

}
