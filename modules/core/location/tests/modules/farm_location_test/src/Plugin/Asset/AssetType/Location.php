<?php

declare(strict_types=1);

namespace Drupal\farm_location_test\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the test location asset type.
 */
#[AssetType(
  id: 'location',
  label: new TranslatableMarkup('Location'),
)]
class Location extends FarmAssetType {

}
