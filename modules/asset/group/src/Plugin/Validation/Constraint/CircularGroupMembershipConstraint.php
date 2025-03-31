<?php

declare(strict_types=1);

namespace Drupal\farm_group\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks that a log is not creating a circular group membership.
 */
#[Constraint(
  id: 'CircularGroupMembership',
  label: new TranslatableMarkup('Circular group membership', ['context' => 'Validation']),
)]
class CircularGroupMembershipConstraint extends SymfonyConstraint {

  /**
   * The default violation message.
   *
   * @var string
   */
  public string $message = '%asset cannot be a member of itself.';

}
