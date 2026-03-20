<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.validator
 */
class CRM_CustomAirfile_Service_Validator extends AutoService {

  public function validate(array $parsed): array {

    $errors = [];

    if (empty($parsed['event_ref'])) {
      $errors[] = 'Missing event reference (RM*REF)';
    }

    if (empty($parsed['participants'])) {
      $errors[] = 'No participants found in airfile';
      return $errors;
    }

    foreach ($parsed['participants'] as $index => $participant) {

      if (empty($participant['first_name']) || empty($participant['last_name'])) {
        $errors[] = "Participant {$index} has missing name";
      }

      if (!isset($participant['participant_id'])) {
        $errors[] = "Participant {$index} missing participant ID (RM*ID)";
      }

      if (!is_numeric($participant['rate'])) {
        $errors[] = "Participant {$index} has invalid rate";
      }

      if (empty($participant['travel_legs'])) {
        $errors[] = "Participant {$index} has no travel legs";
      }

    }

    return $errors;
  }

}
