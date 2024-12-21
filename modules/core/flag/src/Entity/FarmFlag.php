<?php

declare(strict_types=1);

namespace Drupal\farm_flag\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the FarmFlag entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'flag',
  label: new TranslatableMarkup('Flag'),
  label_collection: new TranslatableMarkup('Flags'),
  handlers: [
    'access' => '\Drupal\entity\EntityAccessControlHandler',
    'permission_provider' => '\Drupal\entity\EntityPermissionProvider',
  ],
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  config_export: [
    'id',
    'label',
    'entity_types',
  ],
)]
class FarmFlag extends ConfigEntityBase implements FarmFlagInterface {

  /**
   * The flag ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The flag label.
   *
   * @var string
   */
  protected $label;

  /**
   * The entity types and bundles that this flag applies to.
   *
   * @var array
   */
  protected $entity_types;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntitytypes() {
    return $this->entity_types;
  }

}
