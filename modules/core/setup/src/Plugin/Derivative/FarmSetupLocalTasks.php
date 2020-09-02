<?php

namespace Drupal\farm_setup\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\farm_setup\FarmSetupPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic local tasks for the Farm Setup settings page.
 */
class FarmSetupLocalTasks extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The FarmSetup plugin manager.
   *
   * This is used to get all of the FarmSetup plugins.
   *
   * @var \Drupal\farm_setup\FarmSetupPluginManager
   */
  protected $farmSetupManager;

  /**
   * Constructor.
   *
   * @param \Drupal\farm_setup\FarmSetupPluginManager $farm_setup_manager
   *   The farm setup plugin manger service. We're injecting this service so
   *   that we can use it to access the FarmSetup plugins.
   */
  public function __construct(FarmSetupPluginManager $farm_setup_manager) {
    $this->farmSetupManager = $farm_setup_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    // Load settings forms provided by plugins.
    $settings_forms = $this->farmSetupManager->getFormClasses('settings');

    // Build local task definition for each settings form.
    foreach ($settings_forms as $form_info) {
      $plugin = $form_info['plugin'];
      $definition = $plugin->getPluginDefinition();

      $route_name = 'farm_setup.' . $definition['id'];
      $this->derivatives[$route_name]['route_name'] = $route_name;
      $this->derivatives[$route_name]['base_route'] = 'farm_setup.settings';
      $this->derivatives[$route_name]['title'] = $definition['label'];
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

  /**
   * {@inheritdoc}
   *
   * Override the parent method so that we can inject dependencies.
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('plugin.manager.farm_setup'));
  }

}
