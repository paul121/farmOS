<?php

declare(strict_types=1);

namespace Drupal\farm_land\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the FarmLandType entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'land_type',
  label: new TranslatableMarkup('Land type'),
  label_collection: new TranslatableMarkup('Land types'),
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  handlers: [
    'access' => '\Drupal\entity\EntityAccessControlHandler',
    'permission_provider' => '\Drupal\entity\EntityPermissionProvider',
  ],
  config_export: [
    'id',
    'label',
  ],
)]
class FarmLandType extends ConfigEntityBase implements FarmLandTypeInterface {

  /**
   * The land type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The land type label.
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
