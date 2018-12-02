<?php

namespace Drupal\govcms_jobs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\govcms_jobs\GovcmsJobsApiClient;

/**
 * Class GovcmsJobsSettingsForm.
 */
class GovcmsJobsSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'govcms_jobs_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'govcms_jobs.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('govcms_jobs.settings');

    $form['govcms_jobs_settings']['#markup'] = 'Config account for APSjobs API. <p>Sync job and related data at <a href="/admin/structure/govcms_jobs/fapi">Govcms Jobs FAPI page</a></p>';

    $form['base_uri'] = [
      '#type' => 'textfield',
      '#title' => 'Base URI',
      '#default_value' => $config->get('api.base_uri', 'https://development.apsjobs.gov.au'),
      '#required' => TRUE,
    ];

    $form['basic_auth_username'] = [
      '#type' => 'textfield',
      '#title' => 'Basic Authentication User Name',
      '#description' => 'Leave it empty if API not require basic authentication.',
      '#default_value' => $config->get('api.basic_auth_username', ''),
    ];

    $form['basic_auth_password'] = [
      '#type' => 'textfield',
      '#title' => 'Basic Authentication Password',
      '#description' => 'Leave it empty if API not require basic authentication.',
      '#default_value' => $config->get('api.basic_auth_password', ''),
    ];

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => 'User Name',
      '#default_value' => $config->get('api.username', ''),
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#type' => 'textfield',
      '#title' => 'Password',
      '#default_value' => $config->get('api.password', ''),
      '#required' => TRUE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $base_uri = $form_state->getValue('base_uri');
    $basic_auth_username = $form_state->getValue('basic_auth_username');
    $basic_auth_password = $form_state->getValue('basic_auth_password');
    $authorization = NULL;
    if (!empty($basic_auth_username)) {
      $authorization = 'Basic ' . \base64_encode($basic_auth_username.':'.$basic_auth_password);
    }
    $username = $form_state->getValue('username');
    $password = $form_state->getValue('password');
    $tempStore = \Drupal::service('user.private_tempstore')->get('govcms_jobs');
    $tempStore->delete('api_token');
    $tempStore->delete('api_cookie');
    $client = new GovcmsJobsApiClient($base_uri, $username, $password, $authorization);
    if (!$login = $client->login()) {
      $form_state->setErrorByName('', $this->t('Can not login to the API. The base uri or username or password or basic auth was incorrect.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('govcms_jobs.settings');
    $config->set('api.base_uri', $form_state->getValue('base_uri'))
    ->set('api.username', $form_state->getValue('username'))
    ->set('api.password', $form_state->getValue('password'));
    $basic_auth_username = $form_state->getValue('basic_auth_username');
    $basic_auth_password = $form_state->getValue('basic_auth_password');
    if (!empty($basic_auth_username)) {
      $config->set('api.basic_auth_username', $form_state->getValue('basic_auth_username'))
      ->set('api.basic_auth_password', $form_state->getValue('basic_auth_password'))
      ->set('api.authorization', 'Basic ' . \base64_encode($basic_auth_username.':'.$basic_auth_password));
    }
    else {
      $config->clear('api.basic_auth_username')
      ->clear('api.basic_auth_password')
      ->clear('api.authorization');
    }
    $config->save();
    parent::submitForm($form, $form_state);
  }

}
