<?php

namespace Drupal\Tests\farm_entity\Functional;

use Drupal\Tests\farm\Functional\FarmBrowserTestBase;

/**
 * Tests farm_entity behavior with contrib modules.
 *
 * @group farm
 */
class FarmEntityContribTest extends FarmBrowserTestBase {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The module installer service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface;
   */
  protected $moduleInstaller;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'farm';

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'farm_entity',
    'farm_entity_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp():void {
    parent::setUp();
    $this->entityFieldManager = $this->container->get('entity_field.manager');
    $this->moduleInstaller = $this->container->get('module_installer');
  }

  /**
   * Test when contrib module installed later on.
   */
  public function testHookEntityBaseFieldInfo() {

    // Install the contrib module.
    $result = $this->moduleInstaller->install(['farm_entity_contrib_test'], TRUE);
    $this->assertTrue($result);

    // Test log field storage definition.
    $fields = $this->entityFieldManager->getFieldStorageDefinitions('log');
    $this->assertArrayHasKey('test_contrib_hook_bundle_field', $fields);

    // Test bundle field storage definition.
    $fields = $this->entityFieldManager->getFieldDefinitions('log', 'test');
    $this->assertArrayHasKey('test_contrib_hook_bundle_field', $fields);

  }

}
