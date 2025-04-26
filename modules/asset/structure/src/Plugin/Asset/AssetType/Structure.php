<?php

declare(strict_types=1);

namespace Drupal\farm_structure\Plugin\Asset\AssetType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\AssetType;
use Drupal\farm_entity\Plugin\Asset\AssetType\FarmAssetType;

/**
 * Provides the structure asset type.
 */
#[AssetType(
  id: 'structure',
  label: new TranslatableMarkup('Structure'),
)]
class Structure extends FarmAssetType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = [];

    // Structure type field.
    $options = [
      'type' => 'list_string',
      'label' => $this->t('Structure type'),
      'allowed_values_function' => 'farm_structure_type_field_allowed_values',
      'required' => TRUE,
      'weight' => [
        'form' => -80,
        'view' => -80,
      ],
    ];
    $fields['structure_type'] = $this->farmFieldFactory->bundleFieldDefinition($options);

    return $fields;
  }

}
