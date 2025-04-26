<?php

declare(strict_types=1);

namespace Drupal\farm_equipment\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the equipment asset type.
 */
#[AssetType(
  id: 'equipment',
  label: new TranslatableMarkup('Equipment'),
)]
class Equipment extends FarmAssetType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = parent::buildFieldDefinitions();
    $field_info = [
      'equipment_type' => [
        'type' => 'entity_reference',
        'label' => $this->t('Equipment type'),
        'description' => $this->t("Enter the type of equipment."),
        'target_type' => 'taxonomy_term',
        'target_bundle' => 'equipment_type',
        'auto_create' => TRUE,
        'multiple' => TRUE,
        'weight' => [
          'form' => -90,
          'view' => -50,
        ],
      ],
      'manufacturer' => [
        'type' => 'string',
        'label' => $this->t('Manufacturer'),
        'weight' => [
          'form' => -20,
          'view' => -50,
        ],
      ],
      'model' => [
        'type' => 'string',
        'label' => $this->t('Model'),
        'weight' => [
          'form' => -15,
          'view' => -40,
        ],
      ],
      'serial_number' => [
        'type' => 'string',
        'label' => $this->t('Serial number'),
        'weight' => [
          'form' => -10,
          'view' => -30,
        ],
      ],
    ];
    foreach ($field_info as $name => $info) {
      $fields[$name] = $this->farmFieldFactory->bundleFieldDefinition($info);
    }
    return $fields;
  }

}
