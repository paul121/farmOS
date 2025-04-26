<?php

declare(strict_types=1);

namespace Drupal\farm_quick\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Quick Form plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class QuickForm extends Plugin {

  /**
   * Constructs a quick form attribute.
   *
   * @param string $id
   *   The quick form ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The quick form label.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   The quick form description.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $helpText
   *   The quick form help text.
   * @param string[] $permissions
   *   An array of access permissions for the quick form.
   * @param bool $requiresEntity
   *   Require a quick form instance entity to instantiate.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
    public readonly ?TranslatableMarkup $description = NULL,
    public readonly ?TranslatableMarkup $helpText = NULL,
    public readonly array $permissions = [],
    public readonly bool $requiresEntity = FALSE,
  ) {}

}
