<?php

namespace Drupal\farm_access;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeDiscoveryDecorator;
use Drupal\Core\Plugin\Discovery\YamlDiscovery;
use Drupal\user\RoleInterface;

/**
 * ManagedRolePermissions Plugin Manager.
 *
 * @ingroup farm
 */
class ManagedRolePermissionsManager extends DefaultPluginManager implements ManagedRolePermissionsManagerInterface {

  /**
   * Controller resolver service.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  protected $controllerResolver;

  /**
   * Entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Default values for each FarmRolePermissions plugin.
   *
   * @var array
   */
  protected $defaults = [
    'class' => 'Drupal\farm_access\ManagedRolePermissions',
    'default_permissions' => [],
    'config_permissions' => [],
    'permission_callbacks' => [],
  ];

  /**
   * An array of role permissions keyed by role ID.
   *
   * @var array
   */
  protected $rolePermissions;

  /**
   * Constructs a ArchiverManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controller_resolver
   *   The controller resolver service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ControllerResolverInterface $controller_resolver, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct(
      'Plugin/ManagedRolePermissions',
      $namespaces,
      $module_handler
    );
    $this->controllerResolver = $controller_resolver;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->rolePermissions = [];
    $this->setCacheBackend($cache_backend, 'managed_role_permissions_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $discovery = new YamlDiscovery('managed_role_permissions', $this->moduleHandler->getModuleDirectories());
      $this->discovery = new ContainerDerivativeDiscoveryDecorator($discovery);
    }
    return $this->discovery;
  }

  /**
   * {@inheritdoc}
   */
  public function isPermissionInRole($permission, RoleInterface $role) {

    // Check if permissions have been built for the specified role.
    if (isset($this->rolePermissions[$role->id()])) {
      $permissions = $this->rolePermissions[$role->id()];
    }
    else {
      // Build permissions for the role.
      $permissions = $this->getManagedPermissionsForRole($role);
    }

    return in_array($permission, $permissions);
  }

  /**
   * Helper function to build managed permissions for managed roles.
   *
   * @param \Drupal\user\RoleInterface $role
   *   The role to load permissions for.
   *
   * @return array
   *   Array of permissions for the managed role.
   */
  protected function getManagedPermissionsForRole(RoleInterface $role) {

    // Start list of permissions.
    $perms = [];

    // If the role does not have farm_access settings, bail.
    if (!$role->getThirdPartySetting('farm_access', 'access', FALSE)) {
      return $perms;
    }

    // Load the Role's third party farm_access access settings.
    $access_settings = $role->getThirdPartySetting('farm_access', 'access');

    // Get all plugin definitions.
    $plugin_definitions = $this->getDefinitions();

    // Include permissions defined by plugin_definitions config entities.
    foreach ($plugin_definitions as $plugin_definition) {

      // Create an instance of the plugin.
      $plugin = $this->createInstance($plugin_definition['id']);

      // Always include default permissions.
      $default_perms = $plugin->getDefaultPermissions();
      $perms = array_merge($perms, $default_perms);

      // Include config permissions if the role has config access.
      if (!empty($access_settings['config'])) {
        $config_perms = $plugin->getConfigPermissions();
        $perms = array_merge($perms, $config_perms);
      }

      // Include permissions defined by permission callbacks.
      foreach ($plugin->getPermissionCallbacks() as $permission_callback) {

        // Resolve callback name and call the function. Pass the Role object as
        // a parameter so the callback can access the role's settings.
        $callback = $this->controllerResolver->getControllerFromDefinition($permission_callback);
        if ($callback_permissions = call_user_func($callback, $role)) {

          // Add any callback permissions to the array of permissions.
          $perms = array_merge($perms, $callback_permissions);
        }
      }
    }

    // Load the access.entity settings. Use an empty array if not provided.
    $entity_settings = $access_settings['entity'] ? $access_settings['entity'] : [];

    // Managed entity types.
    $managed_entity_types = ['asset', 'log', 'taxonomy_term'];

    // Start an array of permission rules. This will be a multi-dimensional
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

        // Asset and Log entities.
        case 'asset':
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
            $permission_rules[$entity_type]['edit'] = ['all'];
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
      $entity_bundle_info = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
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

            // Asset and Log entities.
            case 'asset':
            case 'log':
              if (array_intersect(['all', $bundle], $allowed_bundles)) {
                $perms[] = $operation . ' ' . $bundle . ' ' . $entity_type;
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

    $this->rolePermissions[$role->id()] = $perms;
    return $perms;
  }

}
