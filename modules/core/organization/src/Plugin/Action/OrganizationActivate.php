<?php

declare(strict_types=1);

namespace Drupal\organization\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Action that makes an organization active.
 */
#[Action(
  id: 'organization_activate_action',
  label: new TranslatableMarkup('Makes an Organization active'),
  type: 'organization',
)]
class OrganizationActivate extends OrganizationStateChangeBase {

  /**
   * {@inheritdoc}
   */
  protected $targetState = 'active';

}
