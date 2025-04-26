<?php

declare(strict_types=1);

namespace Drupal\farm_compost\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the compost asset type.
 */
#[AssetType(
  id: 'compost',
  label: new TranslatableMarkup('Compost'),
)]
class Compost extends FarmAssetType {

}
