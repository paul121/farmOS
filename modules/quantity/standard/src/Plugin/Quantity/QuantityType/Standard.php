<?php

declare(strict_types=1);

namespace Drupal\farm_quantity_standard\Plugin\Quantity\QuantityType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\QuantityType;
use Drupal\farm_entity\Plugin\Quantity\QuantityType\FarmQuantityType;

/**
 * Provides the standard quantity type.
 */
#[QuantityType(
  id: 'standard',
  label: new TranslatableMarkup('Standard'),
)]
class Standard extends FarmQuantityType {

}
