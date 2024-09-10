<?php
header('Access-Control-Allow-Origin: *');
require_once(__DIR__ . '/crest.php');
// lead es UC_FH5727 
// pool es UC_LSS7ZV
// wazup es UC_A7U9SN
// test es UC_TX3D8B
// test 2 es UC_BGL8OP
// duplicados es 9
// interested in promo es UC_A7U9SN
// comment pending contact es UC_Y28S31

if (isset($_GET['id'])) {
  $lead = crest::call(
    'crm.lead.get',
    [
      'id' => $_GET['id']
    ],
  );
  $lead = $lead['result'];
  $results = [];

  // si el lead tiene celular
  if ($lead['HAS_PHONE'] == 'Y') {
    $phones = $lead['PHONE'];
    foreach ($phones as $phone) {
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
    echo json_encode('si');
  }
}
