<?php

declare(strict_types=1);

namespace Drupal\farm_ui_location\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\asset\Entity\AssetInterface;
use Drupal\farm_location\AssetLocationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for changing the hierarchy of all location assets.
 *
 * @ingroup farm
 */
class LocationHierarchyForm extends BaseLocationHierarchyForm {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The asset location service.
   *
   * @var \Drupal\farm_location\AssetLocationInterface
   */
  protected $assetLocation;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AssetLocationInterface $asset_location) {
    parent::__construct($entity_type_manager, $asset_location);
    $this->entityTypeManager = $entity_type_manager;
    $this->assetLocation = $asset_location;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('asset.location'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_ui_location_location_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?AssetInterface $asset = NULL) {

    // Show a map of all locations.
    $form['map'] = [
      '#type' => 'farm_map',
      '#map_type' => 'locations',
    ];

    // Build location form.
    return parent::buildLocationForm(
      $form,
      (string) $this->t('All locations'),
      Url::fromRoute('farm.locations'),
      $this->buildTree(),
    );
  }

}
