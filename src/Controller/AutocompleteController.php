<?php

namespace Drupal\govcms_jobs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;

/**
 * Defines a route controller for entity autocomplete form elements.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $field_name, $count) {
    $results = [];

    if ($input = $request->query->get('q')) {
      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));

      if (!empty($typed_string)) {
        switch ($field_name) {
          case 'locations':
            $results = $this->getLocations($typed_string, $count);
            break;

          default:
            break;
        }
      }
    }

    return new JsonResponse($results);
  }

  public function getLocations($typed_string, $count) {
    $results = [];
    $connection = \Drupal::database();
    $locations = $connection->select('govcms_jobs_location', 'l')
    ->condition('title', '%' . $typed_string . '%', 'LIKE')
    ->range(0, $count)
    ->fields('l', array('title'))
    ->execute()
    ->fetchCol();
    foreach ($locations as $location) {
      $results[] = [
        'value' => \strtoupper($location),
        'label' => \strtoupper($location),
      ];
    }
    return $results;
  }

}
