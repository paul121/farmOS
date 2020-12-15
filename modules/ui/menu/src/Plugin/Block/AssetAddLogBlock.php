<?php

namespace Drupal\farm_ui_menu\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Url;

/**
 * Provides a dropbutton to create logs from an asset.
 *
 * @Block(
 *   id = "asset_add_log_block",
 *   admin_label = @Translation("Asset add log"),
 * )
 */
class AssetAddLogBlock extends BlockBase {


  public function build() {

    $button = [
      '#type' => 'dropbutton',
      '#dropbutton_type' => 'small',
      '#links' => [],
    ];

    $button['#links'][] = [
      'title' => $this->t('Add log'),
      'url' => Url::fromRoute('entity.log.add_page'),
    ];

    // Add links for each log type.
    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('log');
    foreach ($bundles as $type => $info) {

      $route_match = RouteMatch::createFromRequest(\Drupal::request());
      $asset = $route_match->getParameter('asset');

      $button['#links'][] = [
        'title' => $this->t('Add') . ' ' . $info['label'],
        'url' => Url::fromRoute(
          'entity.log.add_form',
          ['log_type' => $type],
          ['query' => ['edit[asset][widget][0][target_id]' => $asset->id()]]
        ),
//        'attributes' => [
//          'class' => ['local-actions__item']
//        ]
      ];
//      $links['farm.asset.add_log.' . $type] = [
//        'title' => 'Add ' . $info['label'],
//        'route_name' => 'entity.log.add_form',
//        'class' => '\Drupal\farm_ui_menu\Plugin\Menu\LocalAction\FarmAddLogPrepopulate',
//        'appears_on' => [
//          'entity.asset.canonical',
//        ],
//        'route_parameters' => ['log_type' => $type],
//        'cache_tags' => [
//          'entity_bundles',
//        ],
//        'prepopulate' => [
//          'asset' => [
//            'route_parameter' => 'asset',
//          ],
//        ],
//      ] + $base_plugin_definition;

    }

    return $button;

  }

}
