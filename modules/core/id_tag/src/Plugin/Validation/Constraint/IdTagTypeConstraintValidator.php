<?php

declare(strict_types=1);

namespace Drupal\farm_id_tag\Plugin\Validation\Constraint;

use Drupal\farm_id_tag\Plugin\Field\FieldType\IdTagItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates the IdTagTypeConstraint constraint.
 */
class IdTagTypeConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    $id_tag = $value->first();
    if (!$id_tag instanceof IdTagItem || empty($id_tag->type)) {
      return;
    }
    $bundle = $value->getEntity()->bundle();
    $valid_types = array_keys(farm_id_tag_type_options($bundle));
    if (!in_array($id_tag->type, $valid_types)) {
      // @phpstan-ignore property.notFound
      $this->context->addViolation($constraint->message, ['@type' => $id_tag->type]);
    }
  }

}
