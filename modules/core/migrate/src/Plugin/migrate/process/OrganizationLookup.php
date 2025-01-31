<?php

declare(strict_types=1);

namespace Drupal\farm_migrate\Plugin\migrate\process;

use Drupal\Component\Uuid\Uuid;
use Drupal\migrate\Attribute\MigrateProcess;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\MigrateSkipRowException;
use Drupal\migrate\Row;
use Drupal\migrate_plus\Plugin\migrate\process\EntityLookup;

/**
 * This plugin looks for existing organization entities.
 *
 * Lookups are performed on multiple fields to find the organization, in the
 * following order:
 *
 * - UUID
 * - Name
 * - ID (primary key)
 *
 * @codingStandardsIgnoreStart
 *
 * Example usage:
 * @code
 * destination:
 *   plugin: 'entity:log'
 * process:
 *   organization:
 *     plugin: organization_lookup
 *     source: organization
 * @endcode
 * @codingStandardsIgnoreEnd
 */
#[MigrateProcess(
  id: 'organization_lookup',
  handle_multiples: FALSE,
)]
class OrganizationLookup extends EntityLookup {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Hard-code the entity type.
    $this->configuration['entity_type'] = 'organization';

    // If no bundle was specified, add all bundles.
    if (empty($this->configuration['bundle'])) {
      $organization_types = $this->entityTypeManager->getStorage('organization_type')->loadMultiple();
      foreach ($organization_types as $bundle => $organization_type) {
        $this->configuration['bundle'][] = $bundle;
      }
    }

    // Ignore case sensitivity.
    $this->configuration['ignore_case'] = TRUE;

    // Delegate to the parent entity_lookup plugin.
    return parent::transform($value, $migrate_executable, $row, $destination_property);
  }

  /**
   * {@inheritdoc}
   */
  protected function query($value) {

    // Trim the value.
    $value = trim($value);

    // If the value is empty, return NULL.
    if (empty($value)) {
      return NULL;
    }

    // We are going to attempt to look up the organization via multiple fields.
    // If one lookup fails, we will try the next, until all options are
    // exhausted.
    $results = [];

    // First, if the value is a valid UUID, attempt a UUID lookup.
    if (Uuid::isValid($value)) {
      $this->lookupValueKey = 'uuid';
      $results = parent::query($value);
    }

    // If there are no results, try a lookup by name.
    if (empty($results)) {
      $this->lookupValueKey = 'name';
      $results = parent::query($value);
    }

    // If there are no results, and the value is a positive integer, try a
    // lookup by organization ID.
    if (empty($results) && is_numeric($value) && (int) $value == $value && (int) $value > 0) {
      $this->lookupValueKey = 'id';
      $results = parent::query($value);
    }

    // If there are still no results, throw an exception and skip the row.
    if (empty($results)) {
      throw new MigrateSkipRowException('Organization not found: ' . $value);
    }

    return $results;
  }

}
