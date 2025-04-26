<?php

declare(strict_types=1);

namespace Drupal\farm_quick_test\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the test log type.
 */
#[LogType(
  id: 'test',
  label: new TranslatableMarkup('Test'),
)]
class Test extends FarmLogType {

}
