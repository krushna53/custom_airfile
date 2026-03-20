<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.participant_finder
 */
class CRM_CustomAirfile_Service_ParticipantFinder extends AutoService {

  public function findParticipant($participantId, $surname) {

    try {

      // Load participant
      $participant = civicrm_api4('Participant', 'get', [
        'select' => ['id', 'contact_id', 'event_id'],
        'where' => [['id', '=', $participantId]]
      ]);
      if ($participant->rowCount == 0) {
        print_r('enasd');die('enndwron');
        return [
          'success' => false,
          'error' => "Participant {$participantId} not found"
        ];
      }
      $participant = $participant[0];

      $contactId = $participant['contact_id'];

      // Load contact to check surname
      $contact = civicrm_api4('Contact', 'get', [
        'select' => ['id', 'last_name'],
        'where' => [['id', '=', $contactId]]
      ]);
      if (empty($contact)) {
        return [
          'success' => false,
          'error' => "Contact {$contactId} not found"
        ];
      }

      $contact = $contact[0];

      // Compare surnames
      if (strcasecmp($contact['last_name'], $surname) !== 0) {
        return [
          'success' => false,
          'error' => "Surname mismatch. Airfile: {$surname}, CRM: {$contact['last_name']}"
        ];
      }

      return [
        'success' => true,
        'participant' => $participant,
        'contact' => $contact
      ];

    } catch (Exception $e) {

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];

    }

  }

}