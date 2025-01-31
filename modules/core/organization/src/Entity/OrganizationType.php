<?php

declare(strict_types=1);

namespace Drupal\organization\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the organization type entity.
 *
 * @ConfigEntityType(
 *   id = "organization_type",
 *   label = @Translation("Organization type"),
 *   label_collection = @Translation("Organization types"),
 *   label_singular = @Translation("Organization type"),
 *   label_plural = @Translation("Organization types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count organization type",
 *     plural = "@count organization types",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\organization\OrganizationTypeListBuilder",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "form" = {
 *       "add" = "Drupal\organization\Form\OrganizationTypeForm",
 *       "edit" = "Drupal\organization\Form\OrganizationTypeForm",
 *       "delete" = "\Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer organization types",
 *   config_prefix = "type",
 *   bundle_of = "organization",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/organization-type/{organization_type}",
 *     "add-form" = "/admin/structure/organization-type/add",
 *     "edit-form" = "/admin/structure/organization-type/{organization_type}/edit",
 *     "delete-form" = "/admin/structure/organization-type/{organization_type}/delete",
 *     "collection" = "/admin/structure/organization-type"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "workflow",
 *     "new_revision",
 *   }
 * )
 */
class OrganizationType extends ConfigEntityBundleBase implements OrganizationTypeInterface {

  /**
   * The organization type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The organization type label.
   *
   * @var string
   */
  protected $label;

  /**
   * A brief description of this organization type.
   *
   * @var string
   */
  protected $description;

  /**
   * The organization type workflow ID.
   *
   * @var string
   */
  protected $workflow;

  /**
   * Default value of the 'Create new revision' checkbox.
   *
   * @var bool
   */
  protected $new_revision = TRUE;

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
  public function getWorkflowId() {
    return $this->workflow;
  }

  /**
   * {@inheritdoc}
   */
  public function setWorkflowId($workflow_id) {
    $this->workflow = $workflow_id;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();

    // The organization type must depend on the module that provides the
    // workflow.
    $workflow_manager = \Drupal::service('plugin.manager.workflow');
    $workflow = $workflow_manager->createInstance($this->getWorkflowId());
    $this->calculatePluginDependencies($workflow);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function shouldCreateNewRevision() {
    return $this->new_revision;
  }

  /**
   * {@inheritdoc}
   */
  public function setNewRevision($new_revision) {
    return $this->set('new_revision', $new_revision);
  }

}
