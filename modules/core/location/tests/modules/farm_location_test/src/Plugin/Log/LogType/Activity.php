<?php

declare(strict_types=1);

namespace Drupal\farm_location_test\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the test activity log type.
 */
#[LogType(
  id: 'activity',
  label: new TranslatableMarkup('Activity'),
)]
class Activity extends FarmLogType {

}
