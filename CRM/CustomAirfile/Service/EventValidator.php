<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.event_validator
 */
class CRM_CustomAirfile_Service_EventValidator extends AutoService {

  public function validateEvent($eventId) {

    try {

      $event = civicrm_api4('Event', 'get', [
        'select' => ['id', 'title', 'end_date', 'is_active'],
        'where' => [['id', '=', $eventId]]
      ]);

      if (empty($event)) {
        return [
          'success' => false,
          'error' => "Event {$eventId} not found"
        ];
      }

      $event = $event[0];

      // Check active flag
      if (!$event['is_active']) {
        return [
          'success' => false,
          'error' => "Event {$eventId} is not active"
        ];
      }

      // Check end date
      if (!empty($event['end_date'])) {

        $now = new DateTime();
        $end = new DateTime($event['end_date']);

        if ($end < $now) {
          return [
            'success' => false,
            'error' => "Event {$eventId} already ended"
          ];
        }

      }

      return [
        'success' => true,
        'event' => $event
      ];

    }
    catch (Exception $e) {

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];

    }

  }

}