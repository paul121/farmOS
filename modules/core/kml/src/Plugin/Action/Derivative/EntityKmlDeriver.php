<?php

namespace Drupal\farm_kml\Plugin\Action\Derivative;

use Drupal\Core\Action\Plugin\Action\Derivative\EntityActionDeriverBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides an action deriver for the KML action.
 *
 * @see \Drupal\farm_kml\Plugin\Action\EntityKml
 */
class EntityKmlDeriver extends EntityActionDeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    if (empty($this->derivatives)) {
      $definitions = [];
      foreach ($this->getApplicableEntityTypes() as $entity_type_id => $entity_type) {
        $definition = $base_plugin_definition;
        $definition['type'] = $entity_type_id;
        $definition['label'] = $this->t('Export @entity_type KML', ['@entity_type' => $entity_type->getSingularLabel()]);
        $definitions[$entity_type_id] = $definition;
      }
      $this->derivatives = $definitions;
    }

    return parent::getDerivativeDefinitions($base_plugin_definition);

  }

  /**
   * {@inheritdoc}
   */
  protected function isApplicable(EntityTypeInterface $entity_type) {
    return in_array($entity_type->id(), ['log', 'asset']);
  }

}
