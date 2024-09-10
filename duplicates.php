<?php
require_once(__DIR__ . '/crest.php');
// contacted no answer es UC_ISOPF0 
// lead es UC_FH5727 
// pool es UC_LSS7ZV
// wazup es UC_A7U9SN
// test es UC_TX3D8B
// test 2 es UC_BGL8OP
// duplicados es 9
// interested in promo es UC_A7U9SN
// comment pending contact es UC_Y28S31
// web es UC_FH5727

if (!empty($_REQUEST['auth']['application_token']) && $_REQUEST['auth']['application_token'] == 'zjiwp2hvk9hw4j2wh4kqu41yqr9et15h') {
  if ($_REQUEST['event'] == 'ONCRMLEADADD') {
    $lead = crest::call(
      'crm.lead.get',
      [
        'id' => $_REQUEST['data']['FIELDS']['ID']
      ],
    );
    $lead = $lead['result'];
    $results = [];

    // Si tiene contacto 
    $contact = null;
    if ($lead['CONTACT_ID']) {
      $contact = crest::call(
        'crm.contact.get',
        [
          'id' => $lead['CONTACT_ID']
        ],
      );
      //  si tiene telefono
      $contact = $contact['result'];
      if ($contact['HAS_PHONE'] == 'Y') {
        $contactPhones = $contact['PHONE'];
        // recorro los telefonos del contacto, si no tienen +1 se le agrega
        foreach ($contactPhones as $phon) {
          if ($lead['STATUS_ID'] == 'UC_FH5727') {
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
          }
        }
      }
    }
    // si el lead tiene celular
    if ($lead['HAS_PHONE'] == 'Y') {
      $phones = $lead['PHONE'];
      foreach ($phones as $phone) {
        // modificamos el telefono
        if (strpos($phone['VALUE'], '+1') === false) {
          $done = CRest::call(
            'crm.lead.update',
            [
              'id' => $_REQUEST['data']['FIELDS']['ID'],
              'fields' => [
                'PHONE' => [
                  array(
                    "ID" => $phone['ID'],
                    "VALUE" => '+1 ' . $phone['VALUE'],
                    "VALUE_TYPE" => $phone['VALUE_TYPE'],
                    "TYPE_ID" => $phone['TYPE_ID']
                  )
                ]
              ]
            ]
          );
        }

        // por cada celular, llamo al api para verificar duplicados
        $duplicates = CRest::call(
          'crm.duplicate.findbycomm',
          [
            'entity_type' => "LEAD",
            'type' => "PHONE",
            'values' => array(
              $phone['VALUE']
            )
          ],
        );
        $duplicates = $duplicates['result'];
        if (isset($duplicates['LEAD'])) {
          $duplicates = $duplicates['LEAD'];
          $results = array_merge($results, $duplicates);
        }
      }
    }
    if ($lead['HAS_EMAIL'] == 'Y') {
      $emails = $lead['EMAIL'];
      // por cada email se llama al api para verificar duplicados
      foreach ($emails as $email) {
        $duplicates = CRest::call(
          'crm.duplicate.findbycomm',
          [
            'entity_type' => "LEAD",
            'type' => "EMAIL",
            'values' =>  array($email['VALUE'])
          ],
        );
        $duplicates = $duplicates['result'];
        if (isset($duplicates['LEAD'])) {
          $duplicates = $duplicates['LEAD'];
          $results = array_merge($results, $duplicates);
        }
      }
    }

    $results = array_diff($results, [$lead['ID']]);
    $results = array_unique($results);
    if (!empty($results)) {
      // se notifica que el lead ya existe 
      // cambiar el id del webhook
      $event = crest::call(
        'bizproc.workflow.start',
        [
          'TEMPLATE_ID' => 204,
          'DOCUMENT_ID' => ['crm', 'CCrmDocumentLead', 'LEAD_' . $lead['ID']]
        ],
      );
      $url = 'https://btx.dds.miami/rest/10476/1mewdlanh4kgrnos/bizproc.workflow.start?TEMPLATE_ID=204&DOCUMENT_ID[]=crm&DOCUMENT_ID[]=CCrmDocumentLead&DOCUMENT_ID[]=LEAD_' . $lead['ID'];
      // si tiene duplicados, se le agrega (con duplicados) 
      $rest = CRest::call(
        'crm.lead.update',
        [
          'id' => $_REQUEST['data']['FIELDS']['ID'],
          'fields' => [
            'TITLE' => $lead['TITLE'] . ' (con duplicados)'
          ]
        ],
      );
    }
  }
}
