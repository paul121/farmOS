<?php

declare(strict_types=1);

namespace Drupal\farm_lab_test\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the FarmLabTestType entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'lab_test_type',
  label: new TranslatableMarkup('Lab test type'),
  label_collection: new TranslatableMarkup('Lab test type'),
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  handlers: [],
  admin_permission: 'administer site configuration',
  config_export: [
    'id',
    'label',
  ],
)]
class FarmLabTestType extends ConfigEntityBase implements FarmLabTestTypeInterface {

  /**
   * The lab test type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The lab test type label.
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
