<?php

namespace Drupal\farm_access;

/**
 *
 */
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Example configuration override.
 */
class ConfigOverrider implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {

    // Start an array of overrides.
    $overrides = [];

    // TODO: Inject farm_role_manager service as dependency.
    $farm_role_manager = \Drupal::service('farm_roles_manager');

    // Load managed farm roles.
    $farm_roles = $farm_role_manager->getFarmRoles();

    // Check if overrides are being loaded for this role.
    foreach ($farm_roles as $role) {
      if (in_array('user.role.' . $role, $names)) {

        // TODO: Inject config factory as a dependency.
        // Load original perms.
        $original = \Drupal::configFactory()->getEditable('user.role.' . $role)->getOriginal('', FALSE);
        $original_perms = $original['permissions'];

        // Load overridden perms for this role.
        $managed_perms = $farm_role_manager->getRolePermissions($role);

        // Set the override.
        $overrides['user.role.' . $role] = [
          'permissions' => array_merge($original_perms, $managed_perms),
        ];
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'ConfigExampleOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
