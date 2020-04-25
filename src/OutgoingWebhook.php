<?php

namespace Drupal\http_webhooks;

use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Utility\Error;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;

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
  * @var \Psr\Log\LoggerInterface
  */
  protected $logger;

  /**
   * Constructs a new OutgoingWebhook object.
   */
  public function __construct(
    ClientInterface $http_client,
    SerializationInterface $serialization_json,
    ConfigFactoryInterface $config_factory,
    LoggerInterface $logger
  ) {
    $this->httpClient = $http_client;
    $this->serializationJson = $serialization_json;
    $this->config = $config_factory->get('http_webhooks.outgoing_config');
    $this->logger = $logger;
  }

  public function handle_event(EntityInterface $entity, $event) {
    $entityType = $entity->getEntityTypeId();
    $eventString = "entity:$entityType:$event";
    $allowedEvents = $this->config->get("http_webhooks.outgoing.events");

    // only post for entities and events allowed in the configuration
    if (in_array($eventString, $allowedEvents)) {
      $this->post($entityType, $event);
    };
  }

  private function post($entityType, $event) {
    $secret = $this->config->get('http_webhooks.outgoing.secret');
    $url = $this->config->get('http_webhooks.outgoing.url');
    if (empty($secret) || empty($url)) {
      $this->logger->critical('Cannot send the webhook since either the secret or the url is undefined.');
      return;
    }

    try {
      $response = $this->httpClient->request('POST', $url, [
        'json' => ['secret'=> $secret]
      ]);
    } catch(RequestException $e) {
      $variables = Error::decodeException($e);
      if ($e instanceof BadResponseException) {
        $this->logger->error('Received error response after sending the webhook: %type: @message in %function (line %line of %file).', $variables);
      } else {
        $this->logger->error('There was an error when trying to send the webhook: %type: @message in %function (line %line of %file).', $variables);
      }
      return;
    }
    $this->logger->info('Webhook sent and acknowledged after %entity_type %event event.', [
      '%event' => $event,
      '%entity_type' => $entityType
    ]);
  }
}
