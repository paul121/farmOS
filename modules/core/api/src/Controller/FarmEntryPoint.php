<?php

namespace Drupal\farm_api\Controller;

use Drupal\jsonapi\Controller\EntryPoint;
use Drupal\jsonapi\JsonApiResource\JsonApiDocumentTopLevel;
use Drupal\jsonapi\JsonApiResource\NullIncludedData;
use Drupal\jsonapi\JsonApiResource\ResourceObjectData;
use Drupal\jsonapi\ResourceResponse;

/**
 *
 */
class FarmEntryPoint extends EntryPoint {

  /**
   * {@inheritdoc}
   */
  public function index() {
    // Get normal response data.
    $response = parent::index();
    $data = $response->getResponseData();

    // Get urls and meta.
    $urls = $data->getLinks();
    $meta = $data->getMeta();

    // Add a "farm" object to meta.
    $profile_info = \Drupal::service('extension.list.profile')->getExtensionInfo('farm');
    $site_info = \Drupal::config('system.site');
    $meta['farm'] = [
      'version' => $profile_info['version'],
      'name' => $site_info->get('name'),
      'system_of_measurement' => 'metric',
      'google_maps_key' => 'load from config',
    ];

    // Return a new response.
    $new_response = new ResourceResponse( new JsonApiDocumentTopLevel( new ResourceObjectData([]), new NullIncludedData(), $urls, $meta));
    return $new_response;
  }
}
