<?php

declare(strict_types=1);

namespace Drupal\farm_land\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the land asset type.
 */
#[AssetType(
  id: 'land',
  label: new TranslatableMarkup('Land'),
)]
class Land extends FarmAssetType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];

    // Land type field.
    $options = [
      'type' => 'list_string',
      'label' => $this->t('Land type'),
      'allowed_values_function' => 'farm_land_type_field_allowed_values',
      'required' => TRUE,
      'weight' => [
        'form' => -80,
        'view' => -80,
      ],
    ];
    $fields['land_type'] = $this->farmFieldFactory->bundleFieldDefinition($options);

    return $fields;
  }

}
