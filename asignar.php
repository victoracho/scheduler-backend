<?php
require_once(__DIR__ . '/crest.php');
// pasar los deals no answer a pool
// contacted no answer = UC_ISOPF0
// pool = UC_LSS7ZV
$startDate = date("Y-n-j\\TG:i:s-H:i", time());
$date = date("Y-n-j\\TG:i:s-H:i", strtotime("$startDate -10 days"));

// la fecha de hace 10 dias 
// junk  es contacted no answer
$results = [];
$next = '0';
do {
  $lead = CRest::call(
    'crm.lead.list',
    [
      'filter' => ['<DATE_MODIFY' => '2024-05-26T16:38:29-04:00', 'STATUS_ID' => 'UC_ISOPF0'],
      'start' => $next,
      'select' => ['ID', 'DATE_MODIFY', 'ASSIGNED_BY_ID'],
    ],
  );

  $next = null;
  if (isset($lead['next'])) {
    $next = $lead['next'];
  }
  $results = array_merge($results, $lead['result']);
} while ($next != null);

$ids = ['582', '583', '4805', '587', '586',  '7035', '580', '578', '15431'];
foreach ($results as $res) {
  $exclude = array($res['ASSIGNED_BY_ID']);
  $newIds = array_diff($ids, $exclude);
  $new = array_rand($newIds);
  $election = $newIds[$new];
  $val = CRest::call(
    'crm.lead.update',
    [
      'id' => $res['ID'],
      'fields' => [
        'ASSIGNED_BY_ID' => $election,
        'STATUS_ID' => 'UC_LSS7ZV'
      ]
    ]
  );
}

die('fin del proceso');

// fin proceso pasar los deals no answer a pool
//["ID" => $lead['PHONE'][0]['ID'], "VALUE" => "+1123123123", "VALUE_TYPE" => "WORK"]
