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

    // Start an array of permissions rules. This will be a multi-dimensional
    // array that ultimately defines which permission strings will be given to
    // the managed role. Each entity type's operations can be granted to
    // individual bundles or all bundles by providing 'all' as a bundle name.
    // Once built, the array will contain the following structure:
    // $permission_rules[$entity_types][$operations][$bundles];.
    $permission_rules = [];

    // Build permission rules for each entity type.
    foreach ($managed_entity_types as $entity_type) {

      // Create empty array of operations for the entity_type.
      $permission_rules[$entity_type] = [];

      // Different entity types support different operations. Allow each entity
      // type to map the high level 'create_all', 'view all', 'update all' and
      // 'delete_all' operations to their specific operations.
      switch ($entity_type) {

        // Log entities.
        case 'log':

          // Create.
          if (!empty($entity_settings['create all'])) {
            $permission_rules[$entity_type]['create'] = ['all'];
          }

          // View.
          if (!empty($entity_settings['view all'])) {
            $permission_rules[$entity_type]['view any'] = ['all'];
            $permission_rules[$entity_type]['view own'] = ['all'];
          }

          // Update.
          if (!empty($entity_settings['update all'])) {
            $permission_rules[$entity_type]['update any'] = ['all'];
            $permission_rules[$entity_type]['update own'] = ['all'];
          }

          // Delete.
          if (!empty($entity_settings['delete all'])) {
            $permission_rules[$entity_type]['delete any'] = ['all'];
            $permission_rules[$entity_type]['delete own'] = ['all'];
          }
          break;

        // Taxonomy entities.
        case 'taxonomy_term':

          // Create.
          if (!empty($entity_settings['create all'])) {
            $permission_rules[$entity_type]['create'] = ['all'];
          }

          // Update.
          if (!empty($entity_settings['update all'])) {
            $permission_rules[$entity_type]['update'] = ['all'];
          }

          // Delete.
          if (!empty($entity_settings['delete all'])) {
            $permission_rules[$entity_type]['delete'] = ['all'];
          }
          break;
      }
    }

    // Include granular entity + bundle permissions if defined on the role.
    if (!empty($entity_settings['type'])) {

      // Recursively merge granular permissions into the permission_rules array.
      $permission_rules = array_merge_recursive(
        $permission_rules,
        $entity_settings['type']
      );
    }

    // Build permissions for each entity type as defined in the
    // permission_rules array.
    foreach ($permission_rules as $entity_type => $operations) {

      // Load all bundles of this entity type.
      $entity_bundle_info = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);
      $entity_bundles = array_keys($entity_bundle_info);

      // Build permissions for each operation associated with the entity.
      foreach ($operations as $operation => $allowed_bundles) {

        // Build operation permission for each bundle in the entity.
        foreach ($entity_bundles as $bundle) {

          // Build the operation permission string for each entity type. The
          // permission syntax may be different for each entity type so build
          // permission strings according to the entity type. Only add
          // permissions if the operation explicitly lists the bundle name or
          // specifies 'all' bundles.
          switch ($entity_type) {

            // Log entities.
            case 'log':
              if (array_intersect(['all', $bundle], $allowed_bundles)) {
                $perms[] = $operation . ' ' . $bundle . ' log';
              }
              break;

            // Taxonomy entities.
            case 'taxonomy_term':
              if (array_intersect(['all', $bundle], $allowed_bundles)) {
                $perms[] = $operation . ' terms in ' . $bundle;
              }
              break;
          }
        }
      }
    }

    return $perms;
  }

}
