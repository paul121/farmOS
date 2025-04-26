<?php

declare(strict_types=1);

namespace Drupal\farm_group_test\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the pasture asset type.
 */
#[AssetType(
  id: 'pasture',
  label: new TranslatableMarkup('Pasture'),
)]
class Pasture extends FarmAssetType {

}
