<?php

declare(strict_types=1);

namespace Drupal\farm_quick_test\Plugin\Log\LogType;

use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the test log type.
 *
 * @LogType(
 *   id = "test",
 *   label = @Translation("Test"),
 * )
 */
class Test extends FarmLogType {

}
