<?php

declare(strict_types=1);

namespace Drupal\farm_map\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the MapType config entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'map_type',
  label: new TranslatableMarkup('Map type'),
  label_collection: new TranslatableMarkup('Map types'),
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  handlers: [],
  admin_permission: 'administer farm map',
  config_export: [
    'id',
    'label',
    'description',
    'behaviors',
    'options',
  ],
)]
class MapType extends ConfigEntityBase implements MapTypeInterface {

  /**
   * The map type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The map type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this map type.
   *
   * @var string
   */
  protected $description;

  /**
   * Behaviors to add to the map.
   *
   * @var string[]
   */
  protected $behaviors;

  /**
   * The options to pass to farmOS-map.
   *
   * @var mixed|null
   */
  protected $options;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    return $this->set('description', $description);
  }

  /**
   * {@inheritdoc}
   */
  public function getMapBehaviors() {
    return empty($this->behaviors) ? [] : $this->behaviors;
  }

  /**
   * {@inheritdoc}
   */
  public function getMapOptions() {
    return $this->options;
  }

}
