<?php

/**
 * @file
 * Post update hooks for the farm_entity module.
 */

declare(strict_types=1);

/**
 * Enforce entity reference integrity on plan reference fields.
 */
function farm_entity_post_update_enforce_plan_eri(&$sandbox) {
  $config = \Drupal::configFactory()->getEditable('entity_reference_integrity_enforce.settings');
  $entity_types = $config->get('enabled_entity_type_ids');
  $entity_types['plan'] = 'plan';
  $config->set('enabled_entity_type_ids', $entity_types);
  $config->save();
}

/**
 * Rebuild bundle field maps.
 */
function farm_entity_post_update_rebuild_bundle_field_maps(&$sandbox = NULL) {
  \Drupal::service('entity_field.manager')->rebuildBundleFieldMap();
}

/**
 * Uninstall EXIF Orientation module.
 */
function farm_entity_post_update_uninstall_exif_orientation() {
  if (\Drupal::service('module_handler')->moduleExists('exif_orientation')) {
    $modules = \Drupal::service('extension.list.module')->reset()->getList();
    if (empty($modules['exif_orientation']->required_by)) {
      \Drupal::service('module_installer')->uninstall(['exif_orientation']);
    }
  }
}

/**
 * Enforce entity reference integrity on organization reference fields.
 */
function farm_entity_post_update_enforce_organization_eri(&$sandbox) {
  $config = \Drupal::configFactory()->getEditable('entity_reference_integrity_enforce.settings');
  $entity_types = $config->get('enabled_entity_type_ids');
  $entity_types['organization'] = 'organization';
  $config->set('enabled_entity_type_ids', $entity_types);
  $config->save();
}
