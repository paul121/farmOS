<?php

declare(strict_types=1);

namespace Drupal\farm_map\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the LayerStyle config entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'layer_style',
  label: new TranslatableMarkup('Layer style'),
  label_collection: new TranslatableMarkup('Layer styles'),
  handlers: [],
  admin_permission: 'administer farm map',
  entity_keys: [
    'id' => 'id',
  ],
  config_export: [
    'id',
    'color',
    'conditions',
  ],
)]
class LayerStyle extends ConfigEntityBase implements LayerStyleInterface {

  /**
   * The layer style ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The layer style conditions.
   *
   * @var mixed|null
   */
  protected $conditions;

  /**
   * {@inheritdoc}
   */
  public function getConditions() {
    return $this->conditions;
  }

}
