<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.logger
 */
class CRM_CustomAirfile_Service_Logger extends AutoService {

  public function logImportResult($results, $file_array_data) {
    $message_new = '';
    $participantCount = 0;
    $legCount = 0;

    if (!empty($file_array_data['participants'])) {
      $participantCount = count($file_array_data['participants']);

      foreach ($file_array_data['participants'] as $participant) {
        if (!empty($participant['travel_legs'])) {
          $legCount += count($participant['travel_legs']);
        }
      }

      $eventRef = $file_array_data['event_ref'] ?? 'N/A';
      $bookingRef = $file_array_data['booking_ref'] ?? 'N/A';

      $message_new = "Airfile processed successfully for Event {$eventRef} (Booking: {$bookingRef}). {$participantCount} participant(s) and {$legCount} travel leg(s) imported.";
    } else {
      $message_new = "Airfile processed, but no participants found.";
    }
    $message = "Airfile Import Results\n";
    $message .= $message_new; 
    if (!empty($results['success'])) {
      $message .= "\nSUCCESS:\n";
      foreach ($results['success'] as $row) {
        $message .= "- " . $row . "\n";
      }
    }

    if (!empty($results['errors'])) {
      $message .= "\nERRORS:\n";
      foreach ($results['errors'] as $row) {
        $message .= "- " . $row . "\n";
      }
    }
    $metadata = [
      'event_ref' => $file_array_data['event_ref'] ?? null,
      'booking_ref' => $file_array_data['booking_ref'] ?? null,
      'participants_count' => $participantCount ?? 0,
      'travel_legs_count' => $legCount ?? 0,
      'processed_at' => date('Y-m-d H:i:s'),
    ];
    $user_job_log = civicrm_api4('UserJob', 'create', [
      'checkPermissions' => FALSE,
      'values' => [
        'name' => 'airfile_import_' . time(),
        'label' => 'Airfile Import',
        'job_type' => 'import_generic',
        'status_id' => 1,
        'description' => $message,
        'metadata' => $metadata,
        'start_date' => date('Y-m-d H:i:s'),
        'end_date' => date('Y-m-d H:i:s'),
      ]
    ]);

  }

}
