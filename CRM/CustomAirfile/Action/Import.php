<?php

class CRM_CustomAirfile_Action_Import {

  /**
   * Main handler called by SearchKit
   */
  public static function run($ids, $params = []) {

    $success = 0;
    $failed = 0;
    $skipped = 0;
    $details = [];

    $importer = \Civi::service('custom_airfile.importer');
    $parser = \Civi::service('custom_airfile.parser');
    $logger = \Civi::service('custom_airfile.logger');
    $fileLoader = \Civi::service('custom_airfile.file_loader');

    foreach ($ids as $id) {
      try {

        $airfile = civicrm_api4('Eck_Airfile', 'get', [
          'where' => [['id', '=', $id]],
          'limit' => 1,
        ]);

        if (empty($airfile[0])) {
          throw new Exception("Airfile not found");
        }

        $airfile = $airfile[0];

        if (!empty($airfile['status']) && $airfile['status'] === 'completed') {
          $skipped++;
          $details[] = "Airfile {$id}: Already imported";
          continue;
        }

        civicrm_api4('Eck_Airfile', 'update', [
          'where' => [['id', '=', $id]],
          'values' => ['status' => 'processing'],
        ]);

        $logger->log($id, 'started', 'Import started');

        $file = civicrm_api4('EntityFile', 'get', [
          'where' => [['entity_id', '=', $id]],
          'limit' => 1,
        ]);

        if (empty($file[0]['file_id'])) {
          throw new Exception('No file attached');
        }

        $filePath = $fileLoader->getFilePath($file[0]['file_id']);

        $parsed = $parser->parse($filePath);

        if (empty($parsed['participants'])) {
          throw new Exception('No participants found');
        }

        $result = $importer->import($parsed, $id);

        civicrm_api4('Eck_Airfile', 'update', [
          'where' => [['id', '=', $id]],
          'values' => [
            'status' => 'completed',
            'import_message' => 'Imported ' . $result['count'] . ' participants',
          ],
        ]);

        $logger->log($id, 'success', json_encode($result));

        $success++;
        $details[] = "Airfile {$id}: Imported {$result['count']}";

      }
      catch (Exception $e) {

        civicrm_api4('Eck_Airfile', 'update', [
          'where' => [['id', '=', $id]],
          'values' => [
            'status' => 'failed',
            'import_message' => substr($e->getMessage(), 0, 255),
          ],
        ]);

        $logger->log($id, 'error', $e->getMessage());

        $failed++;
        $details[] = "Airfile {$id} FAILED: " . $e->getMessage();
      }
    }

    return [
      'status' => $failed ? 'warning' : 'success',
      'message' => "$success success, $failed failed, $skipped skipped",
      'details' => implode('<br>', $details),
    ];
  }
}