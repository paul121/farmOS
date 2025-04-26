<?php

declare(strict_types=1);

namespace Drupal\farm_quantity_test\Plugin\Quantity\QuantityType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\QuantityType;
use Drupal\farm_entity\Plugin\Quantity\QuantityType\FarmQuantityType;

/**
 * Provides the test quantity type.
 */
#[QuantityType(
  id: 'test',
  label: new TranslatableMarkup('Test'),
)]
class Test extends FarmQuantityType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {

    // Inherit default quantity fields.
    $fields = parent::buildFieldDefinitions();

    // Test method.
    $options = [
      'type' => 'entity_reference',
      'label' => $this->t('Test method'),
      'description' => $this->t('What testing method was used to obtain this measurement?'),
      'target_type' => 'taxonomy_term',
      'target_bundle' => 'test_method',
      'auto_create' => TRUE,
      'weight' => [
        'form' => 25,
        'view' => 25,
      ],
    ];
    $fields['test_method'] = $this->farmFieldFactory->bundleFieldDefinition($options);

    return $fields;
  }

}
