# Farm Access

Provides mechanisms for managing farmOS user access permissions.

## Managed Roles

The Farm Access module provides methods to create user roles with permissions
that are managed for the purposes of farmOS. These roles cannot be modified
from the Admin Permissions UI. Instead, these roles allow permissions to be
provided by other modules that want to provide sensible defaults for common
farmOS roles.

### Creating a managed role

User roles are provided as config entities in Drupal 9. Managed roles are
provided in the same way the only difference being that they include
additional `third party settings` the Farm Access module uses to build
managed permissions. The `user.role.*.third_party.farm_acccess` schema
defines the structure of these settings.

- `access`: An optional array of default access permissions.
  - `config`: Boolean that specifies whether the role should have access to
  configuration. Only grant this to trusted roles.
  - `entity`: Access permissions relating to entities.
    - `view all`: Boolean that specifies the role should have access to view
    all bundles of all entity types.
    - `create all`: Boolean that specifies the role should have access to
    create all bundles of all entity types.
    - `update all`: Boolean that specifies the role should have access to
    update all bundles of all entity types.
    - `delete all`: Boolean that specifies the role should have access to
    delete all bundles of all entity types.
    - `type`: Access permissions for specific entity types.
      - `{entity_type}`: The id of the entity type. eg: `log`,`farm_asset`,
      `taxonomy_term`, etc.
        - `{operation}`: The operation to grant bundles of this entity type
        . Eg: `create`, `view any`, `view own`, `delete any`, `delete own`, etc.
          - `{bundle}`: The id of the entity type bundle or `all` to grant
          the operation permission to all bundles of the entity type.

Settings used for the Farm Manager role (full access to all entities + access
to configuration):


    # user.role.farm_manager.yml
    ... standard role config ...
    third_party_settings:
      farm_access:
        access:
          config: true
          entity:
            view all: true
            create all: true
            update all: true
            delete all: true

Example settings to define a "Farm Harvester" role with these limitations:
* View all log entities.
* Only create harvest logs, update harvest logs, and delete own harvest logs.
* View all farm_asset entities.
* Only update planting assets.
* View, edit and delete any taxonomy_term entity.


    # user.role.farm_harvester.yml
    ... standard role config ...
    third_party_settings:
      farm_access:
        access:
          entity:
            view all: true
            type:
              log:
                create:
                  - harvest
                update any:
                  - harvest
                delete own:
                  - harvest
              farm_asset:
                update any:
                  - planting
              taxonomy_term:
                edit:
                  - all
                delete:
                  - all


### Providing permissions for managed roles

Modules can define sensible permissions to any managed roles. These permissions
are provided by creating a `ManagedRolePermissions` plugin in the
`module.managed_role_permissions.yml` file. The following keys can be provided:

- `default_permissions`: A list of permissions that will be added to *all*
managed roles.
- `config_permissions`: A list of permissions that will be added to managed
 roles that have access to configuration (`config: true`).
- `permission_callbacks`: A list of callbacks in controller notation that
return an array of permissions to add to managed roles. Callbacks are
provided a `Role` object so that permissions can be applied conditionally
based on the managed role's settings.

As an example, the `farm_access` module provides the following permissions:


    # farm_access.managed_role_permissions.yml
    farm_access:
      default_permissions:
        - access content
        - access administration pages
        - access user profiles
        - access taxonomy overview
      config_permissions:
        - administer taxonomy

#### Permission callbacks

Example that adds permissions conditionally based on the role name and settings:

Plugin definition:


    # my_module.managed_role_permissions.yml
    my_module:
      permission_callbacks:
        - Drupal\my_module\CustomPermissions::permissions


Example implementation of a `permission_callback`:


    <?php

    # my_module/src/CustomPermissions.php

    namespace Drupal\my_module;

    use Drupal\user\RoleInterface;

    /**
     * Example custom permission callback.
     */
    class CustomPermissions {

      public function permissions(RoleInterface $role) {

        // Array of permissions to return.
        $perms = [];

        // Add permissions based on role name.
        if ($role->id() == 'farm_manager') {
          $perms = 'my manager permission';
        }

        // Get the farm_access third party settings from the Role entity.
        $access_settings = $role->getThirdPartySetting('farm_access', 'access');
        $entity_settings = $access_settings['entity'] ?: [];

        // Only add permissions if `update all` and `delete all` are true.
        if (!empty($entity_settings['update all'] && $entity_settings['delete all'])) {
          $perms[] = 'recover all permission';
        }

        // Return array of permissions.
        return $perms;
      }
    }
