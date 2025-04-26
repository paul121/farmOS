<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Plan Record Type plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PlanRecordType extends Plugin {

  /**
   * Constructs a plan record type attribute.
   *
   * @param string $id
   *   The plan record type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The plan record type label.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
