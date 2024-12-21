<?php

declare(strict_types=1);

namespace Drupal\farm_id_tag\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the FarmIDTagType entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'tag_type',
  label: new TranslatableMarkup('ID tag type'),
  label_collection: new TranslatableMarkup('ID tag types'),
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
    'bundles',
  ],
)]
class FarmIDTagType extends ConfigEntityBase implements FarmIDTagTypeInterface {

  /**
   * The ID tag type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The ID tag type label.
   *
   * @var string
   */
  protected $label;

  /**
   * The bundles that this tag type applies to.
   *
   * @var array
   */
  protected $bundles;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundles() {
    return $this->bundles;
  }

}
