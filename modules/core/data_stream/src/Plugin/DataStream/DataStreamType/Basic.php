<?php

namespace Drupal\data_stream\Plugin\DataStream\DataStreamType;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Database\Connection;
use Drupal\data_stream\DataStreamApiInterface;
use Drupal\data_stream\DataStreamStorageInterface;
use Drupal\data_stream\Entity\DataStreamInterface;
use Drupal\data_stream\Traits\DataStreamSqlStorage;
use Drupal\data_stream\Traits\DataStreamPrivateKeyAccess;
use Drupal\jsonapi\Exception\UnprocessableHttpEntityException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

/**
 * Provides the basic data stream type.
 *
 * @DataStreamType(
 *   id = "basic",
 *   label = @Translation("Basic"),
 * )
 */
class Basic extends DataStreamTypeBase implements DataStreamStorageInterface, DataStreamApiInterface {

  use DataStreamSqlStorage;
  use DataStreamPrivateKeyAccess;

  /**
   * A database connection.
   *
   * @var \Drupal\Core\Database\Connection
   *
   * @see DataStreamSqlStorage
   */
  protected $connection;

  /**
   * Database table.
   *
   * @var string
   *
   * @see DataStreamSqlStorage
   */
  protected $tableName = 'data_stream_basic';

  /**
   * Constructs a \Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = [];

    // Save the table name.
    $data_table = 'data_stream_basic';

    // Describe the {data_stream_basic} table.
    $data[$data_table]['table']['group'] = $this->t('Basic data stream data');

    // Data stream ID.
    $data[$data_table]['id'] = [
      'title' => $this->t('Data stream ID'),
      'help' => $this->t('ID of the data stream entity.'),
      'relationship' => [
        'base' => 'data_stream_data',
        'base_field' => 'id',
        'id' => 'standard',
        'label' => $this->t('Data stream entity'),
      ],
    ];

    // Timestamp.
    $data[$data_table]['timestamp'] = [
      'title' => $this->t('Timestamp'),
      'help' => $this->t('Timestamp of the sensor reading.'),
      'field' => [
        'id' => 'date',
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'date',
      ],
      'filter' => [
        'id' => 'date',
      ],
    ];

    // Value numerator.
    $data[$data_table]['value_numerator'] = [
      'title' => $this->t('Sensor value numerator'),
      'help' => $this->t('The stored numerator value of the sensor reading.'),
      'field' => [
        'id' => 'numeric',
        'click sortable' => TRUE,
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'sort',
      ],
    ];

    // Value denominator.
    $data[$data_table]['value_denominator'] = [
      'title' => $this->t('Sensor value denominator'),
      'help' => $this->t('The stored denominator value of the sensor reading.'),
      'field' => [
        'id' => 'numeric',
        'click sortable' => TRUE,
      ],
      'filter' => [
        'id' => 'numeric',
      ],
      'sort' => [
        'id' => 'sort',
      ],
    ];

    // Create a new decimal column with fraction decimal handlers.
    $fraction_fields = [
      'numerator' => 'value_numerator',
      'denominator' => 'value_denominator',
    ];
    $data[$data_table]['value_decimal'] = [
      'title' => $this->t('Sensor value (decimal)'),
      'help' => $this->t('Decimal equivalent of sensor value.'),
      'real field' => 'value_numerator',
      'field' => [
        'id' => 'fraction',
        'additional fields' => $fraction_fields,
        'click sortable' => TRUE,
      ],
      'sort' => [
        'id' => 'fraction',
        'additional fields' => $fraction_fields,
      ],
      'filter' => [
        'id' => 'fraction',
        'additional fields' => $fraction_fields,
      ],
    ];

    // Add a basic_data relationship to the data_stream_data table that
    // references the data_stream_basic table.
    $data['data_stream_data']['basic_data'] = [
      'title' => $this->t('Basic data'),
      'help' => $this->t('Basic data stream data.'),
      'relationship' => [
        'base' => 'data_stream_basic',
        'base field' => 'id',
        'field' => 'id',
        'id' => 'standard',
        'label' => $this->t('Basic data'),
      ],
    ];

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function apiAllowedMethods() {
    return [Request::METHOD_GET, Request::METHOD_POST];
  }

  /**
   * {@inheritdoc}
   */
  public function apiHandleRequest(DataStreamInterface $stream, Request $request) {

    // Get request method.
    $method = $request->getMethod();

    // Handle GET request.
    if ($method == Request::METHOD_GET) {

      // Bail if the sensor is not public and no private_key is provided.
      if (!$stream->isPublic() && !$this->requestHasValidPrivateKey($stream, $request)) {
        throw new AccessDeniedHttpException();
      }

      return $this->apiGet($stream, $request);
    }

    // Handle POST request.
    if ($method == Request::METHOD_POST) {
      if (!$this->requestHasValidPrivateKey($stream, $request)) {
        throw new AccessDeniedHttpException();
      }
      return $this->apiPost($stream, $request);
    }

    // Else bail.
    throw new MethodNotAllowedHttpException($this->apiAllowedMethods());
  }

  /**
   * Handle API GET requests.
   *
   * @param \Drupal\data_stream\Entity\DataStreamInterface $stream
   *   The data stream.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response.
   */
  protected function apiGet(DataStreamInterface $stream, Request $request) {

    $params = $request->query->all();

    $max_limit = 100000;

    $limit = $max_limit;
    if (isset($params['limit'])) {
      $limit = $params['limit'];

      // Bail if more than the max is requested.
      // Only allow 100k max data points to prevent exhausting PHP's memory,
      // which is a potential DDoS vector.
      if ($limit > $max_limit) {
        throw new UnprocessableHttpEntityException();
      }
    }
    $params['limit'] = $limit;

    $data = $this->storageGet($stream, $params);
    return JsonResponse::create($data);
  }

  /**
   * Handle API POST requests.
   *
   * @param \Drupal\data_stream\Entity\DataStreamInterface $stream
   *   The data stream.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  protected function apiPost(DataStreamInterface $stream, Request $request) {
    $data = Json::decode($request->getContent());
    $success = $this->storageSave($stream, $data);

    if (!$success) {
      throw new BadRequestHttpException();
    }

    return Response::create('', Response::HTTP_CREATED);
  }

}
