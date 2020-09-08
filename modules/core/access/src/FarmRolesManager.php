<?php

namespace Drupal\farm_access;

/**
 *
 */
class FarmRolesManager {


  /**
   * Array of Farm Role providers.
   *
   * @var array
   */
  protected $providers = [];

  /**
   * List of managed farm role IDs.
   *
   * @var array
   */
  protected $roles = [];


  /**
   * The method called when loading FarmRoleProvider services.
   *
   * @param \Drupal\farm_access\FarmRolesInterface $provider
   *  An instance of the provider.
   * @param int $priority
   *  Priority of the provider.
   */
  public function addRole(FarmRolesInterface $provider, $priority = 0) {

    // Save an instance of the provider.
    $this->providers[] = $provider;

    // Save role IDs provided by the provider.
    $roles = $provider->getManagedRoles();
    $this->roles = array_merge($this->roles, $roles);
  }

  /**
   * Get the managed farm roles.
   *
   * @return array
   *  Array of managed farm role IDs.
   */
  public function getFarmRoles() {
    return $this->roles;
  }

  /**
   * Add permissions to a role.
   *
   * @param $role_id
   *  Managed role ID.
   * @return array
   *   Permissions to add to the role.
   */
  public function getRolePermissions($role_id) {
    $permissions = [];

    foreach ($this->providers as $provider) {
      $permissions = array_merge($permissions, $provider->getRolePermissions($role_id));
    }

    return $permissions;
  }
}
