<?php

declare(strict_types=1);

namespace Drupal\farm_ui_views\Plugin\views\argument;

use Drupal\Core\Database\Database;
use Drupal\views\Plugin\views\argument\ArgumentPluginBase;
use Drupal\views\Plugin\views\query\Sql;

/**
 * Argument handler for organization asset references on logs.
 *
 * @ViewsArgument("organization_asset")
 */
class OrganizationAssetArgument extends ArgumentPluginBase {

  /**
   * {@inheritdoc}
   */
  public function query($group_by = FALSE) {

    // Bail if not a SQL query.
    if (!$this->query instanceof Sql) {
      return;
    }

    // Bail if there is no argument value.
    if (empty($this->argument)) {
      return;
    }

    // Build a subquery for logs that reference an asset in the specified
    // organization. Include assets from both the asset and location fields.
    $subquery = Database::getConnection()->select('log', 'l');
    $subquery->addField('l', 'id');
    $subquery->leftJoin('log__asset', 'la', 'l.id = la.entity_id');
    $subquery->leftJoin('log__location', 'll', 'l.id = ll.entity_id');
    $subquery->innerJoin('asset__farm', 'af', 'la.asset_target_id = af.entity_id OR ll.location_target_id = af.entity_id');
    $subquery->condition('af.farm_target_id', $this->argument);

    // Use the subquery in a condition on the views query to prevent duplicates.
    $this->query->addWhere(0, "$this->table.id", $subquery, 'IN');
  }

}
