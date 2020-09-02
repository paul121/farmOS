<?php

namespace Drupal\farm_setup;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\farm_setup\Annotation\FarmSetup;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * A plugin manager for FarmSetup plugins.
 *
 * FarmSetup plugins can provide forms to display in the farmOS Settings and
 *  Setup Wizard pages.
 */
class FarmSetupPluginManager extends DefaultPluginManager {

  /**
   * Creates the discovery object.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {

    // Look for FarmSetup plugins in the src/Plugin/FarmSetup directory.
    $subdir = 'Plugin/FarmSetup';

    // FarmSetup plugins should implement the FarmSetupPluginInterface.
    $plugin_interface = FarmSetupInterface::class;

    // Define the annotation class that defines FarmSetup plugins.
    $plugin_definition_annotation_name = FarmSetup::class;

    parent::__construct($subdir, $namespaces, $module_handler, $plugin_interface, $plugin_definition_annotation_name);

    // Set the configured cache backend. We specify the @cache.default service
    // to be used in farm_setup.services.yml.
    $this->setCacheBackend($cache_backend, 'farm_setup_info', ['farm_setup_info']);
  }

  /**
   * Helper function to load forms of a given class provided by plugins.
   *
   * @param string $class_name
   *   The name of the form class to load. Either "settings" or "setup_wizard".
   *
   * @return array
   *   An array of form info sorted ascending by plugin weight.
   *   The 'form_class' key is a string of the Form class name.
   *   The 'plugin' key is an instance of the providing FarmSetup plugin.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getFormClasses($class_name) {

    // Start empty array of plugins.
    $farm_setup_plugins = $this->getDefinitions();

    // Sort plugins by weight.
    $weights = array_column($farm_setup_plugins, 'weight');
    array_multisort(
      $weights,
      SORT_ASC,
      $farm_setup_plugins);

    // Search for plugins that provide the specified form class.
    $forms = [];
    foreach ($farm_setup_plugins as $farm_setup_plugin) {
      try {
        $plugin = $this->createInstance($farm_setup_plugin['id']);
      }
      catch (PluginException $e) {
        continue;
      }

      if ($plugin->hasFormClass($class_name)) {
        $form_class = $plugin->getFormClass($class_name);
        $forms[] = [
          'form_class' => $form_class,
          'plugin' => $plugin,
        ];
      }
    }

    return $forms;
  }

  /**
   * Route callback function.
   *
   * Adds dynamic routes for settings page forms.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   Route definitions.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function routes() {

    // Start a route collection to return.
    $routes = new RouteCollection();

    // Load all settings forms provided by plugins.
    $settings_forms = $this->getFormClasses('settings');

    // Generate a route to each form.
    foreach ($settings_forms as $form_info) {
      $form_class = $form_info['form_class'];
      $plugin = $form_info['plugin'];
      $definition = $plugin->getPluginDefinition();
      $route = new Route(
        '/farm/setup/' . $definition['id'],
        // Route defaults:
        [
          '_form' => $form_class,
          '_title' => (string) $definition['label'],
        ],
        // Route requirements:
        [
          '_permission' => 'access content',
        ]
      );
      // Add route to collection.
      $routes->add('farm_setup.' . $definition['id'], $route);
    }

    return $routes;
  }

}
