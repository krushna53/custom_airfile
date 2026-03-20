<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.importer
 */
class CRM_CustomAirfile_Service_Importer extends AutoService {

  public function import(array $data) {
    //@todo: Make validator and participant finder services work.
    $finder = \Civi::service('custom_airfile.participant_finder');
    $eventValidator = \Civi::service('custom_airfile.event_validator');
    $logger = \Civi::service('custom_airfile.logger');

    $results = [
      'success' => [],
      'errors' => []
    ];
    foreach ($data['participants'] as $participant) {

      // Step 1: Find participant
      $match = $finder->findParticipant(
        $participant['participant_id'],
        $participant['last_name']
      );

      if (!$match['success']) {
        $results['errors'][] = $match['error'];
        continue;
      }

      $participantRecord = $match['participant'];
      // Step 2: Validate event
      $eventCheck = $eventValidator->validateEvent(
        $participantRecord['event_id']
      );

      if (!$eventCheck['success']) {
        $results['errors'][] = $eventCheck['error'];
        continue;
      }

      $event = $eventCheck['event'];

      $eventNumber = $this->generateEventNumber($participantRecord['event_id']);

      // $fieldResolver = \Civi::service('custom_airfile.field_resolver');
      // $eventNumber = $fieldResolver->getFieldKey('event_number');
      // $departureCity = $fieldResolver->getFieldKey('departure_city');
      // $arrivalCity = $fieldResolver->getFieldKey('arrival_city');
      // $flightNumber = $fieldResolver->getFieldKey('flight_number');
      // $ticketRate = $fieldResolver->getFieldKey('ticket_rate');
      // $bookingReference = $fieldResolver->getFieldKey('booking_reference');
      foreach($participant['travel_legs'] as $leg) {
        // STEP 1 — Fetch participant with all custom fields
        $results = civicrm_api4('Custom_travel_details', 'create', [
          'values' => [
            'entity_id' => $participantRecord['id'],
            'event_number' => $eventNumber,
            'departure_city' => $leg['from_name'],
            'arrival_city' => $leg['to_name'],
            'flight_number' => $leg['flight'],
            'ticket_rate' => $participant['rate'],
            'booking_reference' => $leg['booking_reference'],
            'booking_class'  => $leg['booking_class'],
            'departure_date'  => $leg['departure_date'],
            'departure_time'  => $leg['departure_time'],
            'arrival_date' => $leg['arrival_date'],
            'arrival_time' => $leg['arrival_time'],
          ],
          'checkPermissions' => FALSE,
        ]);
        $participantData = $result[0];
      }

    }

    return $results;

  }

  /**
   * Generate Event Number
   */
  private function generateEventNumber($eventId) {

    $month = date('m');
    $year = date('y');

    $eventIdPadded = str_pad($eventId, 4, '0', STR_PAD_LEFT);

    return $month . $year . $eventIdPadded;

  }

}