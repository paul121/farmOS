<?php

namespace Drupal\farm_group;

use Drupal\asset\Entity\AssetInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\farm_log\LogQueryFactoryInterface;
use Drupal\log\Entity\LogInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Asset group membership logic.
 */
class GroupMembership implements GroupMembershipInterface {

  /**
   * The name of the log group reference field.
   *
   * @var string
   */
  const LOG_FIELD_GROUP = 'group';

  /**
   * Log query factory.
   *
   * @var \Drupal\farm_log\LogQueryFactoryInterface
   */
  protected LogQueryFactoryInterface $logQueryFactory;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Class constructor.
   *
   * @param \Drupal\farm_log\LogQueryFactoryInterface $log_query_factory
   *   Log query factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(LogQueryFactoryInterface $log_query_factory, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time) {
    $this->logQueryFactory = $log_query_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('farm.log_query'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasGroup(AssetInterface $asset): bool {
    $log = $this->getGroupAssignmentLog($asset);
    if (empty($log)) {
      return FALSE;
    }
    return !$log->get(static::LOG_FIELD_GROUP)->isEmpty();
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(AssetInterface $asset): array {
    $log = $this->getGroupAssignmentLog($asset);
    if (empty($log)) {
      return [];
    }
    return $log->{static::LOG_FIELD_GROUP}->referencedEntities() ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupAssignmentLog(AssetInterface $asset): ?LogInterface {

    // Get the latest group assignment log.
    $logs = $this->getGroupAssignmentLogs($asset, ['limit' => 1]);

    // Return the log, if available.
    if (!empty($logs)) {
      return reset($logs);
    }

    // Otherwise, return NULL.
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupAssignmentLogs(AssetInterface $asset, array $options = []): array {

    // If the asset is new, no group assignment logs will reference it.
    if ($asset->isNew()) {
      return [];
    }

    // Query for group assignment logs that reference the asset.
    $group_assignment_options = [
      'asset' => $asset,
      'timestamp' => $this->time->getRequestTime(),
      'status' => 'done',
    ];
    $group_assignment_options += $options;
    $query = $this->logQueryFactory->getQuery($group_assignment_options);
    $query->condition('is_group_assignment', TRUE);
    $log_ids = $query->execute();

    // Bail if no logs are found.
    if (empty($log_ids)) {
      return [];
    }

    // Load the logs.
    /** @var \Drupal\log\Entity\LogInterface[] $logs */
    $logs = $this->entityTypeManager->getStorage('log')->loadMultiple($log_ids);
    return $logs;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupHistory(AssetInterface $asset): array {

    // Get all group assignment logs. Sort ascending for convenience.
    $logs = $this->getGroupAssignmentLogs($asset, ['sort' => 'ASC']);

    // Build history by looping through the logs.
    $history = [];
    foreach ($logs as $log) {

      // Set this log as the departing log for active intervals.
      // If this log is not a departing log it will be overwritten below.
      // This is an optimistic optimization, it prevents us needing a loop
      // later on.
      foreach ($history as &$group_intervals) {
        if (!empty($group_intervals)) {
          $last_interval = &$group_intervals[count($group_intervals) - 1];
          $last_interval['depart'] = $last_interval['depart'] ?? $log;
        }
      }
      // Unset the array pointer.
      unset($group_intervals);

      // Loop through each group the log references.
      // Either create a new interval, or unset the depart log that was set
      // above.
      foreach ($log->get('group')->referencedEntities() as $group) {

        // If the group has existing intervals.
        if (!empty($history[$group->id()])) {

          // Get the group intervals and point to the last one.
          $group_intervals = $history[$group->id()];
          $last_interval = &$group_intervals[count($group_intervals) - 1];
          $depart_log = $last_interval['depart'];

          // If the current log was set as the departing log above, set the
          // departing log to NULL and continue to the next group.
          if (!empty($depart_log) && $depart_log->id() == $log->id()) {
            $group_intervals['depart'] = NULL;
            continue;
          }
        }

        // Otherwise we add a new interval starting with the current log.
        $history[$group->id()][] = [
          'arrive' => $log,
          'depart' => NULL,
        ];
      }
    }

    return $history;
  }

}
