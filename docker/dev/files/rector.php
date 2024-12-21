<?php

/**
 * @file
 * Rector configuration for farmOS.
 */

declare(strict_types=1);

use DrupalFinder\DrupalFinderComposerRuntime;
use DrupalRector\Drupal10\Rector\Deprecation\AnnotationToAttributeRector;
use DrupalRector\Drupal10\Rector\ValueObject\AnnotationToAttributeConfiguration;
use DrupalRector\Rector\PHPUnit\ShouldCallParentMethodsRector;
use DrupalRector\Set\Drupal10SetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {

  // Check against the Drupal 10 set list.
  $rectorConfig->sets([
    Drupal10SetList::DRUPAL_10,
  ]);

  $drupalFinder = new DrupalFinderComposerRuntime();
  $drupalRoot = $drupalFinder->getDrupalRoot();
  $rectorConfig->autoloadPaths([
    $drupalRoot . '/core',
    $drupalRoot . '/modules',
    $drupalRoot . '/profiles',
    $drupalRoot . '/themes',
  ]);

  $rectorConfig->fileExtensions(['php', 'module', 'theme', 'install', 'profile', 'inc', 'engine']);
  $rectorConfig->importNames(TRUE, FALSE);
  $rectorConfig->importShortClasses(FALSE);

  // Temporarily disable ShouldCallParentMethodsRector in LocationTest.
  // @todo Issue #3494872: Remove farm_install_modules() installation task
  // @see https://www.drupal.org/project/farm/issues/3183739
  $rectorConfig->skip([
    ShouldCallParentMethodsRector::class => [
      '*/modules/core/location/tests/src/Functional/LocationTest.php',
    ],
  ]);

  // Ensure that annotations are not used when attributes are available.
  // @todo Remove this if/when PHPStan or PHP CodeSniffer can check for it.
  $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [

    // Drupal core attributes.
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'Action', 'Drupal\Core\Action\Attribute\Action'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'Block', 'Drupal\Core\Block\Attribute\Block'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'ConfigEntityType', 'Drupal\Core\Entity\Attribute\ConfigEntityType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'Constraint', 'Drupal\Core\Validation\Attribute\Constraint'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'ContentEntityType', 'Drupal\Core\Entity\Attribute\ContentEntityType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'DataType', 'Drupal\Core\TypedData\Attribute\DataType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'FieldFormatter', 'Drupal\Core\Field\Attribute\FieldFormatter'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'FieldType', 'Drupal\Core\Field\Attribute\FieldType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'FieldWidget', 'Drupal\Core\Field\Attribute\FieldWidget'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'MigrateDestination', 'Drupal\migrate\Attribute\MigrateDestination'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'MigrateProcessPlugin', 'Drupal\migrate\Attribute\MigrateProcess'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'ViewsDisplayExtender', 'Drupal\views\Attribute\ViewsDisplayExtender'),

    // farmOS attributes.
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'AssetType', 'Drupal\farm_entity\Attribute\AssetType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'DataStreamType', 'Drupal\data_stream\Attribute\DataStreamType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'LogType', 'Drupal\log\Attribute\LogType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'NotificationCondition', 'Drupal\data_stream_notification\Attribute\NotificationCondition'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'NotificationDelivery', 'Drupal\data_stream_notification\Attribute\NotificationDelivery'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'PlanType', 'Drupal\plan\Attribute\PlanType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'QuantityType', 'Drupal\plan\Attribute\QuantityType'),
    new AnnotationToAttributeConfiguration('10.0.0', '10.0.0', 'QuickForm', 'Drupal\farm_quick\Attribute\QuickForm'),
  ]);
};
