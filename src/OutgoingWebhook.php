<?php

namespace Drupal\http_webhooks;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Serialization\SerializationInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class OutgoingWebhook.
 */
class OutgoingWebhook {

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
  }

}
