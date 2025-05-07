<?php

declare(strict_types=1);

namespace Drupal\farm_ui_views\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides task links for farmOS Organization Views.
 */
class FarmOrganizationViewsTaskLink extends DeriverBase implements ContainerDeriverInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a FarmActions object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];
    $organization_entity = $this->entityTypeManager->getDefinition('organization', FALSE);
    if (!$organization_entity) {
      return $links;
    }

    // Add primary tab for assets.
    $asset_entity = $this->entityTypeManager->getDefinition('asset');
    $links['assets'] = [
      'title' => $asset_entity->getCollectionLabel(),
      'route_name' => 'view.farm_organization_asset.page',
      'base_route' => 'entity.organization.canonical',
      'weight' => 50,
    ] + $base_plugin_definition;

    // Build the parent ID from the base ID.
    $base_id = $base_plugin_definition['id'];
    $parent_id = "$base_id:assets";

    // Add default "All" secondary tab.
    $links['all_assets'] = [
      'title' => $this->t('All'),
      'parent_id' => $parent_id,
      'route_name' => 'view.farm_organization_asset.page',
      'route_parameters' => [
        'asset_type' => 'all',
      ],
    ] + $base_plugin_definition;

    // Add secondary tab for each bundle.
    $bundles = $this->entityTypeManager->getStorage('asset_type')->loadMultiple();
    foreach ($bundles as $type => $bundle) {
      $links["asset_$type"] = [
        'title' => $bundle->label(),
        'parent_id' => $parent_id,
        'route_name' => 'view.farm_organization_asset.page_type',
        'route_parameters' => [
          'asset_type' => $type,
        ],
      ];
    }

    // Add primary tab for logs.
    $log_entity = $this->entityTypeManager->getDefinition('log');
    $links['logs'] = [
      'title' => $log_entity->getCollectionLabel(),
      'route_name' => 'view.farm_organization_log.page',
      'base_route' => 'entity.organization.canonical',
      'weight' => 60,
    ] + $base_plugin_definition;

    // Build the parent ID from the base ID.
    $base_id = $base_plugin_definition['id'];
    $parent_id = "$base_id:logs";

    // Add default "All" secondary tab.
    $links['all_logs'] = [
      'title' => $this->t('All'),
      'parent_id' => $parent_id,
      'route_name' => 'view.farm_organization_log.page',
      'route_parameters' => [
        'log_type' => 'all',
      ],
    ] + $base_plugin_definition;

    // Add secondary tab for each bundle.
    $bundles = $this->entityTypeManager->getStorage('log_type')->loadMultiple();
    foreach ($bundles as $type => $bundle) {
      $links["log_$type"] = [
        'title' => $bundle->label(),
        'parent_id' => $parent_id,
        'route_name' => 'view.farm_organization_log.page_type',
        'route_parameters' => [
          'log_type' => $type,
        ],
      ];
    }

    return $links;
  }

}
