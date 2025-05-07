<?php

/**
 * @file
 * Provides Views runtime hooks for farm_ui_views.module.
 */

declare(strict_types=1);

use Drupal\Core\Entity\Sql\SqlContentEntityStorageException;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;

/**
 * Implements hook_views_pre_view().
 */
function farm_ui_views_views_pre_view(ViewExecutable $view, $display_id, array &$args) {

  // We only want to alter the Views we provide.
  if (!in_array($view->id(), [
    'farm_asset',
    'farm_log',
    'farm_log_quantity',
    'farm_organization_asset',
    'farm_organization_log',
    'farm_plan',
  ])) {
    return;
  }

  // Get the entity field manager service.
  /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager */
  $entity_field_manager = \Drupal::service('entity_field.manager');

  // Add field and filter handlers for base fields provided by other modules.
  $base_fields = \Drupal::moduleHandler()->invokeAll('farm_ui_views_base_fields', [$view->getBaseEntityType()->id()]);
  foreach ($entity_field_manager->getBaseFieldDefinitions($view->getBaseEntityType()->id()) as $field_definition) {
    if (!in_array($field_definition->getName(), $base_fields)) {
      continue;
    }
    farm_ui_views_add_handlers($view, $display_id, 'field', $field_definition);
    farm_ui_views_add_handlers($view, $display_id, 'filter', $field_definition);
  }

  // If this is a "By type" display, alter the fields and filters.
  $bundle = farm_ui_views_get_bundle_argument($view, $display_id, $args);
  if (!empty($bundle)) {

    // Remove the type field and filter handlers.
    $view->removeHandler($display_id, 'field', 'type');
    $view->removeHandler($display_id, 'filter', 'type');

    // If the entity type has a bundle_plugin manager, add all of its
    // bundle fields and filters to the page_type view.
    if (\Drupal::entityTypeManager()->hasHandler($view->getBaseEntityType()->id(), 'bundle_plugin')) {
      /** @var \Drupal\entity\BundleFieldDefinition[] $bundle_fields */
      $bundle_fields = \Drupal::entityTypeManager()->getHandler($view->getBaseEntityType()->id(), 'bundle_plugin')->getFieldDefinitions($bundle);
      foreach (array_reverse($bundle_fields) as $field_definition) {
        farm_ui_views_add_handlers($view, $display_id, 'field', $field_definition);
        farm_ui_views_add_handlers($view, $display_id, 'filter', $field_definition);
      }
    }
  }

  // Remove the asset and location filters from the log page_asset display.
  // @todo Make the AssetOrLocationArgument compatible with these filters.
  if ($view->id() == 'farm_log' && $display_id == 'page_asset') {
    $view->removeHandler($display_id, 'filter', 'asset_target_id');
    $view->removeHandler($display_id, 'filter', 'location_target_id');
  }

  // If this is the "Upcoming" or "Late" Logs block display, add a "more" link
  // that points to the default page display with appropriate filters.
  if ($view->id() == 'farm_log' && in_array($display_id, ['block_upcoming', 'block_late'])) {
    $view->display_handler->setOption('use_more', TRUE);
    $view->display_handler->setOption('use_more_always', TRUE);
    $view->display_handler->setOption('link_display', 'custom_url');
    $today = date('Y-m-d', \Drupal::time()->getRequestTime());
    if ($display_id == 'block_upcoming') {
      $view->display_handler->setOption('use_more_text', t('View all upcoming logs'));
      $view->display_handler->setOption('link_url', 'logs?status[]=pending&start=' . $today . '&order=timestamp&sort=asc');
    }
    elseif ($display_id == 'block_late') {
      $view->display_handler->setOption('use_more_text', t('View all late logs'));
      $view->display_handler->setOption('link_url', 'logs?status[]=pending&end=' . $today);
    }
  }
}

/**
 * Implements hook_views_pre_render().
 */
function farm_ui_views_views_pre_render(ViewExecutable $view) {

  // We only want to alter the Views we provide.
  if (!in_array($view->id(), ['farm_asset', 'farm_log', 'farm_log_quantity', 'farm_plan'])) {
    return;
  }

  // We may set the View page title, but assume not.
  $title = '';

  // If this is the farm_asset View and page_children display, include the
  // asset's name.
  if ($view->id() == 'farm_asset' && $view->current_display == 'page_children') {
    $asset_id = $view->args[0];
    $asset = \Drupal::entityTypeManager()->getStorage('asset')->load($asset_id);
    if (!empty($asset)) {
      $title = t('Children of %asset', ['%asset' => $asset->label()]);
    }
  }

  // If this is the farm_asset View and page_location display, include the
  // asset's name.
  if ($view->id() == 'farm_asset' && $view->current_display == 'page_location') {
    $asset_id = $view->args[0];
    $asset = \Drupal::entityTypeManager()->getStorage('asset')->load($asset_id);
    if (!empty($asset)) {
      $title = t('Assets in %location', ['%location' => $asset->label()]);
    }
  }

  // If this is the farm_log View and page_asset display, include the asset's
  // name.
  if ($view->id() == 'farm_log' && $view->current_display == 'page_asset') {
    $asset_id = $view->args[0];
    $asset = \Drupal::entityTypeManager()->getStorage('asset')->load($asset_id);
    if (!empty($asset)) {
      $title = $asset->label() . ' ' . $view->getBaseEntityType()->getPluralLabel();
    }
  }

  // If this is a "By type" display and a bundle argument is specified, load
  // the bundle label and set the title.
  $bundle = farm_ui_views_get_bundle_argument($view, $view->current_display, $view->args);
  if (!empty($bundle)) {
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($view->getBaseEntityType()->id());
    if (!empty($bundles[$bundle])) {
      $title = $bundles[$bundle]['label'] . ' ' . $view->getBaseEntityType()->getPluralLabel();
    }
  }

  // If this is the farm_asset/farm_log View and page_term display, include
  // the term's name.
  if (in_array($view->id(), ['farm_asset', 'farm_log']) && $view->current_display == 'page_term') {
    $term_id = $view->args[0];
    $entity_bundle = $view->args[1];
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term_id);

    if (!empty($term)) {
      $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load($term->bundle());

      $entity_bundle_label = '';
      if ($entity_bundle != 'all') {
        $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($view->getBaseEntityType()->id());
        if (!empty($bundles[$entity_bundle])) {
          $entity_bundle_label = $bundles[$entity_bundle]['label'] . ' ' . $view->getBaseEntityType()->getPluralLabel();
        }
      }

      if (!empty($entity_bundle_label)) {
        $title = t('%bundle with %vocab term %term', [
          '%bundle' => $entity_bundle_label,
          '%vocab' => $vocabulary->label(),
          '%term' => $term->label(),
        ]);
      }
      else {
        $title = t('%base_type with %vocab term %term', [
          '%base_type' => $view->getBaseEntityType()->getCollectionLabel(),
          '%vocab' => $vocabulary->label(),
          '%term' => $term->label(),
        ]);
      }
    }
  }

  // Set the title, if so desired.
  if (!empty($title)) {
    $view->setTitle($title);
  }
}

/**
 * Helper function for adding field and filter handlers to a View.
 *
 * @param \Drupal\views\ViewExecutable $view
 *   The View to add handlers to.
 * @param string $display_id
 *   The ID of the View display to add handlers to.
 * @param string $type
 *   The handler type ('field' or 'filter').
 * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
 *   The field definition to add Views field/filter handlers for.
 */
function farm_ui_views_add_handlers(ViewExecutable $view, string $display_id, string $type, FieldDefinitionInterface $field_definition) {

  // If the display's fields/filters are overridden, bail.
  // We assume that fields/filters were overridden for a reason, and therefore
  // we shouldn't dynamically add anything here.
  if (
    ($type == 'field' && !$view->getDisplay()->getOption('defaults')['fields'])
    ||
    ($type == 'filter' && !$view->getDisplay()->getOption('defaults')['filters'])
    ) {
    return;
  }

  // Get the entity and bundle.
  $base_entity = $view->getBaseEntityType();

  // Get the entity storage and table mapping.
  /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $entity_storage */
  $entity_storage = \Drupal::entityTypeManager()->getStorage($base_entity->id());
  $table_mapping = $entity_storage->getTableMapping();

  // Skip if the field's view display is "hidden".
  $view_options = $field_definition->getDisplayOptions('view');
  if (empty($view_options) || (!empty($view_options['region']) && $view_options['region'] == 'hidden')) {
    return;
  }

  // Save the field type.
  $field_type = $field_definition->getType();

  // Build a views option name, so we can load its views data definition.
  // First try to get the table from the field's table mapping.
  try {
    $table = $table_mapping->getFieldTableName($field_definition->getName());
  }
  // Else default to the entity type's base table.
  // This is the convention that computed fields should follow when defining
  // views data since they do not have a table created in the database.
  catch (SqlContentEntityStorageException $e) {
    $table = $entity_storage->getBaseTable();
  }

  // Build the column name.
  // If this is base field stored in the entity's base data table, or if this is
  // a computed field, then the column name is simply the field name. Otherwise,
  // it is the field name + main property name.
  if ($table == $entity_storage->getDataTable() || $field_definition->isComputed()) {
    $column_name = $field_definition->getName();
  }
  else {
    $property_name = $field_definition->getFieldStorageDefinition()->getMainPropertyName();
    $column_name = $field_definition->getName() . '_' . $property_name;
  }

  // Fraction fields do not have a main property name, so build it manually.
  if ($field_type == 'fraction') {
    $column_name = $field_definition->getName() . '_value';
  }

  // Combine the table and column names.
  $views_option_name = $table . '.' . $column_name;

  // Add a field handler if a views data field definition exists.
  if ($type == 'field') {
    $field_options = Views::viewsDataHelper()->fetchFields($table, 'field');
    if (isset($field_options[$views_option_name])) {

      // Build field options for the field type.
      $field_options = [];
      $sort_order = 'asc';

      // Get the field label.
      $field_options['label'] = $field_definition->getLabel();

      // Add settings that are specific to field types.
      switch ($field_type) {

        case 'timestamp':
          // Render timestamp fields in the html_date format.
          $field_options['type'] = 'timestamp';
          $field_options['settings']['date_format'] = 'html_date';

          // Sort timestamps descending so new dates are on top by default.
          $sort_order = 'desc';
          break;

        case 'boolean':
        case 'entity_reference':
        case 'fraction':
        case 'list_string':
        case 'string':
          // Field types that do not need any modifications.
          break;

        default:
          // Do not add field handlers for unsupported field types.
          return;
      }

      // Add the field handler.
      $new_field_id = $view->addHandler($display_id, 'field', $table, $column_name, $field_options);

      // Determine what position to insert the field handler.
      switch ($base_entity->id()) {
        case 'asset':
        case 'plan':
          $sort_field = 'name';
          break;

        case 'log':
          $sort_field = 'quantity_target_id';
          break;

        case 'quantity':
        default:
          $sort_field = FALSE;
          break;
      }

      // Sort the field handlers if necessary.
      if (!empty($sort_field)) {
        farm_ui_views_sort_field($view, $display_id, $new_field_id, $sort_field);
      }

      // Add the field to the table style options.
      $view->getStyle()->options['columns'][$new_field_id] = $new_field_id;
      $view->getStyle()->options['info'][$new_field_id] = [
        'sortable' => TRUE,
        'default_sort_order' => $sort_order,
        'align' => '',
        'separator' => '',
        'empty_column' => TRUE,
        'responsive' => '',
      ];
    }
  }

  // Add a filter handler if a views data filter definition exists.
  elseif ($type == 'filter') {
    $filter_options = Views::viewsDataHelper()->fetchFields($table, 'filter');
    if (isset($filter_options[$views_option_name])) {
      $filter_options = [
        'id' => $field_definition->getName(),
        'table' => $table,
        'field' => $column_name,
        'exposed' => TRUE,
        'expose' => [
          'operator_id' => $column_name . '_op',
          'label' => $filter_options[$views_option_name]['title'],
          'identifier' => $column_name,
          'multiple' => TRUE,
        ],
        'entity_type' => $base_entity->id(),
        'entity_field' => $field_definition->getName(),
      ];

      // Build filter options for the field type.
      switch ($field_type) {

        case 'boolean':
          $filter_options['value'] = 'All';
          break;

        case 'entity_reference':
          $target_type = $field_definition->getSetting('target_type');

          // Use a select widget for taxonomy term references.
          if ($target_type === 'taxonomy_term') {
            $filter_options['type'] = 'select';

            // Limit to specific vocabularies if configured.
            $handler_settings = $field_definition->getSetting('handler_settings');
            $filter_options['limit'] = FALSE;
            if (!empty($handler_settings['target_bundles'])) {
              $filter_options['limit'] = TRUE;
              $filter_options['vid'] = reset($handler_settings['target_bundles']);
            }
          }

          // We use autocomplete for other entity types.
          else {
            $filter_options['type'] = 'autocomplete';

            // If Views is used for the handler, or if target bundles are
            // specified, then pass the handler and handler settings through
            // to the filter so they can be used to limit options.
            $handler = $field_definition->getSetting('handler');
            $handler_settings = $field_definition->getSetting('handler_settings');
            if ($handler === 'views' || !empty($handler_settings['target_bundles'])) {
              $filter_options['sub_handler'] = $handler;
              $filter_options['sub_handler_settings'] = $handler_settings;
            }
          }

          break;

        case 'string':
          // String fields use the contains operator.
          $filter_options['operator'] = 'contains';
          break;

        case 'list_string':
        case 'timestamp':
          // Field types that do not need any modifications.
          break;

        default:
          // Do not add filter handlers for unsupported field types.
          return;
      }

      // Add the filter handler.
      $view->addHandler($display_id, 'filter', $table, $column_name, $filter_options);
    }
  }
}

/**
 * Helper function for sorting a field handler.
 *
 * Based off the \Drupal\views_ui\Form\Ajax\Rearrange.php method of ordering
 * handlers in views.
 *
 * @param \Drupal\views\ViewExecutable $view
 *   The View to add handlers to.
 * @param string $display_id
 *   The ID of the View display to add handlers to.
 * @param string $field_id
 *   The ID of the field to sort.
 * @param string $base_field_id
 *   The ID of an existing field in the View. The field defined by $field_id
 *   will be added before/after this field in the View.
 * @param bool $before
 *   If TRUE, the field will be added before the field defined by $base_field_id
 *   instead of after.
 */
function farm_ui_views_sort_field(ViewExecutable $view, string $display_id, string $field_id, string $base_field_id, bool $before = FALSE) {

  // Get the existing field handlers.
  $type = 'field';
  $types = ViewExecutable::getHandlerTypes();
  $display = $view->displayHandlers->get($display_id);
  $field_handlers = $display->getOption($types[$type]['plural']);

  // Define the new field handler and insert at desired position.
  $new_field_handler = [$field_id => $field_handlers[$field_id]];
  $keys = array_keys($field_handlers);
  $index = array_search($base_field_id, $keys, TRUE);
  $pos = empty($index) ? count($field_handlers) : $index;
  if (!$before) {
    $pos++;
  }
  $new_field_handlers = array_merge(array_slice($field_handlers, 0, $pos, TRUE), $new_field_handler, array_slice($field_handlers, $pos, NULL, TRUE));

  // Set the display to use the sorted field handlers.
  $display->setOption($types[$type]['plural'], $new_field_handlers);
}
