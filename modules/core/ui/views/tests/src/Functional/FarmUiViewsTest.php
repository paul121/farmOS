<?php

declare(strict_types=1);

namespace Drupal\Tests\farm_ui_views\Functional;

use Drupal\Tests\farm_test\Functional\FarmBrowserTestBase;
use Drupal\asset\Entity\Asset;
use Drupal\log\Entity\Log;
use Drupal\organization\Entity\Organization;
use Drupal\quantity\Entity\Quantity;

/**
 * Tests the farm_ui_views Views.
 *
 * @group farm
 */
class FarmUiViewsTest extends FarmBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'farm_activity',
    'farm_equipment',
    'farm_farm',
    'farm_inventory',
    'farm_observation',
    'farm_quantity_standard',
    'farm_water',
    'farm_ui_views',
    'farm_ui_views_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create and login a user with necessary permissions.
    $user = $this->createUser([
      'view any asset',
      'view any log',
      'view any organization',
      'view any quantity',
    ]);
    $this->drupalLogin($user);

    // Disable entity_reference_integrity_enforce module's protections, so we
    // can delete all entities easily.
    $erie_config = \Drupal::configFactory()->getEditable('entity_reference_integrity_enforce.settings');
    $erie_config->set('enabled_entity_type_ids', []);
    $erie_config->save();
  }

  /**
   * Run all tests.
   */
  public function testAll() {

    // Run each set of tests in a single method to decrease test time.
    // Delete all entities between each set of tests.
    $this->doTestAssetViews();
    $this->deleteAllEntities();

    $this->doTestLogViews();
    $this->deleteAllEntities();

    $this->doTestAssetsByLocationView();
    $this->deleteAllEntities();

    $this->doTestAssetChildrenView();
    $this->deleteAllEntities();

    $this->doTestAssetInventoryView();
    $this->deleteAllEntities();

    $this->doTestAssetLogsView();
    $this->deleteAllEntities();

    $this->doTestOrganizationAssetViews();
    $this->deleteAllEntities();

    $this->doTestOrganizationLogViews();
    $this->deleteAllEntities();
  }

  /**
   * Test farm_asset View's page and page_type displays.
   */
  public function doTestAssetViews() {

    // Create two assets of different types.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $equipment->save();
    $water = Asset::create([
      'name' => 'Water asset',
      'type' => 'water',
      'status' => 'active',
    ]);
    $water->save();

    // Check that both assets are visible in /assets.
    $this->drupalGet('/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());

    // Check that only water assets are visible in /assets/water.
    $this->drupalGet('/assets/water');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());

    // Check that /assets/equipment includes equipment-specific columns.
    $this->drupalGet('/assets/equipment');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manufacturer');
    $this->assertSession()->pageTextContains('Model');
    $this->assertSession()->pageTextContains('Serial number');
  }

  /**
   * Test farm_log View's page and page_type displays.
   */
  public function doTestLogViews() {

    // Create two activity logs with different test_string values.
    $activity1 = Log::create([
      'name' => 'Foo activity',
      'type' => 'activity',
      'status' => 'done',
      'test_string' => 'foo',
    ]);
    $activity1->save();
    $activity2 = Log::create([
      'name' => 'Baz activity',
      'type' => 'activity',
      'status' => 'done',
      'test_string' => 'bar',
    ]);
    $activity2->save();

    // Check that /logs and /logs/activity include the "Test string" column.
    $this->drupalGet('/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Test string');
    $this->drupalGet('/logs/activity');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Test string');

    // Check that both activity logs are present.
    $this->assertSession()->pageTextContains('Foo activity');
    $this->assertSession()->pageTextContains('Baz activity');

    // Check that filtering by "Test string" works.
    $this->drupalGet('/logs/activity', ['query' => ['test_string' => 'foo']]);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Foo activity');
    $this->assertSession()->pageTextNotContains('Baz activity');
  }

  /**
   * Test farm_asset View's page_location display.
   */
  public function doTestAssetsByLocationView() {

    // Create two assets of different types.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $equipment->save();
    $water = Asset::create([
      'name' => 'Water asset',
      'type' => 'water',
      'status' => 'active',
    ]);
    $water->save();

    // Check that the equipment does not appear in /asset/%/assets.
    $this->drupalGet('/asset/' . $water->id() . '/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($equipment->label());

    // Move the equipment asset into the water asset via an activity log.
    $movement = Log::create([
      'type' => 'activity',
      'asset' => [$equipment],
      'location' => [$water],
      'status' => 'done',
      'is_movement' => TRUE,
    ]);
    $movement->save();

    // Check that the equipment appears in /asset/%/assets.
    $this->drupalGet('/asset/' . $water->id() . '/assets');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($equipment->label());

    // Change is_location to FALSE on the water asset.
    $water->set('is_location', FALSE);
    $water->save();

    // Check that /asset/%/assets returns a 403.
    $this->drupalGet('/asset/' . $water->id() . '/assets');
    $this->assertSession()->statusCodeEquals(403);

    // Check that invalid asset IDs are handled gracefully by the access check.
    $this->drupalGet('/asset/0/assets');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/-1/assets');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/foo/assets');
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Test farm_asset View's page_children display.
   */
  public function doTestAssetChildrenView() {

    // Create a parent asset.
    $parent = Asset::create([
      'name' => 'Parent asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $parent->save();

    // Check that /asset/%/children returns a 403.
    $this->drupalGet('/asset/' . $parent->id() . '/children');
    $this->assertSession()->statusCodeEquals(403);

    // Create a child asset.
    $child = Asset::create([
      'name' => 'Child asset',
      'type' => 'equipment',
      'parent' => [$parent],
      'status' => 'active',
    ]);
    $child->save();

    // Check that the child appears in /asset/%/children.
    $this->drupalGet('/asset/' . $parent->id() . '/children');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($child->label());

    // Set is_location to TRUE on the parent.
    $parent->set('is_location', TRUE);
    $parent->save();

    // Check that /asset/%/children returns a 403.
    $this->drupalGet('/asset/' . $parent->id() . '/children');
    $this->assertSession()->statusCodeEquals(403);

    // Check that invalid asset IDs are handled gracefully by the access check.
    $this->drupalGet('/asset/0/children');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/-1/children');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/foo/children');
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Test farm_inventory View's page_asset display.
   */
  public function doTestAssetInventoryView() {

    // Create an equipment asset.
    $water = Asset::create([
      'name' => 'Cistern',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $water->save();

    // Check that /asset/%/inventory returns a 403.
    $this->drupalGet('/asset/' . $water->id() . '/inventory');
    $this->assertSession()->statusCodeEquals(403);

    // Create an observation log with a quantity that sets asset inventory.
    $quantity = Quantity::create([
      'type' => 'standard',
      'value' => 1101,
      'inventory_adjustment' => 'reset',
      'inventory_asset' => $water,
    ]);
    $quantity->save();
    $observation = Log::create([
      'name' => 'Cistern observation',
      'type' => 'observation',
      'quantity' => [$quantity],
      'status' => 'done',
    ]);
    $observation->save();

    // Check that the log appears in /asset/%/inventory.
    $this->drupalGet('/asset/' . $water->id() . '/inventory');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextContains('Reset');
    $this->assertSession()->pageTextContains('1101');

    // Check that invalid asset IDs are handled gracefully by the access check.
    $this->drupalGet('/asset/0/inventory');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/-1/inventory');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/foo/inventory');
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Test farm_log View's page_asset display.
   */
  public function doTestAssetLogsView() {

    // Create an equipment asset.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
    ]);
    $equipment->save();

    // Check that /asset/%/logs shows "No logs found.".
    $this->drupalGet('/asset/' . $equipment->id() . '/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No logs found.');

    // Create two logs of different types that reference the asset.
    $activity = Log::create([
      'name' => 'Equipment activity',
      'type' => 'activity',
      'asset' => [$equipment],
      'status' => 'done',
    ]);
    $activity->save();
    $observation = Log::create([
      'name' => 'Equipment observation',
      'type' => 'observation',
      'asset' => [$equipment],
      'status' => 'done',
    ]);
    $observation->save();

    // Create a third log that does not reference the asset.
    $unrelated = Log::create([
      'name' => 'Generic activity',
      'type' => 'activity',
      'status' => 'done',
    ]);
    $unrelated->save();

    // Check that only 2 logs appear in /asset/%/logs and /asset/%/logs/all.
    $this->drupalGet('/asset/' . $equipment->id() . '/logs');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/all');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());

    // Check that the appropriate logs appear in /asset/%/logs/%log_type.
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/activity');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextNotContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/observation');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());

    // Check that invalid asset IDs are handled gracefully by the access check.
    $this->drupalGet('/asset/0/logs');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/-1/logs');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/foo/logs');
    $this->assertSession()->statusCodeEquals(404);

    // Check that an invalid log_type parameter is handled gracefully by the
    // access check. /asset/%/logs/0 will be passed on to the Views contextual
    // filter validation, so it should return a 404. /asset/%/logs/-1 and
    // /asset/%/logs/foo will be caught by the access check, but will 403
    // because no logs will be found of that type.
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/0');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/-1');
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet('/asset/' . $equipment->id() . '/logs/foo');
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test farm_organization_asset View's page and page_type displays.
   */
  public function doTestOrganizationAssetViews() {

    // Create a farm organization.
    $farm = Organization::create([
      'type' => 'farm',
      'name' => 'Farm 1',
    ]);
    $farm->save();
    $farm_id = $farm->id();

    // Create an unrelated asset.
    $unrelated = Asset::create([
      'name' => 'Unrelated asset',
      'type' => 'water',
      'status' => 'active',
    ]);
    $unrelated->save();

    // Check that /assets shows "No assets found.".
    $this->drupalGet("/organization/$farm_id/assets");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No assets found.');

    // Create assets for the farm organization.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
      'farm' => $farm,
    ]);
    $equipment->save();
    $water = Asset::create([
      'name' => 'Water asset',
      'type' => 'water',
      'status' => 'active',
      'farm' => $farm,
    ]);
    $water->save();

    // Check that only organization assets are visible in /assets.
    $this->drupalGet("/organization/$farm_id/assets");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());

    // Check that only water assets are visible in /assets/water.
    $this->drupalGet("/organization/$farm_id/assets/water");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());
    $this->assertSession()->pageTextNotContains($unrelated->label());

    // Check that /assets/equipment includes bundle view logic.
    $this->drupalGet("/organization/$farm_id/assets/equipment");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Asset type');
    $this->assertSession()->pageTextContains('Manufacturer');
    $this->assertSession()->pageTextContains('Model');
    $this->assertSession()->pageTextContains('Serial number');

    // Check that invalid organization IDs are handled gracefully by the access
    // check.
    $this->drupalGet('/organization/0/assets');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/organization/-1/assets');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/organization/foo/assets');
    $this->assertSession()->statusCodeEquals(404);

    // Check that an invalid asset_type parameter is handled gracefully by the
    // access check. /organization/%/assets/0 will be passed on to the Views
    // contextual filter validation, so it should return a 404.
    // /organization/%/assets/-1 and /organization/%/assets/foo will be caught
    // by the access check, but will 403 because no logs will be found
    // of that type.
    $this->drupalGet("/organization/$farm_id/assets/0");
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet("/organization/$farm_id/assets/-1");
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet("/organization/$farm_id/assets/foo");
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Test farm_organization_log View's page and page_type displays.
   */
  public function doTestOrganizationLogViews() {

    // Create a farm organization.
    $farm = Organization::create([
      'type' => 'farm',
      'name' => 'Farm 1',
    ]);
    $farm->save();
    $farm_id = $farm->id();

    // Create unrelated asset and log.
    $unrelated_asset = Asset::create([
      'name' => 'Unrelated asset',
      'type' => 'water',
      'status' => 'active',
    ]);
    $unrelated_asset->save();
    $unrelated_activity = Log::create([
      'name' => 'Unrelated water activity',
      'type' => 'activity',
      'asset' => $unrelated_asset,
      'location' => $unrelated_asset,
      'status' => 'done',
    ]);
    $unrelated_activity->save();

    // Check that /logs shows "No logs found.".
    $this->drupalGet("/organization/$farm_id/logs");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('No logs found.');

    // Create assets for the farm organization.
    $equipment = Asset::create([
      'name' => 'Equipment asset',
      'type' => 'equipment',
      'status' => 'active',
      'farm' => $farm,
    ]);
    $equipment->save();
    $water = Asset::create([
      'name' => 'Water asset',
      'type' => 'water',
      'status' => 'active',
      'farm' => $farm,
    ]);
    $water->save();

    // Create logs reference the organization assets.
    $activity = Log::create([
      'name' => 'Equipment activity',
      'type' => 'activity',
      'location' => $equipment,
      'status' => 'done',
    ]);
    $activity->save();
    $observation = Log::create([
      'name' => 'Equipment observation',
      'type' => 'observation',
      'asset' => $equipment,
      'location' => $water,
      'status' => 'done',
    ]);
    $observation->save();

    // Check that only organization logs are visible in /logs.
    $this->drupalGet("/organization/$farm_id/logs");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());
    $this->assertSession()->pageTextNotContains($unrelated_activity->label());

    // Check that only activity logs are visible in /logs/activity.
    $this->drupalGet("/organization/$farm_id/logs/activity");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($activity->label());
    $this->assertSession()->pageTextNotContains($observation->label());
    $this->assertSession()->pageTextContains($equipment->label());
    $this->assertSession()->pageTextNotContains($water->label());
    $this->assertSession()->pageTextNotContains($unrelated_activity->label());

    // Check that only observation logs are visible in /logs/observation.
    $this->drupalGet("/organization/$farm_id/logs/observation");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains($activity->label());
    $this->assertSession()->pageTextContains($observation->label());
    $this->assertSession()->pageTextContains($equipment->label());
    $this->assertSession()->pageTextContains($water->label());
    $this->assertSession()->pageTextNotContains($unrelated_activity->label());

    // Check that /logs/activity includes bundle view logic.
    $this->drupalGet("/organization/$farm_id/logs/activity");
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('Log type');

    // Check that invalid organization IDs are handled gracefully by the access
    // check.
    $this->drupalGet('/organization/0/logs');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/organization/-1/logs');
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet('/organization/foo/logs');
    $this->assertSession()->statusCodeEquals(404);

    // Check that an invalid log_type parameter is handled gracefully by the
    // access check. /organization/%/logs/0 will be passed on to the Views
    // contextual filter validation, so it should return a 404.
    // /organization/%/logs/-1 and /organization/%/logs/foo will be caught
    // by the access check, but will 403 because no logs will be found
    // of that type.
    $this->drupalGet("/organization/$farm_id/logs/0");
    $this->assertSession()->statusCodeEquals(404);
    $this->drupalGet("/organization/$farm_id/logs/-1");
    $this->assertSession()->statusCodeEquals(403);
    $this->drupalGet("/organization/$farm_id/logs/foo");
    $this->assertSession()->statusCodeEquals(403);
  }

  /**
   * Delete all entities.
   */
  protected function deleteAllEntities() {
    foreach (['asset', 'log', 'organization'] as $type) {
      $storage = \Drupal::entityTypeManager()->getStorage($type);
      $storage->delete($storage->loadMultiple());
    }
  }

}
