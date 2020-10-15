<?php

namespace Drupal\farm_access;

use Drupal\Core\Plugin\PluginBase;

/**
 * Default class for the ManagedRolePermissions plugin.
 *
 * @ingroup farm
 */
class ManagedRolePermissions extends PluginBase implements ManagedRolePermissionsInterface {

  /**
   * {@inheritdoc}
   */
  public function getDefaultPermissions() {
    return (array) $this->pluginDefinition['default_permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigPermissions() {
    return (array) $this->pluginDefinition['config_permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionCallbacks() {
    return (array) $this->pluginDefinition['permission_callbacks'];
  }

}
