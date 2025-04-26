<?php

declare(strict_types=1);

namespace Drupal\data_stream\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Data Stream Type plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DataStreamType extends Plugin {

  /**
   * Constructs a data stream type attribute.
   *
   * @param string $id
   *   The data stream type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The data stream type label.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
