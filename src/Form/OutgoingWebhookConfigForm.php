<?php

namespace Drupal\http_webhooks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\http_webhooks\OutgoingWebhook;

/**
 * Class OutgoingWebhookConfigForm.
 */
class OutgoingWebhookConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'http_webhooks.outgoing_config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'outgoing_webhook_config_form';
  }

  protected function getEventOptions() {
    return OutgoingWebhook::VALID_EVENTS;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('http_webhooks.outgoing_config');
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL of the webhook'),
      '#description' => $this->t('The URL to make the POST request'),
      '#default_value' => $config->get('http_webhooks.outgoing.url'),
    ];
    $form['secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret'),
      '#description' => $this->t('The secret'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('http_webhooks.outgoing.secret'),
    ];
    $form['events'] = [
      '#type' => 'tableselect',
      '#header' => array('type' => 'Entity Type' , 'event' => 'Event'),
      '#description' => $this->t("The events that will trigger this webhook."),
      '#options' => $this->getEventOptions(),
    ];
    $form['events']['#default_value'] = ($config->isNew()
      ? []
      : $this->deSerializeEvents($config->get('http_webhooks.outgoing.events'))
    );
    return parent::buildForm($form, $form_state);
  }

  protected function serializeEvents($events) {
    return array_filter(array_values($events));
  }

  protected function deSerializeEvents($events) {
    $options = $this->getEventOptions();
    $result = \array_reduce(array_keys($options), function($carry, $key) use($events) {
      if (in_array($key, $events)) {
        $carry[$key] = $key;
      } else {
        $carry[$key] = 0;
      }
      return $carry;
    }, []);
    $result = (object) $result;
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $serializedEvents = $this->serializeEvents($form_state->getValue('events'));

    $this->config('http_webhooks.outgoing_config')
      ->set('http_webhooks.outgoing.secret', $form_state->getValue('secret'))
      ->set('http_webhooks.outgoing.url', $form_state->getValue('url'))
      ->set('http_webhooks.outgoing.events', $serializedEvents)
      ->save();
  }

}
