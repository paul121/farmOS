<?php

namespace Drupal\farm_api\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\simple_oauth\Entities\ClientEntityInterface;

/**
 * An interface for defining Farm OAuth Client config entities.
 *
 * @ingroup farm
 */
interface FarmClientInterface extends ConfigEntityInterface, ClientEntityInterface {

  /**
   *
   */
}
