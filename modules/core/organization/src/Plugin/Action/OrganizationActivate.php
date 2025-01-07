<?php

declare(strict_types=1);

namespace Drupal\organization\Plugin\Action;

/**
 * Action that makes an organization active.
 *
 * @Action(
 *   id = "organization_activate_action",
 *   label = @Translation("Makes an Organization active"),
 *   type = "organization"
 * )
 */
class OrganizationActivate extends OrganizationStateChangeBase {

  /**
   * {@inheritdoc}
   */
  protected $targetState = 'active';

}
