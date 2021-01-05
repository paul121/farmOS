<?php

namespace Drupal\farm_ui_action\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a dropbutton to create logs from an asset view.
 *
 * @Block(
 *   id = "asset_add_log_block",
 *   admin_label = @Translation("Asset add log"),
 * )
 */
class AssetAddLogBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Log type bundle info.
   *
   * @var array
   */
  private $logTypes;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  private $routeMatch;

  /**
   * Constructs an AssetAddLogBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeBundleInfoInterface $entity_type_bundle_info, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logTypes = $entity_type_bundle_info->getBundleInfo('log');
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.bundle.info'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Create the dropbutton.
    $button = [
      '#type' => 'dropbutton',
      '#dropbutton_type' => 'standard',
      '#links' => [],
    ];

    // Add a link to the log.add_page route.
    $button['#links'][] = [
      'title' => $this->t('Add log'),
      'url' => Url::fromRoute('entity.log.add_page'),
    ];

    // Add links to add each log type.
    foreach ($this->logTypes as $type => $info) {

      // Get the current asset.
      $asset = $this->routeMatch->getParameter('asset');

      // Add a link to add the log type.
      $button['#links'][] = [
        'title' => $this->t('Add @log', ['@log' => $info['label']]),
        'url' => Url::fromRoute(
          'entity.log.add_form',
          ['log_type' => $type],
          ['query' => ['edit[asset][widget][0][target_id]' => $asset->id()]]
        ),
      ];
    }

    return $button;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Rebuild the block when new log types are installed.
    return Cache::mergeTags(parent::getCacheTags(), ['entity_bundles']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Rebuild the block for each route. This is necessary so that links
    // prepopulate the correct asset.
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
