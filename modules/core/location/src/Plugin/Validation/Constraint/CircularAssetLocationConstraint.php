<?php

declare(strict_types=1);

namespace Drupal\farm_location\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks that a log is not creating a circular asset location.
 */
#[Constraint(
  id: 'CircularAssetLocation',
  label: new TranslatableMarkup('Circular asset location', ['context' => 'Validation']),
)]
class CircularAssetLocationConstraint extends SymfonyConstraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public string $message = '%asset cannot be located within itself.';

}
