<?php

declare(strict_types=1);

namespace Drupal\data_stream_notification_test\Plugin\DataStream\NotificationDelivery;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\data_stream_notification\Attribute\NotificationDelivery;
use Drupal\data_stream_notification\Plugin\DataStream\NotificationDelivery\NotificationDeliveryBase;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Delivery plugin for testing that raises an error.
 */
#[NotificationDelivery(
  id: 'error',
  label: new TranslatableMarkup('Error'),
  context_definitions: [
    'value' => new ContextDefinition('float', label: new TranslatableMarkup('value')),
    'data_stream' => new EntityContextDefinition('data_stream', new TranslatableMarkup('Data stream')),
    'data_stream_notification' => new EntityContextDefinition('data_stream_notification', new TranslatableMarkup('Data stream notification')),
    'condition_summaries' => new ContextDefinition('list', label: new TranslatableMarkup('Condition summaries')),
  ],
)]
class Error extends NotificationDeliveryBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $value = $this->getContextValue('value');
    throw new HttpException(299, "Data stream value triggered a notification exception: $value");
  }

}
