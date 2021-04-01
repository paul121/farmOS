<?php

namespace Drupal\farm_group\Plugin\views\argument_validator;

use Drupal\views\Plugin\views\argument_validator\Entity;

/**
 * Validates asset IDs and includes group history logs in the query.
 *
 * @see farm_group.module
 *
 * @ViewsArgumentValidator(
 *   id = "asset_and_group",
 *   title = @Translation("Asset and group"),
 *   entity_type = "asset"
 * )
 */
class AssetAndGroup extends Entity {

}
