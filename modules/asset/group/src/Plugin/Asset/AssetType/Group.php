<?php

declare(strict_types=1);

namespace Drupal\farm_group\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the group asset type.
 */
#[AssetType(
  id: 'group',
  label: new TranslatableMarkup('Group'),
)]
class Group extends FarmAssetType {

}
