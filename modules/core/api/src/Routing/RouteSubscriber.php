<?php

namespace Drupal\farm_api\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\farm_api\Controller\FarmEntryPoint;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('jsonapi.resource_list')) {
      $route->setDefaults([RouteObjectInterface::CONTROLLER_NAME => FarmEntryPoint::class . '::index']);
    }
  }
}
