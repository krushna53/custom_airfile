<?php

class CRM_CustomAirfile_Page_Test extends CRM_Core_Page {

    public function run() {
      _custom_airfile_create_test_data();
      // custom_airfile_create_airfile_contents_and_upload();
      // die('uploaded');
      // $loader = \Civi::service('custom_airfile.file_loader');
      // $parser = \Civi::service('custom_airfile.parser');
      // $importer = \Civi::service('custom_airfile.importer');

      // $path = $loader->getFilePath();

      // $data = $parser->parse($path);
      // $result = $importer->import($data);
      // print_r('<pre>');
      // print_r($result);
      // print_r('</pre>');
      // die('ed');
        CRM_Utils_System::civiExit();
      }

      

}
function _custom_airfile_create_test_data() {

  // Create Event safely
  $eventType = civicrm_api4('OptionValue', 'get', [
    'where' => [['option_group_id:name', '=', 'event_type']],
    'limit' => 1,
  ]);
  if($eventType->count()) {
    $eventTypeId = $eventType[0]['value'];
    $event = civicrm_api4('Event', 'create', [
      'values' => [
        'title' => 'Airfile Test Event',
        'event_type_id' => $eventTypeId,
        'is_active' => TRUE,
        'start_date' => date('Y-m-d H:i:s'),
        'end_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
      ]
    ]);
    $eventId = $event[0]['id'];
    if ($eventId) {
      $existingContact = civicrm_api4('Contact', 'get', [
        'checkPermissions' => FALSE,
        'where' => [
          ['first_name', '=', 'DAGMAR'],
          ['last_name', '=', 'WAEGEMAN'],
          ['contact_type', '=', 'Individual'],
        ],
        'limit' => 1,
      ]);
      if ($existingContact->count() > 0) {
        $contactId = $existingContact->first()['id'];
      } else {
        $contact = civicrm_api4('Contact', 'create', [
          'checkPermissions' => FALSE,
          'values' => [
            'contact_type' => 'Individual',
            'first_name' => 'DAGMAR',
            'last_name' => 'WAEGEMAN',
          ]
        ]);
        $contactId = $contact->first()['id'];
      }
        // Check existing participant
      $existing = civicrm_api4('Participant', 'get', [
        'where' => [
          ['contact_id', '=', $contactId],
          ['event_id', '=', $eventId],
        ],
        'limit' => 1,
      ]);
      $participantId = 0;
      if ($existing->count() == 0) {
       $participant = civicrm_api4('Participant', 'create', [
          'values' => [
            'contact_id' => $contactId,
            'event_id' => $eventId,
            'status_id' => 1,
            'role_id' => 1,
          ]
        ]);
        if ($participant->count() > 0) {
          $participantId = $participant->first()['id'];
        }
        if ($participantId) {
          // --------------------------------------------------
          // 3. Prepare directory safely
          // --------------------------------------------------
          $uploadDir = Civi::paths()->getPath('[civicrm.files]/custom_airfiles');

          if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, TRUE) && !is_dir($uploadDir)) {
              CRM_Core_Session::setStatus(
                'Airfile: Failed to create upload directory.',
                'Airfile Extension',
                'error'
              );
              \Civi::log()->error('Airfile enable failed: directory creation issue');
              return;
            }
          }
          // --------------------------------------------------
          // 4. Create dummy files (safe)
          // --------------------------------------------------
          $uploadDir = Civi::paths()->getPath('[civicrm.files]/custom_airfiles');
          if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, TRUE) && !is_dir($uploadDir)) {
              CRM_Core_Session::setStatus(
                'Airfile: Failed to create upload directory.',
                'Airfile Extension',
                'error'
              );
              \Civi::log()->error('Airfile: directory creation issue');
              return;
            
            }
          }
          $filePath = $uploadDir . '/AIRFILE4.txt';

          $content = "RM*ID/1/{$participantId}\n";
          $content .= "I-DAGMAR MS WAEGEMAN\n";
          $content .= "H-003;002OBRU;BRUSSELS;NBO;NAIROBI KENYATTA;SN 0481 N N 17OCT1025 2020 17OCT";
          
          if (!file_exists($filePath)) {
            file_put_contents($filePath, $content);
          }
          
          // Logging
          \Civi::log()->info("Airfile created for participant ID: {$participantId}");
          
          CRM_Core_Session::setStatus(
            "Airfile created successfully for participant ID: {$participantId}",
            'Airfile Extension',
            'success'
          );
        }
      }
    }
  }    
}

// function custom_airfile_create_airfile_contents_and_upload() {
//   $uploadDir = Civi::paths()->getPath('[civicrm.files]/custom_airfiles');
//   if (!file_exists($uploadDir)) {
//     if (!mkdir($uploadDir, 0777, TRUE) && !is_dir($uploadDir)) {
//       CRM_Core_Session::setStatus(
//         'Airfile: Failed to create upload directory.',
//         'Airfile Extension',
//         'error'
//       );
//       \Civi::log()->error('Airfile: directory creation issue');
//       return;
//     }
//   }
//   $directory = $uploadDir;
//   $subtype = 'default';
//   echo "Using subtype: $subtype\n";
//   // STEP 2: Loop through files
//   $files = scandir($directory);
//   foreach ($files as $fileName) {

//     if ($fileName === '.' || $fileName === '..') {
//       continue;
//     }

//     $filePath = $directory . '/' . $fileName;

//     if (!file_exists($filePath)) {
//       continue;
//     }

//     echo "Processing: $fileName\n";

//     // STEP 3: Upload file to CiviCRM
//     try {
//       $file = civicrm_api4('File', 'create', [
//         'checkPermissions' => FALSE,
//         'values' => [
//           'name' => $fileName,
//           'mime_type' => 'text/plain',
//           'uri' => $filePath,
//         ],
//       ]);

//       $fileId = $file[0]['id'];
//   print_r($fileId);

//       // STEP 4: Create Airfile entity
//       $airfile = civicrm_api4('Eck_Airfile', 'create', [
//         'checkPermissions' => FALSE,
//         'values' => [
//           'title' => 'Imported ' . $fileName,
//           'subtype' => 1,//$subtype,
//           'Airfile.Airfile_Upload' => $fileId,
//         ],
//       ]);
//       print_r($airfile);
//       echo "Created Airfile ID: " . $airfile[0]['id'] . "\n\n";

//     } catch (Exception $e) {
//       echo "Error with $fileName: " . $e->getMessage() . "\n";
//     }
//   }
// }

// function custom_airfile_process_all() {
//   print_r('<pre>');
//   try {

//     // STEP 1: Fetch all Airfiles with file details
//     $airfiles = civicrm_api4('Eck_Airfile', 'get', [
//       'checkPermissions' => FALSE,
//       'select' => [
//         'id',
//         'title',
//         'subtype',
//         'created_date',

//         // File field + metadata
//         'Airfile.Airfile_Upload',
//         'Airfile.Airfile_Upload.id',
//         'Airfile.Airfile_Upload.file_name',
//         'Airfile.Airfile_Upload.uri',
//         'Airfile.Airfile_Upload.mime_type',
//         'Airfile.Airfile_Upload.upload_date',
//       ],

//       // Optional filters (uncomment if needed)
//       // 'where' => [
//       //   ['subtype', '=', 1],
//       //   ['Airfile.Airfile_Upload', 'IS NOT NULL'],
//       // ],
//     ]);

//     if ($airfiles->rowCount == 0) {
//       \Civi::log()->info('No Airfiles found.');
//       return;
//     }

//     // STEP 2: Loop through records
//     foreach ($airfiles as $row) {

//       $airfileId = $row['id'];
//       $title = $row['title'] ?? '';
//       $fileId = $row['Airfile.Airfile_Upload'] ?? NULL;
//       $fileName = $row['Airfile.Airfile_Upload.file_name'] ?? '';
//       $fileUri = $row['Airfile.Airfile_Upload.uri'] ?? NULL;

//       echo "=============================\n";
//       echo "Airfile ID: {$airfileId}\n";
//       echo "Title: {$title}\n";
//       echo "File ID: {$fileId}\n";
//       echo "File Name: {$fileName}\n";
//       echo "File URI: {$fileUri}\n";

//       // STEP 3: Validate file
//       if (!$fileUri) {
//         echo "⚠ No file attached\n\n";
//         continue;
//       }
//       print_r($row);
//       if (!file_exists($fileUri)) {
//         echo "⚠ File not found on disk\n\n";
//         continue;
//       }

//       // STEP 4: Read file content
//       $content = file_get_contents($fileUri);

//       echo "---- FILE CONTENT ----\n";
//       echo $content . "\n";

//       // --------------------------------------------------
//       // STEP 5: (Optional) Parse AIRFILE content
//       // --------------------------------------------------

//       $lines = explode("\n", $content);

//       $participantId = NULL;
//       $passengerName = NULL;
//       $flightData = NULL;

//       foreach ($lines as $line) {

//         $line = trim($line);

//         // RM*ID/1/40261
//         if (strpos($line, 'RM*ID') === 0) {
//           $parts = explode('/', $line);
//           $participantId = $parts[2] ?? NULL;
//         }

//         // I-DAGMAR MS WAEGEMAN
//         if (strpos($line, 'I-') === 0) {
//           $passengerName = substr($line, 2);
//         }

//         // H-003;002OBRU;...
//         if (strpos($line, 'H-') === 0) {
//           $flightData = substr($line, 2);
//         }
//       }

//       echo "Parsed Participant ID: {$participantId}\n";
//       echo "Passenger: {$passengerName}\n";
//       echo "Flight Data: {$flightData}\n";

//       // --------------------------------------------------
//       // STEP 6: (Optional) Update participant / custom fields
//       // --------------------------------------------------

//       if ($participantId) {
//         try {
//           civicrm_api4('Participant', 'update', [
//             'checkPermissions' => FALSE,
//             'values' => [
//               'id' => $participantId,
//               // Example custom field mapping
//               // 'travel_details.flight_number' => 'XYZ123',
//             ],
//           ]);

//           echo "✔ Participant updated\n";

//         } catch (Exception $e) {
//           echo "❌ Failed to update participant: " . $e->getMessage() . "\n";
//         }
//       }

//       echo "=============================\n\n";
//     }

//   } catch (Exception $e) {
//     \Civi::log()->error('Airfile processing failed: ' . $e->getMessage());
//     echo "Global Error: " . $e->getMessage();
//   }
// }