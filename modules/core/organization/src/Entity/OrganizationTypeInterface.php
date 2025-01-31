<?php

declare(strict_types=1);

namespace Drupal\organization\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\Core\Entity\RevisionableEntityBundleInterface;

/**
 * Provides an interface for defining organization type entities.
 */
interface OrganizationTypeInterface extends ConfigEntityInterface, EntityDescriptionInterface, RevisionableEntityBundleInterface {

  /**
   * Gets the organization type's workflow ID.
   *
   * Used by the $organization->status field.
   *
   * @return string
   *   The organization type workflow ID.
   */
  public function getWorkflowId();

  /**
   * Sets the workflow ID of the organization type.
   *
   * @param string $workflow_id
   *   The workflow ID.
   *
   * @return $this
   */
  public function setWorkflowId($workflow_id);

}
