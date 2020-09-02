<?php

namespace Drupal\farm_setup\Plugin\FarmSetup;

use Drupal\farm_setup\FarmSetupBase;

/**
 * Provides forms for installing farmOS modules.
 *
 * @FarmSetup(
 *   id = "modules",
 *   label = @Translation("Modules"),
 *   weight = 0,
 *   forms = {
 *     "setup_wizard" = "\Drupal\farm_setup\Form\FarmSetupModulesForm",
 *     "settings" = "\Drupal\farm_setup\Form\FarmSetupModulesForm",
 *   },
 * )
 */
class FarmSetupModules extends FarmSetupBase {
}
