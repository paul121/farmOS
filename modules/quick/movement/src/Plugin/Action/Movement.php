<?php

declare(strict_types=1);

namespace Drupal\farm_quick_movement\Plugin\Action;

use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\farm_quick\Plugin\Action\QuickFormActionBase;

/**
 * Action for recording movements.
 */
#[Action(
  id: 'quick_movement',
  label: new TranslatableMarkup('Record movement'),
  confirm_form_route_name: 'farm.quick.movement',
  type: 'asset',
)]
class Movement extends QuickFormActionBase {

  /**
   * {@inheritdoc}
   */
  public function getQuickFormId(): string {
    return 'movement';
  }

}
