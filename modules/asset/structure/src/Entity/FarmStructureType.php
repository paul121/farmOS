<?php

declare(strict_types=1);

namespace Drupal\farm_structure\Entity;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the FarmStructureType entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'structure_type',
  label: new TranslatableMarkup('Structure type'),
  label_collection: new TranslatableMarkup('Structure types'),
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
  ],
)]
class FarmStructureType extends ConfigEntityBase implements FarmStructureTypeInterface {

  /**
   * The structure type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The structure type label.
   *
   * @var string
   */
  protected $label;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

}
