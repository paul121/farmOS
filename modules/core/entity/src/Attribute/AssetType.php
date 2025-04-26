<?php

declare(strict_types=1);

namespace Drupal\farm_entity\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Asset Type plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class AssetType extends Plugin {

  /**
   * Constructs an asset type attribute.
   *
   * @param string $id
   *   The asset type ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The asset type label.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
  ) {}

}
