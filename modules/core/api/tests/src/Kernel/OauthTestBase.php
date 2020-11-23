<?php

namespace Drupal\Tests\farm_api\Kernel;

use Drupal\Component\Serialization\Json;
use Drupal\consumers\Entity\Consumer;
use Drupal\Core\Url;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\ConfigTestTrait;
use Drupal\Tests\simple_oauth\Functional\RequestHelperTrait;
use Drupal\Tests\simple_oauth\Functional\SimpleOauthTestTrait;
use Drupal\Tests\simple_oauth\Functional\TokenBearerFunctionalTestBase;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base class that handles common logic for OAuth tests.
 *
 * @group farm
 */
class OauthTestBase extends KernelTestBase {

  use RequestHelperTrait;
  use SimpleOauthTestTrait;
  use ConfigTestTrait;

  protected $strictConfigSchema = FALSE;

  /**
   * The URL.
   *
   * @var \Drupal\Core\Url
   */
  protected $url;

  /**
   * The client.
   *
   * @var \Drupal\consumers\Entity\Consumer
   */
  protected $client;

  /**
   * The base URL.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'field',
    'block',
    'image',
    'serialization',
    'consumers',
    'simple_oauth',
    'text',
    'user',
    'jsonapi',
    'jsonapi_extras',
    'farm_api',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('consumer');
    $this->installEntitySchema('block');
    //$this->installEntitySchema('jsonapi_resource_config');
    $this->installConfig(['consumers', 'simple_oauth', 'farm_api']);

    $this->url = Url::fromRoute('oauth2_token.token');

    // Set up a HTTP client that accepts relative URLs.
    $this->httpClient = $this->container->get('http_client_factory')
                                        ->fromOptions(['base_uri' => $this->baseUrl]);

    $client_role = Role::create([
      'id' => $this->getRandomGenerator()->name(8, TRUE),
      'label' => $this->getRandomGenerator()->word(5),
      'is_admin' => FALSE,
    ]);
    $client_role->save();

    $this->additionalRoles = [];
    for ($i = 0; $i < mt_rand(1, 3); $i++) {
      $role = Role::create([
        'id' => $this->getRandomGenerator()->name(8, TRUE),
        'label' => $this->getRandomGenerator()->word(5),
        'is_admin' => FALSE,
      ]);
      $role->save();
      $this->additionalRoles[] = $role;
    }

    $this->clientSecret = $this->getRandomGenerator()->string();

    $this->client = Consumer::create([
      'owner_id' => '',
      'label' => $this->getRandomGenerator()->name(),
      'secret' => $this->clientSecret,
      'confidential' => TRUE,
      'third_party' => TRUE,
      'roles' => [['target_id' => $client_role->id()]],
    ]);
    $this->client->save();

    $this->user = $this->drupalCreateUser();
    $this->grantPermissions(Role::load(RoleInterface::ANONYMOUS_ID), [
      'access content',
      'debug simple_oauth tokens',
    ]);
    $this->grantPermissions(Role::load(RoleInterface::AUTHENTICATED_ID), [
      'access content',
      'debug simple_oauth tokens',
    ]);

    $this->setUpKeys();

    $num_roles = mt_rand(1, count($this->additionalRoles));
    $requested_roles = array_slice($this->additionalRoles, 0, $num_roles);
    $scopes = array_map(function (RoleInterface $role) {
      return $role->id();
    }, $requested_roles);
    $this->scope = implode(' ', $scopes);

    drupal_flush_all_caches();

    // Add a client_id to the client.
    $this->client->set('client_id', 'farm_test');
    $this->client->set('confidential', FALSE);
    $this->client->save();
  }

  /**
   * Validates a valid token response.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   The response object.
   * @param bool $has_refresh
   *   TRUE if the response should return a refresh token. FALSE otherwise.
   *
   * @return array
   *   An array representing the response of "/oauth/token".
   */
  protected function assertValidTokenResponse(ResponseInterface $response, $has_refresh = FALSE) {
    $this->assertEquals(200, $response->getStatusCode());
    $parsed_response = Json::decode((string) $response->getBody());
    $this->assertSame('Bearer', $parsed_response['token_type']);
    $expiration = $this->config('simple_oauth.settings')
                       ->get('access_token_expiration');
    $this->assertLessThanOrEqual($expiration, $parsed_response['expires_in']);
    $this->assertGreaterThanOrEqual($expiration - 10, $parsed_response['expires_in']);
    $this->assertNotEmpty($parsed_response['access_token']);
    if ($has_refresh) {
      $this->assertNotEmpty($parsed_response['refresh_token']);
    }
    else {
      $this->assertFalse(isset($parsed_response['refresh_token']));
    }

    return $parsed_response;
  }



  /**
   * Process a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   */
  protected function processRequest(Request $request) {
    return $this->container->get('http_kernel')->handle($request);
  }

}
