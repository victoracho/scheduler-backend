<?php
// social media es IN_PROCESS 
// test es UC_TX3D8B  
require_once(__DIR__ . '/crest.php');
// se hace do while hasta que terminen los batch 


$next = '0';
do {
  echo  $next;
  echo  '<br>';

  // hacemos busqueda de todos los leads por el estatus deseado
  $leads = CRest::call(
    'crm.lead.list',
    [
      'filter' => ['STATUS_ID' => 'IN_PROCESS'],
      'start' => $next,
      'select' => ['ID', 'PHONE', 'CONTACT_ID', 'HAS_PHONE'],
    ],
  );

  $results = $leads['result'];
  foreach ($results as $ld) {
    // modificamos los telefonos del contacto del lead
    $contact = null;
    if ($ld['CONTACT_ID'] != null) {
      $contact = crest::call(
        'crm.contact.get',
        [
          'id' => $ld['CONTACT_ID']
        ],
      );
      $contact = $contact['result'];
      if ($contact['HAS_PHONE'] == 'Y') {
        $contactPhones = $contact['PHONE'];
        foreach ($contactPhones as $phon) {
          if (strpos($phon['VALUE'], '+1') === false) {
            $done = CRest::call(
              'crm.contact.update',
              [
                'id' => $contact['ID'],
                'fields' => [
                  'PHONE' => [
                    [
                      "ID" => $phon['ID'],
                      "VALUE" => '+1 ' . $phon['VALUE'],
                      "VALUE_TYPE" => $phon['VALUE_TYPE'],
                      "TYPE_ID" => $phon['TYPE_ID']
                    ]
                  ]
                ]
              ]
            );
          }
          if (strpos($phon['VALUE'], '+1') === 0) {
            $new = str_replace('+1', '', $phon['VALUE']);
            $done = CRest::call(
              'crm.contact.update',
              [
                'id' => $contact['ID'],
                'fields' => [
                  'PHONE' => [
                    [
                      "ID" => $phon['ID'],
                      "VALUE" => '+1 ' . $new,
                      "VALUE_TYPE" => $phon['VALUE_TYPE'],
                      "TYPE_ID" => $phon['TYPE_ID']
                    ]
                  ]
                ]
              ]
            );
          }
        }
      }
    }

    // modificamos todos los telefonos del lead 
    if ($ld['HAS_PHONE'] == 'Y') {
      $phones = $ld['PHONE'];
      foreach ($phones as $phone) {
        // modificamos el telefono
        if (strpos($phone['VALUE'], '+1') === 0) {
          $new = str_replace('+1', '', $phone['VALUE']);
          $res = CRest::call(
            'crm.lead.update',
            [
              'id' => $ld['ID'],
              'fields' => [
                'PHONE' => [
                  [
                    "ID" => $phone['ID'],
                    "VALUE" => '+1 ' . $new,
                    "VALUE_TYPE" => $phone['VALUE_TYPE'],
                    "TYPE_ID" => $phone['TYPE_ID']
                  ]
                ]
              ]
            ]
          );
        }
        if (strpos($phone['VALUE'], '+1') === false) {
          $res = CRest::call(
            'crm.lead.update',
            [
              'id' => $ld['ID'],
              'fields' => [
                'PHONE' => [
                  [
                    "ID" => $phone['ID'],
                    "VALUE" => '+1 ' . $phone['VALUE'],
                    "VALUE_TYPE" => $phone['VALUE_TYPE'],
                    "TYPE_ID" => $phone['TYPE_ID']
                  ]
                ]
              ]
            ]
          );
        }
      }
    }
  }
  // pasamos al siguiente batch, hasta que no haya mas resultados
  $next = null;
  if (isset($leads['next'])) {
    $next = $leads['next'];
  }
} while ($next != null);
