<?php

namespace Drupal\farm_api\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use League\OAuth2\Server\Entities\Traits\ClientTrait;

/**
 * Defines the FarmClient entity.
 *
 * This is used as an alternative to the Consumer content entities that
 * simple_oauth uses to define OAuth Clients.
 *
 * @ConfigEntityType(
 *   id = "farm_client",
 *   label = @Translation("Farm Client"),
 *   label_collection = @Translation("Farm Clients"),
 *   handlers = {},
 *   config_prefix = "farm_client",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "roles",
 *   }
 * )
 *
 * @ingroup farm
 */
class FarmClient extends ConfigEntityBase implements FarmClientInterface {

  /**
   * The farm_client ID.
   *
   * This will be used as the client_id.
   *
   * @var string
   */
  protected $id;

  /**
   * Label.
   *
   * @var string
   */
  protected $label;

  /**
   * Roles.
   *
   * @param array $roles
   */
  protected $roles;

  public function setName($name) {
    // TODO: Implement setName() method.
    return;
  }

  public function getIdentifier() {
    // TODO: Implement getIdentifier() method.
    return $this->id;
  }

  public function getName() {
    // TODO: Implement getName() method.
    return $this->label;
  }

  public function isConfidential() {
    // TODO: Implement isConfidential() method.
    return FALSE;
  }

  public function getDrupalEntity() {
    // TODO: Implement getDrupalEntity() method.
    return $this;
  }

  public function getRedirectUri() {
    // TODO: Implement getRedirectUri() method.
    return '';
  }

}
