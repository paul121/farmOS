<?php

declare(strict_types=1);

namespace Drupal\farm_owner\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Action that assigns users to logs.
 */
#[Action(
  id: 'log_assign_action',
  label: new TranslatableMarkup('Assign logs to users.'),
  confirm_form_route_name: 'farm_owner.log_assign_action_form',
  type: 'log',
)]
class LogAssign extends AssignBase {

}
