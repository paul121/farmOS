<?php

declare(strict_types=1);

namespace Drupal\plan\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Plan record relationship type entity.
 */
#[ConfigEntityType(
  id: 'plan_record_type',
  label: new TranslatableMarkup('Plan record relationship type'),
  label_collection: new TranslatableMarkup('Plan record relationship types'),
  label_singular: new TranslatableMarkup('Plan record relationship type'),
  label_plural: new TranslatableMarkup('plan record relationship types'),
  config_prefix: 'record.type',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
  ],
  handlers: [
    'access' => '\Drupal\entity\BundleEntityAccessControlHandler',
  ],
  bundle_of: 'plan_record',
  label_count: [
    'singular' => '@count plan record relationship type',
    'plural' => '@count plan record relationship types',
  ],
  config_export: [
    'id',
    'label',
    'description',
  ],
)]
class PlanRecordType extends ConfigEntityBundleBase implements PlanRecordTypeInterface {

  /**
   * The Plan record relationship type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Plan record relationship type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this plan record relationship type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    return $this->set('description', $description);
  }

}
