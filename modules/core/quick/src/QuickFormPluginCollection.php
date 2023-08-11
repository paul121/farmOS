<?php

namespace Drupal\farm_quick;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;

/**
 * Provides a collection of quick form plugins.
 */
class QuickFormPluginCollection extends DefaultSingleLazyPluginCollection {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The quick form ID this plugin collection belongs to.
   *
   * @var string
   */
  protected $quickFormId;

  /**
   * Constructs a new QuickFormPluginCollection.
   *
   * @param \Drupal\Component\Plugin\PluginManagerInterface $manager
   *   The manager to be used for instantiating plugins.
   * @param string $instance_id
   *   The ID of the plugin instance.
   * @param array $configuration
   *   An array of configuration.
   * @param string $quick_form_id
   *   The unique ID of the quick form entity using this plugin.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(PluginManagerInterface $manager, string $instance_id, array $configuration, string $quick_form_id, ModuleHandlerInterface $module_handler,) {
    parent::__construct($manager, $instance_id, $configuration);
    $this->moduleHandler = $module_handler;
    $this->quickFormId = $quick_form_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    if (!$instance_id) {
      throw new PluginException("The quick form '{$this->quickFormId}' did not specify a plugin.");
    }

    try {
      parent::initializePlugin($instance_id);
    }
    catch (PluginException $e) {
      $module = $this->configuration['provider'];
      // Ignore quick forms belonging to uninstalled modules, but re-throw valid
      // exceptions when the module is installed and the plugin is
      // misconfigured.
      if (!$module || $this->moduleHandler->moduleExists($module)) {
        throw $e;
      }
    }
  }

}
