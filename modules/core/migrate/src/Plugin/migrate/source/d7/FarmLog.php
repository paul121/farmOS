<?php

namespace Drupal\farm_migrate\Plugin\migrate\source\d7;

use Drupal\log\Plugin\migrate\source\d7\Log;
use Drupal\migrate\Row;

/**
 * Log source from database.
 *
 * Extends the Log source plugin to include source properties needed for the
 * farmOS migration.
 *
 * @MigrateSource(
 *   id = "d7_farm_log",
 *   source_module = "log"
 * )
 */
class FarmLog extends Log {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $id = $row->getSourceProperty('id');

    // Get values from the log that will be inherited to created quantities.
    $log_uid = $row->getSourceProperty('uid');
    $log_created = $row->getSourceProperty('created');
    $log_changed = $row->getSourceProperty('changed');

    // Get quantity log field value.
    $quantity_values = $this->getFieldvalues('log', 'field_farm_quantity', $id);

    // Iterate through quantity field values to collect field collection IDs.
    $quantity_field_collection_item_ids = [];
    foreach ($quantity_values as $quantity_value) {
      if (!empty($quantity_value['value'])) {
        $quantity_field_collection_item_ids[] = $quantity_value['value'];
      }
    }

    // Iterate through the field collection IDs and load values.
    $log_quantities = [];
    foreach ($quantity_field_collection_item_ids as $item_id) {
      $query = $this->select('field_collection_item', 'fci')
        ->condition('fci.item_id', $item_id)
        ->condition('fci.field_name', 'field_farm_quantity');

      // Join the quantity label field.
      $query->leftJoin('field_data_field_farm_quantity_label', 'fdffql', 'fdffql.entity_id = fci.item_id AND fdffql.deleted = 0');
      $query->addField('fdffql', 'field_farm_quantity_label_value', 'label');

      // Join the quantity measure field.
      $query->leftJoin('field_data_field_farm_quantity_measure', 'fdffqm', 'fdffqm.entity_id = fci.item_id AND fdffqm.deleted = 0');
      $query->addField('fdffqm', 'field_farm_quantity_measure_value', 'measure');

      // Join the quantity units field.
      $query->leftJoin('field_data_field_farm_quantity_units', 'fdffqu', 'fdffqu.entity_id = fci.item_id AND fdffqu.deleted = 0');
      $query->addField('fdffqu', 'field_farm_quantity_units_tid', 'units');

      // Join the quantity value field.
      $query->leftJoin('field_data_field_farm_quantity_value', 'fdffqv', 'fdffqv.entity_id = fci.item_id AND fdffqv.deleted = 0');
      $query->addField('fdffqv', 'field_farm_quantity_value_numerator', 'value_numerator');
      $query->addField('fdffqv', 'field_farm_quantity_value_denominator', 'value_denominator');

      // Execute the query.
      $quantity_data = $query->execute()->fetchAssoc();

      // Add values to inherit from the log.
      $quantity_data['uid'] = $log_uid;
      $quantity_data['created'] = $log_created;
      $quantity_data['changed'] = $log_changed;

      // Save the quantity to be created later.
      $log_quantities[] = $quantity_data;
    }

    // Add the quantity logs to the row for future processing.
    $row->setSourceProperty('log_quantities', $log_quantities);

    // Get log inventory field value.
    $inventory_values = $this->getFieldvalues('log', 'field_farm_inventory', $id);

    // Iterate through inventory field values to collect field collection IDs.
    $inventory_field_collection_item_ids = [];
    foreach ($inventory_values as $inventory_value) {
      if (!empty($inventory_value['value'])) {
        $inventory_field_collection_item_ids[] = $inventory_value['value'];
      }
    }

    // Iterate through the field collection IDs and load values.
    $inventories = [];
    foreach ($inventory_field_collection_item_ids as $item_id) {
      $query = $this->select('field_collection_item', 'fci')
        ->condition('fci.item_id', $item_id)
        ->condition('fci.field_name', 'field_farm_inventory');

      // Join the inventory asset field.
      $query->leftJoin('field_data_field_farm_inventory_asset', 'fdffia', 'fdffia.entity_id = fci.item_id AND fdffia.deleted = 0');
      $query->addField('fdffia', 'field_farm_inventory_asset_target_id', 'asset');

      // Join the inventory value field.
      $query->leftJoin('field_data_field_farm_inventory_value', 'fdffiv', 'fdffiv.entity_id = fci.item_id AND fdffiv.deleted = 0');
      $query->addField('fdffiv', 'field_farm_inventory_value_numerator', 'value_numerator');
      $query->addField('fdffiv', 'field_farm_inventory_value_denominator', 'value_denominator');

      // Execute the query.
      $inventory_data = $query->execute()->fetchAssoc();

      // Default to an increment adjustment.
      $adjustment = 'increment';

      // If value_numerator is negative, then it is a decrement.
      if ($inventory_data['value_numerator'] < 0) {
        $adjustment = 'decrement';

        // Use the absolute value of the numerator.
        $inventory_data['value_numerator'] = abs($inventory_data['value_numerator']);
      }

      // Add adjustment to the data.
      $inventory_data['adjustment'] = $adjustment;

      // Add values to inherit from the log.
      $inventory_data['uid'] = $log_uid;
      $inventory_data['created'] = $log_created;
      $inventory_data['changed'] = $log_changed;

      // Save the inventory quantity to be created later.
      $inventories[] = $inventory_data;
    }

    // Add the quantity logs to the row for future processing.
    $row->setSourceProperty('log_inventories', $inventories);

    return parent::prepareRow($row);
  }

}
