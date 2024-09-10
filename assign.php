<?php
require_once(__DIR__ . '/crest.php');
// status de pool es UC_LSS7ZV y unnasigned NEW

$results = [];
$next = '0';
$contact = crest::call(
  'imopenlines.crm.chat.get',
  [
    'CRM_ENTITY_TYPE' => '',
    'CRM_ENTITY' => 48392,
  ],
);

do {
  $lead = CRest::call(
    'crm.lead.list',
    [
      'filter' => ['STATUS_ID' => 'UC_LSS7ZV'],
      'start' => $next,
      'select' => ['ID', 'ASSIGNED_BY_ID'],
    ],
  );
  $next = null;
  if (isset($lead['next'])) {
    $next = $lead['next'];
  }
  $results = array_merge($results, $lead['result']);
} while ($next != null);

$total = count($results);
$arr = array_chunk($results, ceil($total / 8));
$cristina = $arr[0];
$daniela = $arr[1];
$diogo = $arr[2];
$alberto = $arr[3];
$juan = $arr[4];
$mila = $arr[5];
$emidio = $arr[6];
$antonio = $arr[7];

$ids = ['582', '583', '4805', '587', '586',  '7035', '580', '578'];
// cristina 
foreach ($cristina as $result) {
  $id = '582';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('cristina');
// daniela 
foreach ($daniela as $result) {
  $id = '583';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('daniela');
// diogo
foreach ($diogo as $result) {
  $id = '4805';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('diogo');
// alberto
foreach ($alberto as $result) {
  $id = '587';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('alberto');
// juan
foreach ($juan as $result) {
  $id = '7035';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('juan');
//mila
foreach ($mila as $result) {
  $id = '586';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('mila');
//emedio
foreach ($emidio as $result) {
  $id = '580';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('emedio');
//antonio
foreach ($antonio as $result) {
  $id = '578';
  $lead = CRest::call(
    'crm.lead.update',
    [
      'id' => $result['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $id
      ]
    ]
  );
}
var_dump('antonio');
