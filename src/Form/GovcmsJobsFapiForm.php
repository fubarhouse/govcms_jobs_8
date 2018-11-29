<?php

namespace Drupal\govcms_jobs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\govcms_jobs\GovcmsJobsApiClient;
use Drupal\Component\Render\FormattableMarkup;

class GovcmsJobsFapiForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'govcms_jobs_fapi';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['govcms_jobs_fapi']['#markup'] = '<h2>Sync and author jobs from APS API</h2><p>Setting account for login APS API at <a href="/admin/structure/govcms_jobs/settings">Govcms Jobs Settings</a></p>';

    $form['step1'] = [
      '#type' => 'details',
      '#title' => 'Step1: Get taxonomy',
    ];

    $form['step1']['get_taxonomy'] = [
      '#type' => 'submit',
      '#value' => 'Get taxonomy',
      '#submit' => ['::getTaxonomyFormSubmit'],
    ];

    $form['step2'] = [
      '#type' => 'details',
      '#title' => 'Step 2: Get agency',
    ];

    $form['step2']['get_agency'] = [
      '#type' => 'submit',
      '#value' => 'Get agency',
      '#submit' => ['::getAgencyFormSubmit'],
    ];

    $form['step3'] = [
      '#type' => 'details',
      '#title' => 'Step 3: Get job',
    ];

    $form['step3']['get_job'] = [
      '#type' => 'submit',
      '#value' => 'Get job',
      '#submit' => ['::getJobFormSubmit'],
    ];

    $options = $this->getJobsDataFetched();

    $header = [
      'aps_id' => $this->t('APS ID'),
      'title' => $this->t('Job Title'),
      'operation' => $this->t('Operation'),
    ];

    $form['step3']['list_job_data'] = [
      '#type' => 'table',
      '#title' => $this->t('Jobs Data fetched from APS API'),
      '#header' => $header,
      '#rows' => $options,
      '#empty' => $this->t('No jobs were fetched'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Implements submit callback for Get Taxonomy button.
   */
  public function getTaxonomyFormSubmit(array &$form, FormStateInterface $form_state) {
    $config = $this->config('govcms_jobs.settings');
    $client = new GovcmsJobsApiClient($config->get('api.base_uri'), $config->get('api.username'), $config->get('api.password'), $config->get('api.authorization'));
    $data = $client->getTaxonomies();

    if ($data === FALSE) {
      return;
    }

    foreach ($data as $aps_voc => $terms) {
      $mapped_voc = $this->getMappedVocabulary($aps_voc);
      if (!empty($mapped_voc)) {
        foreach ($terms as $value) {
          $this->createTaxonomyTerm($mapped_voc, $value);
        }
      }
    }

    $this->messenger()->addMessage('Done');
  }

  /**
   * Implements submit callback for Get Agency button.
   */
  public function getAgencyFormSubmit(array &$form, FormStateInterface $form_state) {
    $config = $this->config('govcms_jobs.settings');
    $client = new GovcmsJobsApiClient($config->get('api.base_uri'), $config->get('api.username'), $config->get('api.password'));
    $data = $client->getAgencies();

    if ($data === FALSE) {
      return;
    }

    foreach ($data->agencies as $value) {
      if (!$this->isFetched('agecny', $value->nid)) {
        $this->addMappingData('agency', $value->nid, \json_encode($value));
      }
    }

    $this->messenger()->addMessage('Done');
  }

  /**
   * Implements submit callback for Get Job button.
   */
  public function getJobFormSubmit(array &$form, FormStateInterface $form_state) {
    $config = $this->config('govcms_jobs.settings');
    $client = new GovcmsJobsApiClient($config->get('api.base_uri'), $config->get('api.username'), $config->get('api.password'));
    $data = $client->getVacancies();

    if ($data === FALSE) {
      return;
    }

    foreach ($data->vacancies as $value) {
      if (!$this->isFetched('job', $value->vacancy_id)) {
        $this->addMappingData('job', $value->vacancy_id, \json_encode($value));
      }
    }

    $this->messenger()->addMessage('Done');
  }

  private function createTaxonomyTerm($vocabulary, $value) {
    if (!$this->getMappedId('taxonomy', $value->tid)) {
      $term = Term::create([
        'name' => $value->name,
        'vid' => $vocabulary,
      ]);
      try {
        $term->save();
        $this->addMappingData('taxonomy', $value->tid, \json_encode($value), $term->id());
      }
      catch (\Exception $e) {
        $this->messenger()->addError($e->getMessage);
      }
    }
  }

  private function getMappedVocabulary($aps_voc) {
    $map = [
      'aps_job_levels' => 'job_levels',
      'aps_job_categories' => 'job_categories',
      'aps_engagement_types' => 'engagement_types',
      'aps_working_hours' => 'working_hours',
      'aps_clearance_levels' => 'clearance_levels',
      'position_initiatives_and_programs' => 'position_initiatives_and_program',
    ];
    return isset($map[$aps_voc]) ? $map[$aps_voc] : NULL;
  }

  private function getMappedId($type, $aps_id) {
    $connection = \Drupal::database();
    $query = $connection->select('govcms_jobs_mapping', 'm')
      ->condition('type', $type)
      ->condition('aps_id', $aps_id)
      ->fields('m', array('current_id'));
    $current_id = $query->execute()->fetchField();
    return $current_id;
  }

  private function isFetched($type, $aps_id) {
    $connection = \Drupal::database();
    $query = $connection->select('govcms_jobs_mapping', 'm')
      ->condition('type', $type)
      ->condition('aps_id', $aps_id)
      ->fields('m', array('id'));
    $id = $query->execute()->fetchField();
    return $id;
  }

  private function addMappingData($type, $aps_id, $data, $current_id = NULL) {
    $status = $current_id ? 1 : 0;
    $entry = \compact('type', 'aps_id', 'data', 'current_id', 'status');
    $connection = \Drupal::database();
    $connection->insert('govcms_jobs_mapping')->fields($entry)->execute();
  }

  private function getJobsDataFetched() {
    $connection = \Drupal::database();
    $query = $connection->select('govcms_jobs_mapping', 'm')
      ->condition('type', 'job')
      ->fields('m');
    $rows = $query->execute();
    $fetched_data = [];
    foreach ($rows as $value) {
      $value_data = \json_decode($value->data);
      if ($value->current_id) {
        $operation = new FormattableMarkup(
          '<a href=":link">@name</a>',
          [':link' => '/node/' . $value->current_id, '@name' => 'View Job']
        );
      }
      else {
        $operation = new FormattableMarkup(
          '<a href=":link">@name</a>',
          [':link' => '/node/add/govcms_jobs?apsid=' . $value->aps_id, '@name' => 'Create Job']
        );
      }
      $fetched_data[] = [
        'aps_id' => $value->aps_id,
        'title' => $value_data->job_title,
        'operation' => [
          'data' => $operation
        ],
      ];
    }
    return $fetched_data;
  }
}
