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
      $log_quantities[] = $query->execute()->fetchAssoc();
    }

    // Add the quantity logs to the row for future processing.
    $row->setSourceProperty('log_quantities', $log_quantities);

    return parent::prepareRow($row);
  }

}
