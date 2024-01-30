<?php

namespace Drupal\farm_l10n\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Configuration overrides for farmOS localization module.
 */
class FarmLocalizationOverrides implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (in_array('system.site', $names)) {
      $overrides['system.site']['default_langcode'] = 'en';
    }

    // @todo move to farm_quick module.
    $quick_actions = [];
    foreach ($names as $name) {

      // Find system actions that end with quick_*.
      if (preg_match("/system\.action\.quick_(.*)/", $name, $matches)) {
        $quick_actions[$name] = $matches[1];
      }
    }

    // Find quick forms that are disabled and used as actions.
    if (count($quick_actions)) {
      $disabled_quick_forms = \Drupal::entityTypeManager()->getStorage('quick_form')->getQuery()
        ->condition('id', array_values($quick_actions), 'IN')
        ->condition('status', FALSE)
        ->execute();

      // Override config for these quick forms.
      foreach ($disabled_quick_forms as $quick_form_id) {
        // @todo this does override the label, but cannot seem to override the status. Hmmm.
        $overrides["system.action.quick_$quick_form_id"]['status'] = 0;
        $overrides["system.action.quick_$quick_form_id"]['label'] = 'disabled';
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'FarmLocalizationOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    // @todo cache system quick actions with the config entity cache tags.
    if (str_starts_with($name, 'system.action.quick_')) {

    }
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
