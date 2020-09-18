<?php

namespace Drupal\farm_access\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining ManagedRolePermissions config entities.
 *
 * @ingroup farm
 */
interface ManagedRolePermissionsInterface extends ConfigEntityInterface {

  /**
   * Returns the default permissions.
   *
   * @return array
   *   Array of permission strings.
   */
  public function getDefaultPermissions();

  /**
   * Returns the config permissions.
   *
   * @return array
   *   Array of permission strings.
   */
  public function getConfigPermissions();

}
