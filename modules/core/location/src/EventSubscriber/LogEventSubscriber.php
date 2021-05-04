<?php

namespace Drupal\farm_location\EventSubscriber;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\farm_location\AssetLocationInterface;
use Drupal\farm_location\LogLocationInterface;
use Drupal\farm_location\Traits\WktTrait;
use Drupal\farm_log\Event\LogEvent;
use Drupal\log\Entity\LogInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Perform actions on log presave.
 */
class LogEventSubscriber implements EventSubscriberInterface {

  use WktTrait;

  /**
   * Log location service.
   *
   * @var \Drupal\farm_location\LogLocationInterface
   */
  protected LogLocationInterface $logLocation;

  /**
   * Asset location service.
   *
   * @var \Drupal\farm_location\AssetLocationInterface
   */
  protected AssetLocationInterface $assetLocation;

  /**
   * Cache tag invalidator service.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected CacheTagsInvalidatorInterface $cacheTagsInvalidator;

  /**
   * LogEventSubscriber Constructor.
   *
   * @param \Drupal\farm_location\LogLocationInterface $log_location
   *   Log location service.
   * @param \Drupal\farm_location\AssetLocationInterface $asset_locaiton
   *   Asset location service.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   Cache tag invalidator service.
   */
  public function __construct(LogLocationInterface $log_location, AssetLocationInterface $asset_locaiton, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->logLocation = $log_location;
    $this->assetLocation = $asset_locaiton;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   *   The event names to listen for, and the methods that should be executed.
   */
  public static function getSubscribedEvents() {
    return [
      LogEvent::DELETE => 'logDelete',
      LogEvent::PRESAVE => 'logPresave',
    ];
  }

  /**
   * Perform actions on log delete.
   *
   * @param \Drupal\farm_log\Event\LogEvent $event
   *   The log event.
   */
  public function logDelete(LogEvent $event) {
    $this->invalidateAssetCacheOnMovement($event->log);
  }

  /**
   * Perform actions on log presave.
   *
   * @param \Drupal\farm_log\Event\LogEvent $event
   *   The log event.
   */
  public function logPresave(LogEvent $event) {

    // Get the log entity from the event.
    $log = $event->log;

    // Populate the log geometry from the location geometry.
    $this->populateGeometryFromLocation($log);

    // Invalidate asset caches when assets are moved.
    $this->invalidateAssetCacheOnMovement($log);
  }

  /**
   * Populate a log's geometry based on its location.
   *
   * @param \Drupal\log\Entity\LogInterface $log
   *   The Log entity.
   */
  protected function populateGeometryFromLocation(LogInterface $log): void {

    // If the log does not reference any location assets, we will have nothing
    // to copy from, so do nothing.
    if (!$this->logLocation->hasLocation($log)) {
      return;
    }

    // If this is a new log and it has a geometry, do nothing.
    if (empty($log->original) && $this->logLocation->hasGeometry($log)) {
      return;
    }

    // If this is an update to an existing log...
    if (!empty($log->original)) {

      // If the original log has a custom geometry, do nothing.
      if ($this->hasCustomGeometry($log->original)) {
        return;
      }

      // If the geometry has changed (and it has not been emptied), do nothing.
      $old_geometry = $this->logLocation->getGeometry($log->original);
      $new_geometry = $this->logLocation->getGeometry($log);

      // If the new geometry is not empty, do nothing.
      if ($old_geometry != $new_geometry && !empty($new_geometry)) {
        return;
      }
    }

    // Load location assets referenced by the log.
    $assets = $this->logLocation->getLocation($log);

    // Get the combined location asset geometry.
    $wkt = $this->getCombinedAssetGeometry($assets);

    // If the WKT is not empty, set the log geometry.
    if (!empty($wkt)) {
      $this->logLocation->setGeometry($log, $wkt);
    }
  }

  /**
   * Check if a log has a custom geometry.
   *
   * This is determined by checking to see if the log's geometry matches that
   * of the location assets it references. If it does not, and it is not empty,
   * them we assume it has a custom geometry.
   *
   * @param \Drupal\log\Entity\LogInterface $log
   *   The Log entity.
   *
   * @return bool
   *   Returns TRUE if it matches, FALSE otherwise.
   */
  protected function hasCustomGeometry(LogInterface $log): bool {

    // If the log's geometry is empty, then it does not have a custom geometry.
    if (!$this->logLocation->hasGeometry($log)) {
      return FALSE;
    }

    // Load location assets referenced by the log.
    $assets = $this->logLocation->getLocation($log);

    // Get the combined location asset geometry.
    $location_geometry = $this->getCombinedAssetGeometry($assets);

    // Get the log geometry.
    $log_geometry = $this->logLocation->getGeometry($log);

    // Compare the log and location geometries.
    return $log_geometry != $location_geometry;
  }

  /**
   * Load a combined set of location asset geometries.
   *
   * @param \Drupal\asset\Entity\AssetInterface[] $assets
   *   An array of location assets.
   *
   * @return string
   *   Returns a WKT string of the combined asset geometries.
   */
  protected function getCombinedAssetGeometry(array $assets): string {

    // Collect all the location geometries.
    $geoms = [];
    foreach ($assets as $asset) {
      if ($this->assetLocation->hasGeometry($asset)) {
        $geoms[] = $this->assetLocation->getGeometry($asset);
      }
    }

    // Combine the geometries into a single WKT string.
    $wkt = $this->combineWkt($geoms);

    return $wkt;
  }

  /**
   * Invalidate asset caches when assets are moved.
   *
   * @param \Drupal\log\Entity\LogInterface $log
   *   The Log entity.
   */
  protected function invalidateAssetCacheOnMovement(LogInterface $log): void {

    // Keep track if we need to invalidate the cache of referenced assets so
    // the computed 'location' and 'geometry' fields are updated.
    $update_asset_cache = FALSE;

    // If the log is a 'done' movement log, invalidate the cache.
    if ($log->get('status')->value == 'done' && $log->get('is_movement')->value) {
      $update_asset_cache = TRUE;
    }

    // If updating an existing 'done' movement log, invalidate the cache.
    // This catches any movement logs changing from done to pending.
    if (!empty($log->original) && $log->original->get('status')->value == 'done' && $log->original->get('is_movement')->value) {
      $update_asset_cache = TRUE;
    }

    // If an update is not necessary, bail.
    if (!$update_asset_cache) {
      return;
    }

    // Build a list of cache tags.
    // @todo Only invalidate cache if the movement log changes the asset's current location. This might be different for each asset.
    $tags = [];

    // Include assets that were previously referenced.
    if (!empty($log->original)) {
      foreach ($log->original->get('asset')->referencedEntities() as $asset) {
        array_push($tags, ...$asset->getCacheTags());
      }
    }

    // Include assets currently referenced by the log.
    foreach ($log->get('asset')->referencedEntities() as $asset) {
      array_push($tags, ...$asset->getCacheTags());
    }

    // Invalidate the cache tags.
    $this->cacheTagsInvalidator->invalidateTags($tags);
  }

}
