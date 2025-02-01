<?php

declare(strict_types=1);

namespace Drupal\organization\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Action that archives an organization.
 */
#[Action(
  id: 'organization_archive_action',
  label: new TranslatableMarkup('Archive an organization'),
  type: 'organization',
)]
class OrganizationArchive extends OrganizationStateChangeBase {

  /**
   * {@inheritdoc}
   */
  protected $targetState = 'archived';

}
