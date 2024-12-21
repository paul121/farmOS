<?php

declare(strict_types=1);

namespace Drupal\data_stream\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Data Stream type entity.
 */
#[ConfigEntityType(
  id: 'data_stream_type',
  label: new TranslatableMarkup('Data stream type'),
  label_collection: new TranslatableMarkup('Data stream types'),
  label_singular: new TranslatableMarkup('Data stream type'),
  label_plural: new TranslatableMarkup('Data stream types'),
  config_prefix: 'type',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
  ],
  handlers: [
    'list_builder' => 'Drupal\data_stream\DataStreamTypeListBuilder',
    'form' => [
      'add' => 'Drupal\data_stream\Form\DataStreamTypeForm',
      'edit' => 'Drupal\data_stream\Form\DataStreamTypeForm',
      'delete' => '\Drupal\Core\Entity\EntityDeleteForm',
    ],
    'route_provider' => [
      'default' => 'Drupal\entity\Routing\DefaultHtmlRouteProvider',
    ],
  ],
  links: [
    'add-form' => '/admin/structure/data-stream-type/add',
    'edit-form' => '/admin/structure/data-stream-type/{data_stream_type}/edit',
    'delete-form' => '/admin/structure/data-stream-type/{data_stream_type}/delete',
    'collection' => '/admin/structure/data-stream-type',
  ],
  admin_permission: 'administer data stream types',
  bundle_of: 'data_stream',
  label_count: [
    'singular' => '@count data stream type',
    'plural' => '@count data stream types',
  ],
  config_export: [
    'id',
    'label',
    'description',
  ],
)]
class DataStreamType extends ConfigEntityBundleBase implements DataStreamTypeInterface {

  /**
   * The Data Stream type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Data Stream type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this data stream type.
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
