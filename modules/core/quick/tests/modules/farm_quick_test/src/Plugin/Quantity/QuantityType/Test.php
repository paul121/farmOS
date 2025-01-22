<?php

declare(strict_types=1);

namespace Drupal\farm_quick_test\Plugin\Quantity\QuantityType;

use Drupal\farm_entity\Plugin\Quantity\QuantityType\FarmQuantityType;

/**
 * Provides the test quantity type.
 *
 * @QuantityType(
 *   id = "test",
 *   label = @Translation("Test"),
 * )
 */
class Test extends FarmQuantityType {

}
