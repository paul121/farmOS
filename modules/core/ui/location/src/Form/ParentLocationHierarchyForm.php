<?php

declare(strict_types=1);

namespace Drupal\farm_ui_location\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\asset\Entity\AssetInterface;

/**
 * Form for changing the hierarchy of location assets in a parent hierarchy.
 *
 * @ingroup farm
 */
class ParentLocationHierarchyForm extends BaseLocationHierarchyForm {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'farm_ui_location_parent_location_form';
  }

  /**
   * Check access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The account to check.
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The asset to check.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, AssetInterface $asset) {

    // If the asset is not a location, forbid access.
    if (!$this->assetLocation->isLocation($asset)) {
      return AccessResult::forbidden();
    }

    // If the asset does not have child locations, forbid access.
    if (empty($this->getLocations($asset))) {
      return AccessResult::forbidden();
    }

    // Allow access if the asset has child locations.
    return AccessResult::allowedIf($asset->access('view', $account));
  }

  /**
   * Generate the page title.
   *
   * @param \Drupal\asset\Entity\AssetInterface $asset
   *   The parent asset that this page is being built for.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Returns the translated page title.
   */
  public function getTitle(AssetInterface $asset) {
    return $this->t('Locations in %location', ['%location' => $asset->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?AssetInterface $asset = NULL) {

    // Bail if there is no asset.
    if (!$asset) {
      return $form;
    }

    // Build location form.
    return parent::buildLocationForm(
      $form,
      $asset->label(),
      $asset->toUrl('canonical'),
      $this->buildTree($asset),
      $asset->id(),
    );
  }

}
