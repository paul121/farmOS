<?php

declare(strict_types=1);

namespace Drupal\farm_quick_test\Plugin\QuickForm;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_quick\Attribute\QuickForm;

/**
 * Test quick form that requires a configuration entity.
 */
#[QuickForm(
  id: 'requires_entity_test',
  label: new TranslatableMarkup('Test requiresEntity quick form'),
  description: new TranslatableMarkup('Test requiresEntity quick form description.'),
  helpText: new TranslatableMarkup('Test requiresEntity quick form help text.'),
  permissions: [
    'create test log',
  ],
  requiresEntity: TRUE,
)]
class RequiresEntityTest extends Test {

}
