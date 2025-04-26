<?php

declare(strict_types=1);

namespace Drupal\farm_entity_test\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the test asset type.
 */
#[AssetType(
  id: 'test',
  label: new TranslatableMarkup('Test'),
)]
class Test extends FarmAssetType {

}
