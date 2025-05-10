<?php

declare(strict_types=1);

namespace Drupal\farm_ui_location\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\asset\Entity\AssetInterface;
use Drupal\farm_location\AssetLocationInterface;
use Drupal\organization\Entity\OrganizationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for changing the hierarchy of location assets within an organization.
 *
 * @ingroup farm
 */
class OrganizationLocationHierarchyForm extends BaseLocationHierarchyForm {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AssetLocationInterface $asset_location, RouteMatchInterface $route_match) {
    parent::__construct($entity_type_manager, $asset_location);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('asset.location'),
      $container->get('current_route_match'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_ui_location_organization_location_form';
  }

  /**
   * Generate the page title.
   *
   * @param \Drupal\organization\Entity\OrganizationInterface $organization
   *   The organization this page is being built for.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Returns the translated page title.
   */
  public function getTitle(OrganizationInterface $organization) {
    return $this->t('Locations in %organization', ['%organization' => $organization->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?OrganizationInterface $organization = NULL) {

    // Bail if there is no organization.
    if (!$organization) {
      return $form;
    }

    // Show a map of organization locations.
    $form['map'] = [
      '#type' => 'farm_map',
      '#map_type' => 'locations',
      '#location_filters' => [
        'farm_target_id' => $organization->label(),
      ],
    ];

    // Build location form.
    return parent::buildLocationForm(
      $form,
      $organization->label(),
      $organization->toUrl('canonical'),
      $this->buildTree(),
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getLocationQuery(?AssetInterface $parent = NULL): QueryInterface {

    // Add farm organization condition to query.
    $query = parent::getLocationQuery($parent);
    if ($organization = $this->routeMatch->getParameter('organization')) {
      $query->condition('farm', $organization->id());
    }
    return $query;
  }

}
