<?php

namespace Drupal\farm_access;

/**
 * Interface for defining managed farm role services.
 */
interface FarmRolesInterface {

  /**
   * Return a list of roles that should be managed by the farm access module.
   *
   * @return array
   *  Array of role IDs.
   */
  public function getManagedRoles();

  /**
   * Add permissions to managed farm roles.
   *
   * @param $role
   *  The role to alter.
   *
   * @return array
   *   Array of permissions.
   */
  public function getRolePermissions($role);

}
