<?php

class CRM_CustomAirfile_Page_Test extends CRM_Core_Page {

    public function run() {
      custom_airfile_create_airfile_contents_and_upload();
      die('uploaded');
      $loader = \Civi::service('custom_airfile.file_loader');
      $parser = \Civi::service('custom_airfile.parser');
      $importer = \Civi::service('custom_airfile.importer');

      $path = $loader->getFilePath();

      $data = $parser->parse($path);
      $result = $importer->import($data);
      print_r('<pre>');
      print_r($result);
      print_r('</pre>');
      die('ed');
        CRM_Utils_System::civiExit();
      }

      

}


function custom_airfile_create_airfile_contents_and_upload() {
$directory = '/var/www/html/web/sites/default/files/civicrm/custom_airfiles';


$subtype = 'default';

echo "Using subtype: $subtype\n";
print_r('<pre>');
// STEP 2: Loop through files
$files = scandir($directory);
foreach ($files as $fileName) {

  if ($fileName === '.' || $fileName === '..') {
    continue;
  }

  $filePath = $directory . '/' . $fileName;

  if (!file_exists($filePath)) {
    continue;
  }

  echo "Processing: $fileName\n";

  // STEP 3: Upload file to CiviCRM
  try {
    $file = civicrm_api4('File', 'create', [
      'checkPermissions' => FALSE,
      'values' => [
        'name' => $fileName,
        'mime_type' => 'text/plain',
        'uri' => $filePath,
      ],
    ]);

    $fileId = $file[0]['id'];
print_r($fileId);

    // STEP 4: Create Airfile entity
    $airfile = civicrm_api4('Eck_Airfile', 'create', [
      'checkPermissions' => FALSE,
      'values' => [
        'title' => 'Imported ' . $fileName,
        'subtype' => 1,//$subtype,
        'Airfile.Airfile_Upload' => $fileId,
      ],
    ]);
    print_r($airfile);
    echo "Created Airfile ID: " . $airfile[0]['id'] . "\n\n";

  } catch (Exception $e) {
    echo "Error with $fileName: " . $e->getMessage() . "\n";
  }
}

}