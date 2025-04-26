<?php

declare(strict_types=1);

namespace Drupal\data_stream_notification;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\data_stream_notification\Attribute\NotificationDelivery;
use Drupal\data_stream_notification\Plugin\DataStream\NotificationDelivery\NotificationDeliveryInterface;

/**
 * Notification Delivery plugin manager.
 */
class NotificationDeliveryManager extends DefaultPluginManager implements NotificationDeliveryManagerInterface {

  /**
   * Constructs a NotificationDeliveryManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/DataStream/NotificationDelivery',
      $namespaces,
      $module_handler,
      NotificationDeliveryInterface::class,
      NotificationDelivery::class,
      'Drupal\data_stream_notification\Annotation\NotificationDelivery',
    );
    $this->alterInfo('data_stream_notification_delivery_info');
    $this->setCacheBackend($cache_backend, 'data_stream_notification_delivery');
  }

}
