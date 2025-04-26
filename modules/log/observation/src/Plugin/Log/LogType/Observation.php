<?php

declare(strict_types=1);

namespace Drupal\farm_observation\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the observation log type.
 */
#[LogType(
  id: 'observation',
  label: new TranslatableMarkup('Observation'),
)]
class Observation extends FarmLogType {

}
