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

  /**
   * Returns permission callback strings.
   *
   * @return array
   *   Array of function callbacks in controller syntax, see
   *   \Drupal\Core\Controller\ControllerResolver
   */
  public function getPermissionCallbacks();

}
