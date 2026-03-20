<?php

class CRM_CustomAirfile_Page_Test extends CRM_Core_Page {

    public function run() {
      $loader = \Civi::service('custom_airfile.file_loader');
      $parser = \Civi::service('custom_airfile.parser');
      $importer = \Civi::service('custom_airfile.importer');

      $path = $loader->getFilePath();

      $data = $parser->parse($path);
      $result = $importer->import($data);
      print_r('<pre>');
      print_r($result);
      print_r('</pre>');
        CRM_Utils_System::civiExit();
      }

}