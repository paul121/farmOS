<?php

declare(strict_types=1);

namespace Drupal\farm_ui_views\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Checks access for displaying Views of logs that reference organizations.
 */
class FarmOrganizationLogViewsAccessCheck implements AccessInterface {

  /**
   * The log storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $logStorage;

  /**
   * The organization storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $organizationStorage;

  /**
   * FarmOrganizationAssetViewsAccessCheck constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->logStorage = $entity_type_manager->getStorage('log');
    $this->organizationStorage = $entity_type_manager->getStorage('organization');
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function access(RouteMatchInterface $route_match) {

    // Get the "organization" parameter and attempt to load the organization.
    // If the organization cannot be loaded, allow access so that views
    // contextual filter validation returns a 404.
    $organization_id = $route_match->getParameter('organization');
    /** @var \Drupal\organization\Entity\OrganizationInterface|null $organization */
    $organization = $this->organizationStorage->load($organization_id);
    if (is_null($organization)) {
      return AccessResult::allowed();
    }

    // Get the "log_type" parameter. If it is empty or "all", allow access so
    // that Views can handle it.
    $log_type = $route_match->getParameter('log_type');
    if (empty($log_type) || $log_type == 'all') {
      return AccessResult::allowed();
    }

    // Build a count query for logs of this type.
    $query = $this->logStorage->getAggregateQuery()
      ->accessCheck(TRUE)
      ->condition('type', $log_type)
      ->count();

    // Only include logs that reference the organization.
    // Use two separate condition groups to avoid issue where only the
    // first condition is checked.
    $asset_group = $query->andConditionGroup()
      ->condition('asset.entity.farm.entity.id', $organization_id);
    $location_group = $query->andConditionGroup()
      ->condition('location.entity.farm.entity.id', $organization_id);
    $group = $query->orConditionGroup()
      ->condition($asset_group)
      ->condition($location_group);
    $query->condition($group);

    // Determine access based on the log count.
    $count = $query->execute();
    $access = AccessResult::allowedIf($count > 0);

    // Invalidate the access result when logs or assets are changed.
    $access->addCacheTags(["asset"]);
    $access->addCacheTags(["log_list:$log_type"]);
    return $access;
  }

}
