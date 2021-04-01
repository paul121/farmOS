<?php

namespace Drupal\Tests\farm_group\Kernel;

use Drupal\asset\Entity\Asset;
use Drupal\asset\Entity\AssetInterface;
use Drupal\KernelTests\KernelTestBase;
use Drupal\log\Entity\Log;
use Drupal\log\Entity\LogInterface;

/**
 * Tests for farmOS group membership logic.
 *
 * @group farm
 */
class GroupTest extends KernelTestBase {

  /**
   * Group membership service.
   *
   * @var \Drupal\farm_group\GroupMembershipInterface
   */
  protected $groupMembership;

  /**
   * Asset location service.
   *
   * @var \Drupal\farm_location\AssetLocationInterface
   */
  protected $assetLocation;

  /**
   * Log location service.
   *
   * @var \Drupal\farm_location\LogLocationInterface
   */
  protected $logLocation;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'asset',
    'log',
    'farm_field',
    'farm_group',
    'farm_group_test',
    'farm_location',
    'farm_log',
    'geofield',
    'state_machine',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->groupMembership = \Drupal::service('group.membership');
    $this->assetLocation = \Drupal::service('asset.location');
    $this->logLocation = \Drupal::service('log.location');
    $this->installEntitySchema('asset');
    $this->installEntitySchema('log');
    $this->installEntitySchema('user');
    $this->installConfig([
      'farm_group',
      'farm_group_test',
    ]);
  }

  /**
   * Test asset group membership.
   */
  public function testGroupMembership() {

    // Create an animal asset.
    /** @var \Drupal\asset\Entity\AssetInterface $animal */
    $animal = Asset::create([
      'type' => 'animal',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $animal->save();

    // When an asset has no group assignment logs, it has no group membership.
    $this->assertFalse($this->groupMembership->hasGroup($animal), 'New assets do not have group membership.');
    $this->assertEmpty($this->groupMembership->getGroup($animal), 'New assets do not reference any groups.');
    $this->assertAssetGroupHistory($animal);

    // Create a group asset.
    /** @var \Drupal\asset\Entity\AssetInterface $first_group */
    $first_group = Asset::create([
      'type' => 'group',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $first_group->save();

    // Create a "done" log that assigns the animal to the group.
    /** @var \Drupal\log\Entity\LogInterface $first_log */
    $first_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => $first_group,
      'asset' => $animal,
    ]);
    $first_log->save();

    // When an asset has a done group assignment logs, it has group membership.
    $this->assertTrue($this->groupMembership->hasGroup($animal), 'Asset with group assignment has group membership.');
    $this->assertEquals($first_group->id(), $this->groupMembership->getGroup($animal)[0]->id(), 'Asset with group assignment is in the assigned group.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log);

    // Create a second group asset.
    /** @var \Drupal\asset\Entity\AssetInterface $second_group */
    $second_group = Asset::create([
      'type' => 'group',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $second_group->save();

    // Create a "pending" log that assigns the animal to the second group.
    /** @var \Drupal\log\Entity\LogInterface $second_log */
    $second_log = Log::create([
      'type' => 'test',
      'status' => 'pending',
      'is_group_assignment' => TRUE,
      'group' => $second_group,
      'asset' => $animal,
    ]);
    $second_log->save();

    // When an asset has a pending group assignment logs, it still has the same
    // group membership as before.
    $this->assertEquals($first_group->id(), $this->groupMembership->getGroup($animal)[0]->id(), 'Pending group assignment logs do not affect membership.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log);
    $this->assertAssetGroupHistory($animal, $second_group);

    // When the log is marked as "done", the asset's membership is updated.
    $second_log->status = 'done';
    $second_log->save();
    $this->assertEquals($second_group->id(), $this->groupMembership->getGroup($animal)[0]->id(), 'A second group assignment log updates group membership.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log, $second_log);
    $this->assertAssetGroupHistory($animal, $second_group, 0, $second_log);

    // Create a third "done" log in the future.
    /** @var \Drupal\log\Entity\LogInterface $third_log */
    $third_log = Log::create([
      'type' => 'test',
      'timestamp' => \Drupal::time()->getRequestTime() + 86400,
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => $first_group,
      'asset' => $animal,
    ]);
    $third_log->save();

    // When an asset has a "done" group assignment log in the future, the asset
    // group membership remains the same as the previous "done" movement log.
    $this->assertEquals($second_group->id(), $this->groupMembership->getGroup($animal)[0]->id(), 'A third group assignment log in the future does not update group membership.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log, $second_log);
    $this->assertAssetGroupHistory($animal, $second_group, 0, $second_log);

    // Create a fourth log with no group reference.
    /** @var \Drupal\log\Entity\LogInterface $fourth_log */
    $fourth_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => [],
      'asset' => $animal,
    ]);
    $fourth_log->save();

    // When a group assignment log is created with no group references, it
    // effectively "unsets" the asset's group membership.
    $this->assertFalse($this->groupMembership->hasGroup($animal), 'Asset group membership can be unset.');
    $this->assertEmpty($this->groupMembership->getGroup($animal), 'Unset group membership does not reference any groups.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log, $second_log);
    $this->assertAssetGroupHistory($animal, $second_group, 0, $second_log, $fourth_log);

    // Assign the animal to the first group and second group.
    /** @var \Drupal\log\Entity\LogInterface $fifth_log */
    $fifth_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => [$first_group, $second_group],
      'asset' => $animal,
    ]);
    $fifth_log->save();

    // When a log assigns multiple groups, the asset's group membership
    // is updated to include both.
    $this->assertEquals(2, count($this->groupMembership->getGroup($animal)), 'Fifth group membership log adds multiple groups.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log, $second_log);
    $this->assertAssetGroupHistory($animal, $first_group, 1, $fifth_log);
    $this->assertAssetGroupHistory($animal, $second_group, 0, $second_log, $fourth_log);
    $this->assertAssetGroupHistory($animal, $second_group, 1, $fifth_log);

    // Create a sixth log that only includes the first group.
    /** @var \Drupal\log\Entity\LogInterface $sixth_log */
    $sixth_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => $first_group,
      'asset' => $animal,
    ]);
    $sixth_log->save();

    // A log can remove membership of one group, but maintain membership of
    // another group.
    $this->assertEquals(1, count($this->groupMembership->getGroup($animal)), 'Sixth group membership logs maintains first_group, removes second.');
    $this->assertEquals($first_group->id(), $this->groupMembership->getGroup($animal)[0]->id(), 'Sixth group membership logs maintains first_group, removes second.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log, $second_log);
    $this->assertAssetGroupHistory($animal, $first_group, 1, $fifth_log);
    $this->assertAssetGroupHistory($animal, $second_group, 0, $second_log, $fourth_log);
    $this->assertAssetGroupHistory($animal, $second_group, 1, $fifth_log, $sixth_log);

    // Create a seventh log with no group reference.
    /** @var \Drupal\log\Entity\LogInterface $seventh_log */
    $seventh_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => [],
      'asset' => $animal,
    ]);
    $seventh_log->save();

    // When a group assignment log is created with no group references, it
    // effectively "unsets" the asset's group membership.
    $this->assertFalse($this->groupMembership->hasGroup($animal), 'Asset group membership can be unset.');
    $this->assertEmpty($this->groupMembership->getGroup($animal), 'Unset group membership does not reference any groups.');
    $this->assertAssetGroupHistory($animal, $first_group, 0, $first_log, $second_log);
    $this->assertAssetGroupHistory($animal, $first_group, 1, $fifth_log, $seventh_log);
    $this->assertAssetGroupHistory($animal, $second_group, 0, $second_log, $fourth_log);
    $this->assertAssetGroupHistory($animal, $second_group, 1, $fifth_log, $sixth_log);
  }

  /**
   * Test asset location with group membership.
   */
  public function testAssetLocation() {

    // Create an animal asset.
    /** @var \Drupal\asset\Entity\AssetInterface $animal */
    $animal = Asset::create([
      'type' => 'animal',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $animal->save();

    // Create a group asset.
    /** @var \Drupal\asset\Entity\AssetInterface $group */
    $group = Asset::create([
      'type' => 'group',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $group->save();

    // Create a log that assigns the animal to the group.
    /** @var \Drupal\log\Entity\LogInterface $first_log */
    $first_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => $group,
      'asset' => $animal,
    ]);
    $first_log->save();

    // Create two pasture assets.
    /** @var \Drupal\asset\Entity\AssetInterface $first_pasture */
    $first_pasture = Asset::create([
      'type' => 'pasture',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $first_pasture->save();
    /** @var \Drupal\asset\Entity\AssetInterface $second_pasture */
    $second_pasture = Asset::create([
      'type' => 'pasture',
      'name' => $this->randomMachineName(),
      'status' => 'active',
    ]);
    $second_pasture->save();

    // Create a log that moves the animal to the first pasture.
    /** @var \Drupal\log\Entity\LogInterface $second_log */
    $second_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_movement' => TRUE,
      'location' => $first_pasture,
      'asset' => $animal,
    ]);
    $second_log->save();

    // Confirm that the animal is located in the first pasture.
    $this->assertEquals($this->logLocation->getLocation($second_log), $this->assetLocation->getLocation($animal), 'Asset location is determined by asset membership log.');
    $this->assertEquals($this->logLocation->getGeometry($second_log), $this->assetLocation->getGeometry($animal), 'Asset geometry is determined by asset membership log.');

    // Create a log that moves the group to the second pasture.
    /** @var \Drupal\log\Entity\LogInterface $third_log */
    $third_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_movement' => TRUE,
      'location' => $second_pasture,
      'asset' => $group,
    ]);
    $third_log->save();

    // Confirm that the animal is located in the second pasture.
    $this->assertEquals($this->logLocation->getLocation($third_log), $this->assetLocation->getLocation($animal), 'Asset location is determined by group membership log.');
    $this->assertEquals($this->logLocation->getGeometry($third_log), $this->assetLocation->getGeometry($animal), 'Asset geometry is determined by group membership log.');

    // Create a log that unsets the group location.
    /** @var \Drupal\log\Entity\LogInterface $fourth_log */
    $fourth_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_movement' => TRUE,
      'location' => [],
      'asset' => $group,
    ]);
    $fourth_log->save();

    // Confirm that the animal location was unset.
    $this->assertEquals($this->logLocation->getLocation($fourth_log), $this->assetLocation->getLocation($animal), 'Asset location can be unset by group membership log.');
    $this->assertEquals($this->logLocation->getGeometry($fourth_log), $this->assetLocation->getGeometry($animal), 'Asset geometry can be unset by group membership log.');

    // Create a log that unsets the animal's group membership.
    /** @var \Drupal\log\Entity\LogInterface $fifth_log */
    $fifth_log = Log::create([
      'type' => 'test',
      'status' => 'done',
      'is_group_assignment' => TRUE,
      'group' => [],
      'asset' => $animal,
    ]);
    $fifth_log->save();

    // Confirm that the animal's location is determined by its own movement
    // logs now.
    $this->assertEquals($this->logLocation->getLocation($second_log), $this->assetLocation->getLocation($animal), 'Asset location is determined by asset membership log.');
    $this->assertEquals($this->logLocation->getGeometry($second_log), $this->assetLocation->getGeometry($animal), 'Asset geometry is determined by asset membership log.');
  }

  /**
   * Helper function to asset correct asset group history.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset group history.
   * @param \Drupal\asset\Entity\AssetInterface|null $group
   *   Optional group to check. If no group is provided, asserts that the
   *   asset has no group history.
   * @param int|null $interval
   *   Optional interval to check. A group must be provided. If NULL,
   *   asserts that the group has no interval.
   * @param \Drupal\log\Entity\LogInterface|null $arrive
   *   Optional arrive value to check. An interval must be provided.
   * @param \Drupal\log\Entity\LogInterface|null $depart
   *   Optional depart value to check. An arrive value must be provided.
   *   If NULL, asserts that there is no depart for the interval.
   */
  protected function assertAssetGroupHistory(AssetInterface $asset, AssetInterface $group = NULL, int $interval = NULL, LogInterface $arrive = NULL, LogInterface $depart = NULL) {
    $group_history = $this->groupMembership->getGroupHistory($asset);

    // Assert the history is empty.
    if (empty($group)) {
      $this->assertEmpty($group_history, 'Asset has empty group history.');
      return;
    }

    // Assert the history is not empty.
    $this->assertNotEmpty($group_history, 'Asset has non-empty group history.');

    // If no interval is provided, assert the group is not included.
    if (!isset($interval)) {
      $this->assertArrayNotHasKey($group->id(), $group_history, 'Group is not included in asset group history.');
      return;
    }

    // Otherwise assert the group is included.
    $this->assertArrayHasKey($group->id(), $group_history, 'Group included in asset group history.');

    // Assert the correct interval exists.
    $group_intervals = $group_history[$group->id()];
    $this->assertArrayhasKey($interval, $group_intervals, 'Interval exists in asset group history.');
    $interval = $group_intervals[$interval];

    // Assert correct arrive and depart values.
    if (!empty($arrive)) {
      $this->assertNotEmpty($interval['arrive'], 'Interval has arrive value.');
      $this->assertEquals($arrive->id(), $interval['arrive']->id(), 'Interval has correct arrive value.');

      if (empty($depart)) {
        $this->assertEmpty($interval['depart'], 'Interval has no depart value.');
      }
      else {
        $this->assertNotEmpty($interval['depart'], 'Interval has depart value.');
        $this->assertEquals($depart->id(), $interval['depart']->id(), 'Interval has correct depart value.');
      }
    }
  }

}
