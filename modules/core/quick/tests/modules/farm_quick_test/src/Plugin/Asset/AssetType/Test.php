<?php

declare(strict_types=1);

namespace Drupal\farm_quick_test\Plugin\Asset\AssetType;

use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the test asset type.
 *
 * @AssetType(
 *   id = "test",
 *   label = @Translation("Test"),
 * )
 */
class Test extends FarmAssetType {

}
