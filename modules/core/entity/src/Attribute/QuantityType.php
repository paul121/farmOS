<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Quantity Type plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class QuantityType extends Plugin {

  /**
   * Constructs a quantity type attribute.
   *
   * @param string $id
   *   The quantity type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The quantity type label.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
