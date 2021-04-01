<?php

namespace Drupal\farm_group;

use Drupal\asset\Entity\AssetInterface;
use Drupal\log\Entity\LogInterface;

/**
 * Asset group membership logic.
 */
interface GroupMembershipInterface {

  /**
   * Check if an asset is a member of a group.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The Asset entity.
   *
   * @return bool
   *   Returns TRUE if the asset is a member of a group, FALSE otherwise.
   */
  public function hasGroup(AssetInterface $asset): bool;

  /**
   * Get group assets that an asset is a member of.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The Asset entity.
   *
   * @return array
   *   Returns an array of assets.
   */
  public function getGroup(AssetInterface $asset): array;

  /**
   * Find the latest group assignment log that references an asset.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   *
   * @return \Drupal\log\Entity\LogInterface|null
   *   A log entity, or NULL if no logs were found.
   */
  public function getGroupAssignmentLog(AssetInterface $asset): ?LogInterface;

  /**
   * Get group assignment logs that reference an asset.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   * @param array $options
   *   An array of options for building the query.
   *
   * @return \Drupal\log\Entity\LogInterface[]
   *   An array of log entities.
   */
  public function getGroupAssignmentLogs(AssetInterface $asset, array $options = []): array;

  /**
   * Get the group membership history of an asset.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   *
   * @return array
   *   Returns an array of arrays of group membership history keyed by the
   *   group ID. Each group's array is an array of intervals the asset was a
   *   member of a group. Each interval has 'arrive' and 'depart' keys
   *   specifying the arrival and departure logs. If the last interval's
   *   'depart' is NULL, then the interval is "active" and the asset is
   *   currently a member of that group.
   */
  public function getGroupHistory(AssetInterface $asset): array;

  /**
   * Get logs referencing the groups an asset was a member of.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset entity.
   * @param array $options
   *   An array of options for building the query.
   *
   * @return \Drupal\log\Entity\LogInterface[]
   *   An array of log entities.
   */
  public function getGroupHistoryLogs(AssetInterface $asset, array $options = []): array;

}
