<?php

namespace Civi\Api4\Action\Participant;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

class AirfileImportRun extends AbstractAction {


  protected $where = [];
  public function _run(Result $result) {
     // 👇 Extract Airfile IDs from where clause
     $airfileIds = [];
     foreach ($this->where as $condition) {
       if ($condition[0] === 'entity_id') {
         if ($condition[1] === '=') {
           $airfileIds[] = $condition[2];
         }
         elseif ($condition[1] === 'IN') {
           $airfileIds = array_merge($airfileIds, $condition[2]);
         }
       }
     }
 
     if (empty($airfileIds)) {
       throw new \API_Exception('No airfile_id provided');
     }

    if (!$airfileIds) {
      throw new \API_Exception('Missing airfile_id');
    }

    foreach ($airfileIds as $airfileId) {
      if (empty($airfileId)) {
        throw new \API_Exception("Airfile {$airfileId} not found");
      }
      $loader = \Civi::service('custom_airfile.file_loader');
      $file_data = $loader->getFileData($airfileId);
      $parser = \Civi::service('custom_airfile.parser');
      $file_array_data = $parser->parse($file_data);
      $importer = \Civi::service('custom_airfile.importer');
      $results = $importer->import($file_array_data);
      print_r($results);
      die();
     // $logger->logImportResult($results);
    }

    return [];
  }
  /*
   * Define parameters for this API
   */


  private function parseFile($fileId) {
    // TODO: parse CSV/XML
    return [];
  }

  private function isEventActive($eventId) {
    return TRUE;
  }
}