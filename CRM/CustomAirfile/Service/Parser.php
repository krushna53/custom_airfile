<?php

use Civi\Core\Service\AutoService;

/**
 * @service custom_airfile.parser
 */
class CRM_CustomAirfile_Service_Parser extends AutoService {

  public function parse(string $filePath): array {

    if (!file_exists($filePath)) {
      return ['error' => 'File not found'];
    }

    $content = file_get_contents($filePath);
    $content = str_replace(["\r\n", "\r"], "\n", $content);
    $lines = explode("\n", $content);

    $data = [
      'event_ref' => null,
      'booking_ref' => null,
      'participants' => [],
    ];

    $travelLegs = [];

    foreach ($lines as $line) {

      $line = trim($line);
      if ($line === '') continue;

      // --------------------
      // EVENT REF
      // --------------------
      if (strpos($line, 'RM*REF/') === 0) {
        $data['event_ref'] = trim(substr($line, 7));
        continue;
      }

      // --------------------
      // BOOKING REF
      // --------------------
      if (strpos($line, 'RM*RQ/') === 0) {
        $data['booking_ref'] = trim(substr($line, 6));
        continue;
      }
      //PArticipant ID
      if (strpos($line, 'RM*ID/') === 0) {

        $parts = explode('/', $line);
      
        $participantId = $parts[2] ?? null;
      
        $lastIndex = count($data['participants']) - 1;
      
        if ($lastIndex >= 0) {
          $data['participants'][$lastIndex]['participant_id'] = $participantId;
        }
      
        continue;
      }

      // --------------------
      // TRAVEL LEGS
      // --------------------
      if (strpos($line, 'H-') === 0) {

        $parts = explode(';', $line);
      
        $flightRaw = trim($parts[5] ?? '');
      
        $flightParts = preg_split('/\s+/', $flightRaw);
      
        $flightNumber = ($flightParts[0] ?? '') . ($flightParts[1] ?? '');
        $bookingClass = $flightParts[2] ?? '';
      
        $dateTime = $flightParts[4] ?? '';
      
        $departureDate = substr($dateTime, 0, 5);   // 17OCT
        $departureTime = substr($dateTime, 5, 4);   // 1025
      
        $arrivalTime = $flightParts[5] ?? '';
        $arrivalDate = $flightParts[6] ?? '';
      
        $travelLegs[] = [
          'from_code' => trim($parts[1] ?? ''),
          'from_name' => trim($parts[2] ?? ''),
          'to_code'   => trim($parts[3] ?? ''),
          'to_name'   => trim($parts[4] ?? ''),
      
          'flight_number' => $flightNumber,
          'booking_class' => $bookingClass,
          'departure_date' => $departureDate,
          'departure_time' => $departureTime,
          'arrival_date' => $arrivalDate,
          'arrival_time' => $arrivalTime,
        ];
      
        continue;
      }

      // --------------------
      // PARTICIPANT
      // --------------------
      if (strpos($line, 'I-') === 0) {

        $parts = explode(';', $line);

        $namePart = preg_replace('/^\d+/', '', $parts[1] ?? '');
        $namePieces = explode('/', $namePart);

        $participant = [
          'participant_id' => null,
          'first_name' => trim($namePieces[1] ?? ''),
          'last_name'  => trim($namePieces[0] ?? ''),
          'rate'       => null,
          'travel_legs'=> $travelLegs,
        ];

        $data['participants'][] = $participant;

        continue;
      }

      // --------------------
      // RATE
      // --------------------
      if (strpos($line, 'RM*RATE/') === 0) {

        $rate = trim(substr($line, 8));

        $lastIndex = count($data['participants']) - 1;

        if ($lastIndex >= 0) {
          $data['participants'][$lastIndex]['rate'] = $rate;
        }

        continue;
      }

    }

    return $data;

  }

}