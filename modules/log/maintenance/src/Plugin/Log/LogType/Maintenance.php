<?php

declare(strict_types=1);

namespace Drupal\farm_maintenance\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the maintenance log type.
 */
#[LogType(
  id: 'maintenance',
  label: new TranslatableMarkup('Maintenance'),
)]
class Maintenance extends FarmLogType {

}
