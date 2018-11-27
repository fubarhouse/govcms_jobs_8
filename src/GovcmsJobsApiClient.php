<?php

namespace Drupal\govcms_jobs;

use GuzzleHttp\Client;

class GovcmsJobsApiClient {

  private $username;

  private $password;

  private $client;

  private $token;

  private $cookie;

  private $tempStore;

  public function __construct($base_uri, $username, $password, $authorization = NULL) {
    $this->tempStore = \Drupal::service('user.private_tempstore')->get('govcms_jobs');
    $this->username = $username;
    $this->password = $password;
    $headers = ['Content-Type' => 'application/json'];
    if ($authorization) {
      $headers ['Authorization'] = $authorization;
    }
    $this->client = new Client([
      'base_uri' => $base_uri,
      'headers' => $headers,
    ]);
  }

  public function login() {
    $token = $this->tempStore->get('api_token', '');
    $cookie = $this->tempStore->get('api_cookie', '');
    if (!empty($token) && !empty($cookie)) {
      $this->token = $token;
      $this->cookie = $cookie;
      return TRUE;
    }

    $account_info = sprintf('{
      "username": "%s",
      "password": "%s"
    }', $this->username, $this->password);
    try {
      $response = $this->client->request('POST', '/api/user/login', ['body' => $account_info]);
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
      return FALSE;
    }


    if ($response->getStatusCode() == '200') {
      $body = json_decode((string)$response->getBody());
      if (isset($body->token)) {
        $this->tempStore->set('api_token', $body->token);
        $this->token = $body->token;
        $cookies = $response->getHeader('Set-Cookie');
        if (isset($cookies[1])) {
          $this->tempStore->set('api_cookie', $cookies[1]);
          $this->cookie = $cookies[1];
        }
        return TRUE;
      }
    }

    return FALSE;
  }

  public function logout() {
    try {
      $response = $this->client->request('POST', '/api/user/logout',
        [
          'headers' => [
            'X-CSRF-Token' => $this->token,
            'cookie' => $this->cookie,
          ],
        ]
      );

      \Drupal::messenger()->addMessage((string)$response->getBody());
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
    }
    $this->tempStore->delete('api_token');
    $this->tempStore->delete('api_cookie');
    $this->token = NULL;
    $this->cookie = NULL;
  }

  public function getVacancy($nid) {
    if (!$this->login()) {
      \Drupal::messenger()->addError('Can not login to APS API.');
      return FALSE;
    }
    $path = '/api/vacancy/' . $nid;
    try {
      $response = $this->client->request('GET', $path, [
        'headers' => [
          'X-CSRF-Token' => $this->token,
          'cookie' => $this->cookie,
        ]]
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
      return FALSE;
    }
    if ($response->getStatusCode() == '200') {
      $vacancy = json_decode((string)$response->getBody());
      return $vacancy;
    }
    return [];
  }

  public function getVacancies($offset = NULL, $limit = NULL, $options = NULL) {
    if (!$this->login()) {
      \Drupal::messenger()->addError('Can not login to APS API.');
      return FALSE;
    }
    $path = '/api/vacancy/';
    try {
      $response = $this->client->request('GET', $path, [
        'headers' => [
          'X-CSRF-Token' => $this->token,
          'cookie' => $this->cookie,
        ]]
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
      return FALSE;
    }
    if ($response->getStatusCode() == '200') {
      $vacancy = json_decode((string)$response->getBody());
      return $vacancy;
    }
    return [];
  }

  public function getAgencies($offset = NULL, $limit = NULL, $options = NULL) {
    if (!$this->login()) {
      \Drupal::messenger()->addError('Can not login to APS API.');
      return FALSE;
    }
    $path = '/api/agency';
    try {
      $response = $this->client->request('GET', $path, [
        'headers' => [
          'X-CSRF-Token' => $this->token,
          'cookie' => $this->cookie,
        ]]
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
      return FALSE;
    }
    if ($response->getStatusCode() == '200') {
      $body = json_decode((string)$response->getBody());
      return $body;
    }
    return [];
  }

  public function getTaxonomies() {
    if (!$this->login()) {
      \Drupal::messenger()->addError('Can not login to APS API.');
      return FALSE;
    }
    $path = '/api/get_taxonomies/?machine_name=*';
    try {
      $response = $this->client->request('GET', $path, [
        'headers' => [
          'X-CSRF-Token' => $this->token,
          'cookie' => $this->cookie,
        ]]
      );
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($e->getMessage());
      return FALSE;
    }
    if ($response->getStatusCode() == '200') {
      $taxonomies = json_decode((string)$response->getBody());
      return $taxonomies;
    }
    return [];
  }

}
