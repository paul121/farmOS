<?php

declare(strict_types=1);

namespace Drupal\farm_quick_test\Plugin\Quantity\QuantityType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\QuantityType;
use Drupal\farm_entity\Plugin\Quantity\QuantityType\FarmQuantityType;

/**
 * Provides the test2 quantity type.
 */
#[QuantityType(
  id: 'test2',
  label: new TranslatableMarkup('Test2'),
)]
class Test2 extends FarmQuantityType {

}
