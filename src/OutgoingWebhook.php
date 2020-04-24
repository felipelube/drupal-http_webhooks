<?php

namespace Drupal\http_webhooks;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;

/**
 * Class OutgoingWebhook.
 */
class OutgoingWebhook {
  const EVENT_CREATE = "create";
  const EVENT_UPDATE = "update";
  const EVENT_DELETE = "delete";

  const VALID_EVENTS = [
    'entity:user:create' => ['type' => 'user' , 'event' => 'create'],
    'entity:user:update' => ['type' => 'user' , 'event' => 'update'],
    'entity:user:delete' => ['type' => 'user' , 'event' => 'delete'],
    'entity:node:create' => ['type' => 'node' , 'event' => 'create'],
    'entity:node:update' => ['type' => 'node' , 'event' => 'update'],
    'entity:node:delete' => ['type' => 'node' , 'event' => 'delete'],
    'entity:comment:create' => ['type' => 'comment' , 'event' => 'create'],
    'entity:comment:update' => ['type' => 'comment' , 'event' => 'update'],
    'entity:comment:delete' => ['type' => 'comment' , 'event' => 'delete'],
  ];

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Drupal\Component\Serialization\SerializationInterface definition.
   *
   * @var \Drupal\Component\Serialization\SerializationInterface
   */
  protected $serializationJson;

  /**
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Constructs a new OutgoingWebhook object.
   */
  public function __construct(
    ClientInterface $http_client,
    SerializationInterface $serialization_json,
    ConfigFactoryInterface $config_factory
  ) {
    $this->httpClient = $http_client;
    $this->serializationJson = $serialization_json;
    $this->config = $config_factory->get('http_webhooks.outgoing_config');
  }

  public function handle_event(EntityInterface $entity, $event) {
    $type = $entity->getEntityTypeId();
    $eventString = "entity:$type:$event";
    $allowed_events = $this->config->get("http_webhooks.outgoing.events");

    // only post for entities and events allowed in the configuration
    if (in_array($eventString, $allowed_events)) {
      $this->post();
    };
  }

  public function post() {
    $secret = $this->config->get('http_webhooks.outgoing.secret');
    $url = $this->config->get('http_webhooks.outgoing.url');
    if (empty($secret) || empty($url)) {
      // TODO: log a error message: these configuration are necessary,
      return;
    }

    try {
      $response = $this->httpClient->request('POST', $url, [
        'json' => ['secret'=> $secret]
      ]);
    } catch(RequestException $e) {
      // TODO: log a error message: the request failed
      return;
    }
    $body = $response->getBody();
    // TODO: log a success message with the response payload

  }

}
