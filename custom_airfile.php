<?php
declare(strict_types = 1);

// phpcs:disable PSR1.Files.SideEffects
require_once 'custom_airfile.civix.php';
// phpcs:enable

use CRM_CustomAirfile_ExtensionUtil as E;

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function custom_airfile_civicrm_config(\CRM_Core_Config $config): void {
  _custom_airfile_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function custom_airfile_civicrm_install(): void {

  _custom_airfile_civix_civicrm_install();

  CRM_Core_Session::setStatus(
    'custom_airfile install hook triggered',
    'Airfile Extension',
    'success'
  );

  $group = civicrm_api4('CustomGroup', 'get', [
    'select' => ['id'],
    'where' => [['name', '=', 'travel_details']]
  ]);
 
  if ($group->rowCount == 0) {
    $created = civicrm_api4('CustomGroup', 'create', [
      'checkPermissions' => FALSE,
      'values' => [
        'name' => 'travel_details',
        'title' => 'Travel Details',
        'extends' => 'Participant',
        'is_multiple' => TRUE,
        'style' => 'Inline',
        'is_active' => TRUE
      ]
    ]);

    $groupId = $created[0]['id'];


  } else {
    $groupId = $group[0]['id'];

  }

  $fields = [
    ['event_number','Event Number','String'],
    ['departure_city','Departure City','String'],
    ['arrival_city','Arrival City','String'],
    ['flight_number','Flight Number','String'],
    ['ticket_rate','Ticket Rate','Money'],
    ['booking_reference','Booking Reference','String'],
  
    ['booking_class','Class of Booking','String'],
    ['departure_date','Departure Date','String'],
    ['departure_time','Departure Time','String'],
    ['arrival_date','Arrival Date','String'],
    ['arrival_time','Arrival Time','String'],
  ];
  foreach ($fields as $field) {
    
    $existing = civicrm_api4('CustomField', 'get', [
      'select' => ['id'],
      'where' => [
        ['name', '=', $field[0]],
        ['custom_group_id', '=', $groupId]
      ]
    ]);
    if ($existing->rowCount == 0) {
      $result = civicrm_api4('CustomField', 'create', [
          'checkPermissions' => FALSE,
          'values' => [
            'custom_group_id' => $groupId,
            'name' => $field[0],
            'label' => $field[1],
            'data_type' => $field[2],
            'html_type' => 'Text',
            'is_active' => TRUE
          ]
        ]);
    }
  }
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function custom_airfile_civicrm_enable(): void {
  _custom_airfile_civix_civicrm_enable();

  // Create test event
  $event = civicrm_api4('Event', 'create', [
    'checkPermissions' => FALSE,
    'values' => [
      'title' => 'Airfile Test Event',
      'event_type_id' => 1,
      'is_active' => TRUE,
      'start_date' => date('Y-m-d H:i:s'),
      'end_date' => date('Y-m-d H:i:s', strtotime('+30 days')),
    ]
  ]);
  $eventId = $event[0]['id'];

  // Create test contact
  $contact = civicrm_api4('Contact', 'create', [
    'checkPermissions' => FALSE,
    'values' => [
      'contact_type' => 'Individual',
      'first_name' => 'DAGMAR',
      'last_name' => 'WAEGEMAN'
    ]
  ]);
  $contactId = $contact[0]['id'];

  // Create participant

  $participant = civicrm_api4('Participant', 'get', [
    'checkPermissions' => FALSE,
    'where' => [
      ['id', '=', 40261],
    ],
  ]);

  if ($participant->rowCount == 0) {
    $participant = civicrm_api4('Participant', 'create', [
      'checkPermissions' => FALSE,
      'values' => [
        'contact_id' => $contactId,
        'event_id' => $eventId,
        'status_id' => 1,
        'role_id' => 1
      ]
    ]);

    $participantId = $participant[0]['id'];
      // Force ID to 40261 for testing
      CRM_Core_DAO::executeQuery("
        UPDATE civicrm_participant
        SET id = 40261
        WHERE id = %1
      ", [
        1 => [$participantId, 'Integer']
     ]);
  }


  try {
    // Ensure upload dir exists
    $uploadDir = Civi::paths()->getPath('[civicrm.files]/custom_airfiles');
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, TRUE);
    }
    // Dummy AIRFILE content
    $dummyFiles = [
      'AIRFILE3.txt' => "RM*REF 08SEP\nI-DAGMAR MS WAEGEMAN\nH-003;002OBRU;BRUSSELS;NBO;NAIROBI KENYATTA;SN    0481 N N 17OCT1025 2020 17OCT",
      'AIRFILE4.txt' => "RM*REF 09SEP\nI-JOHN DOE\nH-003;002ONBO;NAIROBI;BRU;BRUSSELS;SN    0491 N N 26OCT2355 0700 27OCT",
    ];

    foreach ($dummyFiles as $filename => $content) {
      $filePath = $uploadDir . '/' . $filename;

      // Write file
      file_put_contents($filePath, $content);

      \Civi::log()->info('Airfile check done');

    }
    \Civi::log()->info('Airfile dummy files created successfully');

  }
  catch (\Exception $e) {
    \Civi::log()->error('Airfile creation failed: ' . $e->getMessage());
  }

  

  CRM_Core_Session::setStatus(
    'Test event and participant (ID 40261) created',
    'Airfile Extension',
    'success'
  );

}

function custom_airfile_civicrm_xmlMenu(&$files) {
  $files[] = __DIR__ . '/xml/Menu/custom_airfile.xml';
}

function custom_airfile_civicrm_searchKitTasks(&$tasks) {
  $tasks['Eck_Airfile']['custom_airfile_import'] = [
    'title' => ts('Airfile Import'),
    'icon' => 'fa-upload',
    'entity' => 'Eck_Airfile',

    'apiBatch' => [
      'action' => 'AirfileImportRun', // your API action
      'params' => [
        'ids' => '$ids',
      ],

      // Optional but prevents crash
      'confirmMsg' => ts('Are you sure you want to import selected Airfiles?'),
    ],

    // Optional UI dialog (nice to have)
    'uiDialog' => [
      'confirm' => TRUE,
    ],

    'successMsg' => ts('Import completed successfully'),
  ];
}

