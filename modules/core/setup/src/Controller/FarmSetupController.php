<?php

namespace Drupal\farm_setup\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Class FarmSetupController.
 */
class FarmSetupController extends ControllerBase {

  /**
   * Settings page.
   *
   * @return array
   *   Markup.
   */
  public function settingsPage() {
    $setup_wizard_url = Url::fromRoute('farm_setup.setup_wizard');
    $setup_wizard_link = Link::fromTextAndUrl('Go to the wizard', $setup_wizard_url);
    return [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->t('The setup wizard will guide you through the process of configuring your farmOS.') . '</p><p>' . $setup_wizard_link->toString() . '</p>',
    ];
  }

}
