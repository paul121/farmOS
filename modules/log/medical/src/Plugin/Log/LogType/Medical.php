<?php

declare(strict_types=1);

namespace Drupal\farm_medical\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the medical log type.
 */
#[LogType(
  id: 'medical',
  label: new TranslatableMarkup('Medical'),
)]
class Medical extends FarmLogType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = parent::buildFieldDefinitions();

    // Veterinarian.
    $options = [
      'type' => 'string',
      'label' => $this->t('Veterinarian'),
      'description' => $this->t('If a veterinarian was involved, enter their name here.'),
      'weight' => [
        'form' => -40,
        'view' => -40,
      ],
    ];
    $fields['vet'] = $this->farmFieldFactory->bundleFieldDefinition($options);

    return $fields;
  }

}
