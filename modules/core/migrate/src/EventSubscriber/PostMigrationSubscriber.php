<?php

namespace Drupal\farm_migrate\EventSubscriber;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PostMigrationSubscriber.
 *
 * Run our user flagging after the last node migration is run.
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * PostMigrationSubscriber Constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(Connection $database, TimeInterface $time) {
    $this->database = $database;
    $this->time = $time;
  }

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_IMPORT][] = ['onMigratePostImport'];
    $events[MigrateEvents::POST_ROW_SAVE][] = ['onMigratePostRowSave'];
    return $events;
  }

  /**
   * Run post migration logic.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {

    // Define the migration groups that we will post-process and their
    // corresponding entity revision tables.
    $groups_revision_tables = [
      'farm_migrate_asset' => 'asset_revision',
      'farm_migrate_area' => 'asset_revision',
      'farm_migrate_log' => 'log_revision',
      'farm_migrate_plan' => 'plan_revision',
      'farm_migrate_quantity' => 'quantity_revision',
      'farm_migrate_taxonomy' => 'taxonomy_term_revision',
    ];
    $migration = $event->getMigration();
    if (isset($migration->migration_group) && array_key_exists($migration->migration_group, $groups_revision_tables)) {

      // Define the entity id column name. This will be "id" in all cases
      // except taxonomy_terms, which use "tid".
      $id_column = 'id';
      if ($migration->migration_group == 'farm_migrate_taxonomy') {
        $id_column = 'tid';
      }

      // Build a query to set the revision log message.
      $revision_table = $groups_revision_tables[$migration->migration_group];
      $migration_id = $migration->id();
      $query = "UPDATE {$revision_table}
        SET revision_log_message = :revision_log_message
        WHERE revision_id IN (
          SELECT r.revision_id
          FROM {migrate_map_$migration_id} mm
          INNER JOIN {$revision_table} r ON mm.destid1 = r.$id_column
        )";
      $args = [
        ':revision_log_message' => 'Migrated from farmOS 1.x on ' . date('Y-m-d', $this->time->getRequestTime()),
      ];
      $this->database->query($query, $args);
    }
  }

  /**
   * Run logic after rows are migrated.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *   The post row save migrate event.
   */
  public function onMigratePostRowSave(MigratePostRowSaveEvent $event) {

    $migration = $event->getMigration();
    $migration_id = $migration->id();

    // Migrate listener sensor data for each data stream.
    if ($migration_id === "farm_migrate_sensor_listener_data_streams") {

      // Get values to identify the source data.
      $migration_row = $event->getRow();
      $source_id = $migration_row->getSourceProperty('id');
      $source_name = $migration_row->getSourceProperty('name');

      // Get the destination data stream ID.
      $destination_id = $event->getDestinationIdValues()[0];

      // Query the source for data. Override the ID field that is returned with
      // each row to be the ID of the migrated data stream.
      $query = "SELECT :id as id, timestamp, value_numerator, value_denominator
          FROM {farm_sensor_data} fsd
          WHERE fsd.id = :sensor_id and fsd.name = :name
         ";
      $args = [
        'id' => $destination_id,
        'sensor_id' => $source_id,
        'name' => $source_name,
      ];
      $source_db = Database::getConnection('default', 'migrate');
      $source_data = $source_db->query($query, $args);
      $source_data->setFetchMode(\PDO::FETCH_ASSOC);

      // Start an insert statement.
      $insert = $this->database->insert('data_stream_basic')
        ->fields(['id', 'timestamp', 'value_numerator', 'value_denominator']);

      // Loop through the source data and insert in batches.
      $batch_size = 10000;
      $count = 0;
      foreach ($source_data as $data) {
        $insert->values($data);
        $count++;
        if ($count >= $batch_size) {
          $insert->execute();
          $count = 0;
          $insert = $this->database->insert('data_stream_basic')
            ->fields(['id', 'timestamp', 'value_numerator', 'value_denominator']);
        }
      }
      $insert->execute();
    }
  }

}
