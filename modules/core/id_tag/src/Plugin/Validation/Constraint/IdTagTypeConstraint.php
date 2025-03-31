<?php

declare(strict_types=1);

namespace Drupal\farm_id_tag\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks that ID tag type is valid.
 */
#[Constraint(
  id: 'IdTagType',
  label: new TranslatableMarkup('Valid ID tag type', ['context' => 'Validation']),
)]
class IdTagTypeConstraint extends SymfonyConstraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public string $message = 'Invalid ID tag type: @type';

}
