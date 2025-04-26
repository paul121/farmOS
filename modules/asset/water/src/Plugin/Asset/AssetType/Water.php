<?php

declare(strict_types=1);

namespace Drupal\farm_water\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the water asset type.
 */
#[AssetType(
  id: 'water',
  label: new TranslatableMarkup('Water'),
)]
class Water extends FarmAssetType {

}
