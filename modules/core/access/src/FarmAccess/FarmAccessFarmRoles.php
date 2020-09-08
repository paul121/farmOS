<?php

namespace Drupal\farm_access\FarmAccess;

use Drupal\farm_access\FarmRolesInterface;

/**
 * FarmAccess configuration of managed Farm roles.
 */
class FarmAccessFarmRoles implements FarmRolesInterface {

  /**
   * {@inheritdoc}
   */
  public function getManagedRoles() {
    // Specify managed farm roles.
    return ['farm_manager', 'farm_worker', 'farm_viewer'];
  }

  /**
   * {@inheritdoc}
   */
  public function getRolePermissions($role) {
    // Add view permissions to any role.
    $perms = ['access_content', 'view any observation log'];

    // Add edit permissions to higher access roles.
    if (in_array($role, ['farm_manager', 'farm_worker'])) {
      $perms[] = 'update any observation log';
    }

    return $perms;
  }

}
