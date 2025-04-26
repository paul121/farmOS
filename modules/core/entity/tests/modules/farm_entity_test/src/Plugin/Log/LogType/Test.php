<?php

declare(strict_types=1);

namespace Drupal\farm_entity_test\Plugin\Log\LogType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_entity\Attribute\LogType;
use Drupal\farm_entity\Plugin\Log\LogType\FarmLogType;

/**
 * Provides the test log type.
 */
#[LogType(
  id: 'test',
  label: new TranslatableMarkup('Test'),
)]
class Test extends FarmLogType {

  /**
   * {@inheritdoc}
   */
  public function buildFieldDefinitions() {
    $fields = parent::buildFieldDefinitions();

    // Add a test field to all Log bundles.
    $options = [
      'type' => 'string',
      'label' => $this->t('Test default bundle field'),
    ];
    $fields['test_default_bundle_field'] = $this->farmFieldFactory->bundleFieldDefinition($options);

    return $fields;
  }

}
