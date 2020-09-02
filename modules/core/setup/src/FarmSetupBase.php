<?php

namespace Drupal\farm_setup;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginWithFormsTrait;

/**
 * A base class for implementing FarmSetup plugins.
 *
 * @see \Drupal\farm_setup\Annotation\FarmSetup
 * @see \Drupal\farm_setup\FarmSetupInterface
 */
abstract class FarmSetupBase extends PluginBase implements FarmSetupInterface {
  use PluginWithFormsTrait;

}
