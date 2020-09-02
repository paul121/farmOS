<?php

namespace Drupal\farm_setup\Plugin\FarmSetup;

use Drupal\farm_setup\FarmSetupBase;

/**
 * Provides a setup wizard form for configuring the default timezone.
 *
 * @FarmSetup(
 *   id = "timezone",
 *   label = @Translation("Timezone"),
 *   weight = 1,
 *   forms = {
 *     "setup_wizard" = "\Drupal\farm_setup\Form\FarmSetupTimezoneForm",
 *   },
 * )
 */
class FarmSetupTimezone extends FarmSetupBase {
}
