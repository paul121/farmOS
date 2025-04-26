<?php

declare(strict_types=1);

namespace Drupal\farm_group_test\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the animal asset type.
 */
#[AssetType(
  id: 'animal',
  label: new TranslatableMarkup('Animal'),
)]
class Animal extends FarmAssetType {

}
