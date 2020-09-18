<?php

namespace Drupal\farm_access;

use Drupal\user\RoleInterface;
use Drupal\user\RoleStorage;

/**
 * FarmRoleStorage.
 *
 * Extend the RoleStorage class to include permissions defined with managed
 * farm roles.
 *
 * @ingroup farm
 */
class FarmRoleStorage extends RoleStorage {

  /**
   * {@inheritdoc}
   */
  public function isPermissionInRoles($permission, array $rids) {

    // Check if the permission is defined directly on the role.
    $has_permission = parent::isPermissionInRoles($permission, $rids);

    // Else check if the permission is included via farm_access rules.
    if (!$has_permission) {
      foreach ($this->loadMultiple($rids) as $role) {
        /** @var \Drupal\user\RoleInterface $role */
        if ($role->getThirdPartySetting('farm_access', 'access', FALSE)) {
          $permissions = $this->getPermissionsForManagedRole($role);
          if (in_array($permission, $permissions)) {
            $has_permission = TRUE;
            break;
          }
        }
      }
    }

    return $has_permission;
  }

  /**
   * Helper function to build permissions for managed roles.
   *
   * @param \Drupal\user\RoleInterface $role
   *   The role to load permissions for.
   *
   * @return array
   *   Array of permissions for the managed role.
   */
  protected function getPermissionsForManagedRole(RoleInterface $role) {

    // Start list of permissions.
    $perms = [];

    // Load the Role's third party farm_access access settings.
    $access_settings = $role->getThirdPartySetting('farm_access', 'access');

    /** @var $managed_role_permissions \Drupal\farm_access\Entity\ManagedRolePermissionsInterface[] */
    $managed_role_permissions = \Drupal::entityTypeManager()->getStorage('managed_role_permissions')->loadMultiple();

    // Include permissions defined by managed_role_permissions config entities.
    foreach ($managed_role_permissions as $role_permissions) {

      // Always include default permissions.
      $default_perms = $role_permissions->getDefaultPermissions();
      $perms = array_merge($perms, $default_perms);

      // Include config permissions if the role has config access.
      if (!empty($access_settings['config'])) {
        $config_perms = $role_permissions->getConfigPermissions();
        $perms = array_merge($perms, $config_perms);
      }
    }

    // Load the access.entity settings. Use an empty array if not provided.
    $entity_settings = $access_settings['entity'] ? $access_settings['entity'] : [];

    // Managed entity types.
    $managed_entity_types = ['log', 'taxonomy_term'];

    // Build permissions for each entity type.
    foreach ($managed_entity_types as $entity_type) {

      // Load all bundles of this entity type.
      $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);
      $bundles = array_keys($bundles);

      // Build permissions for each bundle.
      foreach ($bundles as $bundle) {
        switch ($entity_type) {

          // Log entities.
          case 'log':

            // Create.
            if (!empty($entity_settings['create all'])) {
              $perms[] = 'create ' . $bundle . ' log';
            }

            // View.
            if (!empty($entity_settings['view all'])) {
              $perms[] = 'view any ' . $bundle . ' log';
              $perms[] = 'view own ' . $bundle . ' log';
            }

            // Update.
            if (!empty($entity_settings['update all'])) {
              $perms[] = 'update any ' . $bundle . ' log';
              $perms[] = 'update own ' . $bundle . ' log';
            }

            // Delete.
            if (!empty($entity_settings['delete all'])) {
              $perms[] = 'delete any ' . $bundle . ' log';
              $perms[] = 'delete own ' . $bundle . ' log';
            }
            break;

          // Taxonomy entities.
          case 'taxonomy_term':

            // Create.
            if (!empty($entity_settings['create all'])) {
              $perms[] = 'create terms in ' . $bundle;
            }

            // Update.
            if (!empty($entity_settings['update all'])) {
              $perms[] = 'edit terms in ' . $bundle;
            }

            // Delete.
            if (!empty($entity_settings['delete all'])) {
              $perms[] = 'delete terms in ' . $bundle;
            }
            break;
        }
      }
    }

    return $perms;
  }

}
