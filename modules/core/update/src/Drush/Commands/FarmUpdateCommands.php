<?php

declare(strict_types=1);

namespace Drupal\farm_update\Drush\Commands;

use Drupal\farm_update\FarmUpdateInterface;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Farm Update Drush commands.
 *
 * @ingroup farm
 */
final class FarmUpdateCommands extends DrushCommands {

  use AutowireTrait;

  /**
   * Constructs a FarmUpdateCommands object.
   */
  public function __construct(
    #[Autowire(service: 'farm.update')]
    private readonly FarmUpdateInterface $farmUpdate,
  ) {
    parent::__construct();
  }

  /**
   * Rebuild farmOS configuration.
   */
  #[CLI\Command(name: 'farm_update:rebuild')]
  #[CLI\Usage(name: 'farm_update:rebuild', description: 'Rebuild farmOS configuration.')]
  public function rebuild() {
    $this->farmUpdate->rebuild();
  }

}
