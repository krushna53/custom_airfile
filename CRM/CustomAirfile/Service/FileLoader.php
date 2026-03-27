<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.file_loader
 */
class CRM_CustomAirfile_Service_FileLoader extends AutoService {

  public function getFilePath($airfileId = NULL) {

    $config = CRM_Core_Config::singleton();

    // Default development file
    //$defaultFile = $config->uploadDir . 'AIRFILE.TXT';
    $defaultFile = "/var/www/html/web/sites/default/files/AIRFILE.TXT";
    return $defaultFile;
    // try {

    //   if (!$airfileId) {
    //     return $defaultFile;
    //   }

    //   $airfile = civicrm_api4('Airfile', 'get', [
    //     'select' => ['id', 'airfile_87'],
    //     'where' => [['id', '=', $airfileId]]
    //   ]);

    //   if (empty($airfile)) {
    //     return $defaultFile;
    //   }

    //   $fileId = $airfile[0]['airfile_87'] ?? NULL;

    //   if (!$fileId) {
    //     return $defaultFile;
    //   }

    //   $file = civicrm_api4('File', 'get', [
    //     'select' => ['uri'],
    //     'where' => [['id', '=', $fileId]]
    //   ]);

    //   if (empty($file)) {
    //     return $defaultFile;
    //   }

    //   $uri = $file[0]['uri'];

    //   $path = $config->customFileUploadDir . $uri;

    //   if (!file_exists($path)) {
    //     return $defaultFile;
    //   }

    //   return $path;

    // }
    // catch (Exception $e) {
    //   return $defaultFile;
    // }

  }

}