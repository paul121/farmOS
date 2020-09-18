<?php

namespace Drupal\farm_access\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the ManagedRolePermissions entity.
 *
 * @ConfigEntityType(
 *   id = "managed_role_permissions",
 *   label = @Translation("Managed role permissions"),
 *   label_collection = @Translation("Managed role permissions"),
 *   handlers = { },
 *   config_prefix = "managed_role_permissions",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *   },
 *   config_export = {
 *     "id",
 *     "default_permissions",
 *     "config_permissions",
 *     "permission_callbacks",
 *   },
 * )
 *
 * @ingroup farm
 */
class ManagedRoleRolePermissions extends ConfigEntityBase implements ManagedRolePermissionsInterface {

  /**
   * The manged_permissions ID.
   *
   * By convention this should be the module name.
   *
   * @var string
   */
  protected $id;

  /**
   * Default permissions for all managed roles.
   *
   * @var array
   */
  protected $default_permissions = [];

  /**
   * Config permissions for managed roles with config access.
   *
   * @var array
   */
  protected $config_permissions = [];

  /**
   * Permission callbacks that return custom permissions.
   *
   * A list of methods in controller syntax, see
   * \Drupal\Core\Controller\ControllerResolver. These should return an array
   * of permission strings.
   *
   * @var array
   */
  protected $permission_callbacks = [];

  /**
   * {@inheritdoc}
   */
  public function getDefaultPermissions() {
    return $this->default_permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigPermissions() {
    return $this->config_permissions;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionCallbacks() {
    return $this->permission_callbacks;
  }

}
