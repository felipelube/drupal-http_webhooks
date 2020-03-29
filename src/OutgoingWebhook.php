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
  const EVENT_CREATED = "created";
  const EVENT_UPDATED = "updated";
  const EVENT_DELETED = "deleted";

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
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

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
    $this->configFactory = $config_factory;
  }

  public function handle_event(EntityInterface $entity, $event) {
    // TODO: only post for entities and events allowed in configuration
    $this->post();
  }

  public function post() {
    $config = $this->configFactory->get('http_webhooks.outgoing_config');
    $secret = $config->get('http_webhooks.outgoing.secret');
    $url = $config->get('http_webhooks.outgoing.url');
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
