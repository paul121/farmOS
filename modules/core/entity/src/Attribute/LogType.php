<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Log Type plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class LogType extends Plugin {

  /**
   * Constructs a log type attribute.
   *
   * @param string $id
   *   The log type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The log type label.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
