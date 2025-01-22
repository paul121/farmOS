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
    /** @var \Drupal\Core\Field\FieldItemListInterface $value */

    // Bail if it is an empty field.
    if ($value->isEmpty()) {
      return;
    }

    // Get valid tag types for the asset bundle.
    $bundle = $value->getEntity()->bundle();
    $valid_types = array_keys(farm_id_tag_type_options($bundle));

    // Check for a valid ID tag type on each field delta.
    foreach ($value as $id_tag) {
      if (!$id_tag instanceof IdTagItem || empty($id_tag->type)) {
        continue;
      }
      if (!in_array($id_tag->type, $valid_types)) {
        // @phpstan-ignore property.notFound
        $this->context->addViolation($constraint->message, ['@type' => $id_tag->type]);
      }
    }
  }

}
