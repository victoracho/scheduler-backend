<?php
require_once(__DIR__ . '/crest.php');
// status de pool es UC_LSS7ZV y unnasigned NEW
$results = [];
$next = '0';
$lead = CRest::call(
  'imconnector.list',
);
var_dump($lead);
die();
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
