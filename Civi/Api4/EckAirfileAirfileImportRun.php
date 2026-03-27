<?php

namespace Civi\Api4;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;


class EckAirfileAirfileImportRun extends AbstractAction {

  public function _run(Result $result) {

    $output = [
      'success' => [],
      'errors' => [],
    ];

    $ids = $this->getIds();

    if (empty($ids)) {
      throw new \Exception('No Airfiles selected');
    }

    $parser = \Civi::service('custom_airfile.parser');
    $importer = \Civi::service('custom_airfile.importer');

    foreach ($ids as $airfileId) {

      try {

        $airfile = civicrm_api4('Eck_Airfile', 'get', [
          'select' => ['id', 'Airfile.Airfile_Upload'],
          'where' => [['id', '=', $airfileId]],
        ]);

        if (empty($airfile)) {
          $output['errors'][] = "Airfile {$airfileId} not found";
          continue;
        }

        $fileId = $airfile[0]['Airfile.Airfile_Upload'];

        if (empty($fileId)) {
          $output['errors'][] = "No file attached to Airfile {$airfileId}";
          continue;
        }

        $file = civicrm_api4('File', 'get', [
          'select' => ['uri'],
          'where' => [['id', '=', $fileId]],
        ]);

        $filePath = $file[0]['uri'];

        if (!file_exists($filePath)) {
          $output['errors'][] = "File not found: {$filePath}";
          continue;
        }

        $parsed = $parser->parse($filePath);
        $importResult = $importer->import($parsed);

        $output['success'] = array_merge($output['success'], $importResult['success'] ?? []);
        $output['errors'] = array_merge($output['errors'], $importResult['errors'] ?? []);

      } catch (\Exception $e) {
        $output['errors'][] = "Airfile {$airfileId}: " . $e->getMessage();
      }
    }

    return $output;
  }
}