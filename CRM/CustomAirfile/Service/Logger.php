<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.logger
 */
class CRM_CustomAirfile_Service_Logger extends AutoService {

  public function logImportResult($results) {

    $message = "Airfile Import Results\n";

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

    civicrm_api4('UserJob', 'create', [
      'checkPermissions' => FALSE,
      'values' => [
        'name' => 'Airfile Import',
        'description' => $message,
        'status_id' => 'Completed'
      ]
    ]);

  }

}
