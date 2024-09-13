<?php
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/crest.php');
// Calendario Miami  
$calendar = CRest::call(
  'calendar.event.get',
  [
    'type' => 'group',
    'ownerId' => '5',
  ],
);
$results = $calendar['result'];
$servername = "173.31.30.43";
$username = "bitrix";
$password = "8726231";
$dbname = "miami";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$deal_id = 0;
foreach ($results as $res) {
  $deal_id++;
  if ($res['SECTION_ID'] == 84) {
    $color = '#f7699d';
    $status = 'evaluation';
  }
  if ($res['SECTION_ID'] == 85) {
    $color = '#bbecf1';
    $status = 'free eval';
  }
  if ($res['SECTION_ID'] == 86) {
    $color = '#fff55a';
    $status = 're-evaluation';
  }
  if ($res['SECTION_ID'] == 88) {
    $color = '#e89b06';
    $status = 'emergency';
  }
  if ($res['SECTION_ID'] == 89) {
    $color = '#0092cc';
    $status = 'vip';
  }
  $name = $res['NAME'];
  $substatus = '#808080';

  $start = $res['DATE_FROM'];
  $start = new DateTime($start, new DateTimeZone('America/New_York'));
  $start = $start->format('Y-m-d\TH:i:sP');

  $end = $res['DATE_TO'];
  $end = new DateTime($end, new DateTimeZone('America/New_York'));
  $end = $end->format('Y-m-d\TH:i:sP');

  $user = 'No-name';
  if ($res['MEETING']) {
    if (isset($res['MEETING']['HOST_NAME'])) {
      $user = $res['MEETING']['HOST_NAME'] ? $res['MEETING']['HOST_NAME'] : 'No-name';
    }
  }
  $stmt = $conn->prepare($sql = "INSERT into appointments SET deal_id = ? , name= ?, status= ?, user= ?, substatus= ?, start = ?, end = ? ");
  $stmt->bind_param('sssssss', $deal_id, $name, $status, $user, $substatus, $start, $end);
  $result = $stmt->execute();
}
$conn->close();
die();
