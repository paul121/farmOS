<?php

namespace Drupal\farm_group\Plugin\views\argument_validator;

use Drupal\views\Plugin\views\argument_validator\Entity;

/**
 * Validates asset IDs and includes asset groups in the contextual filter.
 *
 * Most of this logic is copied from the Entity argument validator.
 *
 * @see Drupal\views\Plugin\views\argument_validator\Entity
 *
 * @ViewsArgumentValidator(
 *   id = "asset_and_group",
 *   title = @Translation("Asset and group"),
 *   entity_type = "asset"
 * )
 */
class AssetAndGroup extends Entity {

  /**
   * {@inheritdoc}
   */
  public function validateArgument($argument) {
    $entity_type = $this->definition['entity_type'];

    if ($this->multipleCapable && $this->options['multiple']) {
      // At this point only interested in individual IDs no matter what type,
      // just splitting by the allowed delimiters.
      $ids = array_filter(preg_split('/[,+ ]/', $argument));
    }
    elseif ($argument) {
      $ids = [$argument];
    }
    // No specified argument should be invalid.
    else {
      return FALSE;
    }

    // Start an array of group ids.
    $group_ids = [];

    $entities = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($ids);
    // Validate each id => entity. If any fails break out and return false.
    foreach ($ids as $id) {
      // There is no entity for this ID.
      if (!isset($entities[$id])) {
        return FALSE;
      }
      if (!$this->validateEntity($entities[$id])) {
        return FALSE;
      }

      if (!empty($entities[$id]->get('group'))) {
        foreach ($entities[$id]->get('group')->getValue() as $group) {
          $group_ids[] = $group['target_id'];
        }
      }
    }

    // Include the group asset IDs in the contextual filter.
    $all_asset_ids = array_unique(array_merge($ids, $group_ids));
    $this->argument->argument = implode(',', $all_asset_ids);
    return TRUE;
  }

}
