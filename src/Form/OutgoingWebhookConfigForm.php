<?php

namespace Drupal\http_webhooks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('http_webhooks.outgoing_config');
    $form['secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret'),
      '#description' => $this->t('The secret'),
      '#maxlength' => 255,
      '#size' => 64,
      '#default_value' => $config->get('http_webhooks.outgoing.secret'),
    ];
    $form['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL of the webhook'),
      '#description' => $this->t('The URL to make the POST request'),
      '#default_value' => $config->get('http_webhooks.outgoing.url'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('http_webhooks.outgoing_config')
      ->set('http_webhooks.outgoing.secret', $form_state->getValue('secret'))
      ->set('http_webhooks.outgoing.url', $form_state->getValue('url'))
      ->save();
  }

}
