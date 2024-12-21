<?php

declare(strict_types=1);

namespace Drupal\farm_fieldkit\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the FieldModule entity.
 *
 * @ingroup farm
 */
#[ConfigEntityType(
  id: 'field_module',
  label: new TranslatableMarkup('Field module'),
  label_collection: new TranslatableMarkup('Field modules'),
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
  ],
  handlers: [
    'access' => '\Drupal\entity\EntityAccessControlHandler',
    'permission_provider' => '\Drupal\entity\EntityPermissionProvider',
  ],
  config_export: [
    'id',
    'label',
    'description',
    'library',
  ],
)]
class FieldModule extends ConfigEntityBase implements FieldModuleInterface {

  /**
   * The field module ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The field module label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this field module.
   *
   * @var string
   */
  protected $description;

  /**
   * The field module library name.
   *
   * @var string
   */
  protected $library;

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

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibrary() {
    return $this->library;
  }

}
