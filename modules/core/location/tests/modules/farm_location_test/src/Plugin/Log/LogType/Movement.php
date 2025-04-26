<?php

declare(strict_types=1);

namespace Drupal\farm_location_test\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the test movement log type.
 */
#[LogType(
  id: 'movement',
  label: new TranslatableMarkup('Movement'),
)]
class Movement extends FarmLogType {

}
