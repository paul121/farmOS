<?php

declare(strict_types=1);

namespace Drupal\data_stream_notification\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * The Notification Condition plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class NotificationCondition extends Plugin {

  /**
   * Constructs a notification condition attribute.
   *
   * @param string $id
   *   The notification condition ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   The notification condition label.
   * @param \Drupal\Core\Plugin\Context\ContextDefinitionInterface[] $context_definitions
   *   (optional) An array of context definitions describing the context used by
   *   the plugin. The array is keyed by context names.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
    public readonly array $context_definitions = [],
  ) {}

}
