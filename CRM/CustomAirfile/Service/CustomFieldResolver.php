<?php

/**
 * @service custom_airfile.field_resolver
 */
class CRM_CustomAirfile_Service_CustomFieldResolver extends \Civi\Core\Service\AutoService {

  private $fields = [];

  public function getFieldKey(string $fieldName): string {

    if (!isset($this->fields[$fieldName])) {

      $result = civicrm_api4('CustomField', 'get', [
        'checkPermissions' => FALSE,
        'select' => ['id','name'],
        'where' => [
          ['name', '=', $fieldName],
          ['custom_group_id.name', '=', 'travel_details']
        ]
      ]);
      if ($result->rowCount == 0) {
        throw new Exception("Custom field {$fieldName} not found");
      }

      $this->fields[$fieldName] = 'custom_' . $result[0]['id'];
    }

    return $this->fields[$fieldName];
  }
}