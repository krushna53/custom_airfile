<?php

namespace Civi\Api4\Action\Participant;

use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;

class AirfileImportRun extends AbstractAction {


  protected $where = [];
  public function _run(Result $result) {
    $processedCount = 0;
    $skipped = 0;
    $errors = 0;
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
       $errors++;
       throw new \API_Exception('No airfile_id provided');
     }

    if (!$airfileIds) {
      $errors++;
      throw new \API_Exception('Missing airfile_id');
    }

    foreach ($airfileIds as $airfileId) {
      if (empty($airfileId)) {
        $errors++;
        throw new \API_Exception("Airfile {$airfileId} not found");
      }
      try {
        // your logic here
        $processedCount++;
        $loader = \Civi::service('custom_airfile.file_loader');
        $file_data = $loader->getFileData($airfileId);
        $parser = \Civi::service('custom_airfile.parser');
        $file_array_data = $parser->parse($file_data);
        $importer = \Civi::service('custom_airfile.importer');
        $results = $importer->import($file_array_data);
        $logger = \Civi::service('custom_airfile.logger');
        // Log import results @todo: Maybe we can use this logger on per entry instead of per participant
        $logger->logImportResult($results, $file_array_data);
      }
      catch (\Exception $e) {
        $errors++;
      }
      
    
    }

    return [
      'count' => $processedCount,
      'skipped' => $skipped,
      'errors' => $errors
    ];
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