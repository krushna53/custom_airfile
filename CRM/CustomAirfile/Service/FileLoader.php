<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.file_loader
 */
class CRM_CustomAirfile_Service_FileLoader extends AutoService {

  public function getFileData($airfileId = NULL) {
    $config = CRM_Core_Config::singleton();
    $airfile = civicrm_api4('Eck_Airfile', 'get', [
      'select' => [
        'Airfile.Airfile_Upload.url',
      ],
      'where' => [
        ['id', '=', $airfileId],
      ],
      'limit' => 26,
      'checkPermissions' => TRUE,
    ]);
    $airfile_url = $airfile[0]['Airfile.Airfile_Upload.url'];
    $domain_url = \CRM_Utils_System::url('', '', true);
    $url = $domain_url . $airfile_url;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // If authentication is needed (logged-in session)
    if (!empty($_SERVER['HTTP_COOKIE'])) {
      curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);
    }
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

}