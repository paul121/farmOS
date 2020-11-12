<?php

namespace Drupal\farm_quantity\Plugin\migrate\process;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create farm_quantity entities.
 *
 * This is an alternative to using the entity_generate process plugin which
 * requires a "lookup" to happen before creating the entity. Since the quantity
 * value field is a Fraction field, it is easier to use our own process plugin.
 *
 * @MigrateProcessPlugin(
 *   id = "create_quantity"
 * )
 */
class CreateQuantity extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Farm quantity entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $quantityStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
    $instance->quantityStorage = $container->get('entity_type.manager')->getStorage('farm_quantity');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // TODO: Do the quantity UID, created/changed timestamps need to be
    // inherited from the log?
    $values = [
      'measure' => $row->getSourceProperty('measure'),
      'value' => [
        'numerator' => $row->getSourceProperty('value_numerator'),
        'denominator' => $row->getSourceProperty('value_denominator'),
      ],
      'units' => $row->getSourceProperty('units'),
      'label' => $row->getSourceProperty('label'),
    ];

    // Create the entity.
    $entity = $this->quantityStorage->create($values);

    // Save the entity so it has an ID.
    $entity->save();

    // Return the ID.
    return $entity->id();
  }

}
