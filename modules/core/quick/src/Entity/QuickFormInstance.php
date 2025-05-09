<?php

declare(strict_types=1);

namespace Drupal\farm_quick\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_quick\QuickFormPluginCollection;

/**
 * Defines the quick form instance config entity.
 */
#[ConfigEntityType(
  id: 'quick_form',
  label: new TranslatableMarkup('Quick form'),
  label_collection: new TranslatableMarkup('Quick forms'),
  label_singular: new TranslatableMarkup('quick form'),
  label_plural: new TranslatableMarkup('quick forms'),
  entity_keys: [
    'id' => 'id',
    'status' => 'status',
    'label' => 'label',
  ],
  handlers: [
    'access' => '\Drupal\entity\EntityAccessControlHandler',
    'permission_provider' => '\Drupal\entity\EntityPermissionProvider',
    'list_builder' => 'Drupal\farm_quick\QuickFormListBuilder',
    'form' => [
      'add' => 'Drupal\farm_quick\Form\QuickFormEntityForm',
      'edit' => 'Drupal\farm_quick\Form\QuickFormEntityForm',
      'configure' => 'Drupal\farm_quick\Form\ConfigureQuickForm',
      'delete' => '\Drupal\Core\Entity\EntityDeleteForm',
    ],
    'route_provider' => [
      'default' => 'Drupal\entity\Routing\DefaultHtmlRouteProvider',
    ],
  ],
  links: [
    'edit-form' => '/setup/quick/{quick_form}/edit',
    'delete-form' => '/setup/quick/{quick_form}/delete',
    'collection' => '/setup/quick',
  ],
  admin_permission: 'administer quick_form',
  label_count: [
    'singular' => '@count quick form',
    'plural' => '@count quick forms',
  ],
  config_export: [
    'id',
    'plugin',
    'label',
    'description',
    'helpText',
    'settings',
  ],
)]
class QuickFormInstance extends ConfigEntityBase implements QuickFormInstanceInterface, EntityWithPluginCollectionInterface {

  /**
   * The ID of the quick form instance.
   *
   * @var string
   */
  protected $id;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin collection that holds the quick form plugin for this entity.
   *
   * @var \Drupal\farm_quick\QuickFormPluginCollection
   */
  protected $pluginCollection;

  /**
   * The quick form label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of the quick form.
   *
   * @var string
   */
  protected $description;

  /**
   * Help text for the quick form.
   *
   * @var string
   */
  protected $helpText;

  /**
   * The plugin instance settings.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * Encapsulates the creation of the farm_quick's plugin collection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The block's plugin collection.
   */
  protected function getPluginCollection() {
    // PHPStan level 3+ throws the following error on the next line:
    // Negated boolean expression is always false.
    // We ignore this because we are following Drupal core's pattern.
    // @phpstan-ignore booleanNot.alwaysFalse
    if (!$this->pluginCollection) {
      $this->pluginCollection = new QuickFormPluginCollection(\Drupal::service('plugin.manager.quick_form'), $this->plugin, $this->get('settings'), $this->id());
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'settings' => $this->getPluginCollection(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return (string) $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return (string) $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function getHelpText() {
    return (string) $this->helpText;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage, array &$values) {
    parent::preCreate($storage, $values);

    /** @var \Drupal\farm_quick\QuickFormPluginManager $quick_form_plugin_manager */
    $quick_form_plugin_manager = \Drupal::service('plugin.manager.quick_form');

    // If the plugin is set use the default label, description and helpText.
    if (isset($values['plugin']) && $plugin = $quick_form_plugin_manager->getDefinition($values['plugin'], FALSE)) {
      foreach (['label', 'description', 'helpText'] as $field) {
        if (!isset($values[$field])) {
          $values[$field] = $plugin[$field];
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    \Drupal::service('router.builder')->setRebuildNeeded();
  }

}
