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
  
  if ($group->count() == 0) {
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
    if ($existing->count() == 0) {
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

  // --------------------------------------------------
  // 1. Check Event Types
  // --------------------------------------------------
  $eventType = civicrm_api4('OptionValue', 'get', [
    'where' => [
      ['option_group_id:name', '=', 'event_type'],
      ['is_active', '=', TRUE],
    ],
    'limit' => 25,
    'checkPermissions' => FALSE,
  ]);
  if ($eventType->count() == 0) {
    CRM_Core_Session::setStatus(
      'Airfile: No active Event Type found. Please configure event types.',
      'Airfile Extension',
      'error'
    );
    \Civi::log()->error('Airfile enable failed: missing event type');
    return;
  }

  // --------------------------------------------------
  // 2. Check Participant Status
  // --------------------------------------------------
  $participantStatusTypes = civicrm_api4('ParticipantStatusType', 'get', [
    'limit' => 25,
    'checkPermissions' => TRUE,
  ]);
  
  if ($participantStatusTypes->count() == 0) {
    CRM_Core_Session::setStatus(
      'Airfile: No participant statuses found.',
      'Airfile Extension',
      'error'
    );
    \Civi::log()->error('Airfile enable failed: no participant statuses');
    return;
  }

  
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

