<?php
require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
//109657
// calendario de miami

/* $calendar = CRest::call( */
/*   'calendar.event.get', */
/*   [ */
/*     'type' => 'group', */
/*     'ownerId' => '5', */
/*   ], */
/* ); */
/**/
/* $results = $calendar['result']; */
/* var_dump($results); */
/* die(); */

/* $results = array_map(function ($res) { */
/*   $date = strtotime($res['DATE_FROM']); */
/*   $arr = array( */
/*     'id' => $res['ID'], */
/*     'allDay' => true, */
/*     'title' => $res['NAME'], */
/*     'start' => date('Y-m-d', $date), */
/*     'backgroundColor' => $res['COLOR'], */
/*   ); */
/*   return $arr; */
/* }, $results); */
/**/
/* $results = json_encode($results); */

$range = $_GET['range'];
$status = strtolower($_GET['status']);

$substatus = $_GET['substatus'];

if ($substatus == "") {
  $substatus = null;
}

if ($substatus) {
  $substatus = '#' . $substatus;
  $substatus =  "substatus = '$substatus' AND";
}

$range = explode(",", $range);

$servername = "16.171.204.95";
$username = "bitrix";
$password = "8726231";
$dbname = "miami";



// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

if (empty($status)) {
  $status = null;
}

$sql = "SELECT * FROM appointments where $substatus status in ('$status') AND start between '$range[0]' AND '$range[1]' ";

$result = mysqli_query($conn, $sql);
$results = [];

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while ($res = mysqli_fetch_assoc($result)) {
    if ($res['status'] == 'free eval') {
      $status = '#bbecf1';
    }
    if ($res['status'] == 're-evaluation') {
      $status = '#fff55a';
    }
    if ($res['status'] == 'evaluation') {
      $status = '#f10057';
    }
    if ($res['status'] == 'emergency') {
      $status = '#e89b06';
    }
    if ($res['status'] == 'vip') {
      $status = '#0092cc';
    }
    if ($res['status'] == 'missing-appointment') {
      $status = '#683696';
    }

    $results[] =
      [
        'id' => $res['id'],
        'deal_id' => $res['deal_id'],
        'allDay' => false,
        'title' => $res['name'],
        'backgroundColor' => $status,
        'status' => $res['status'],
        'start' => $res['start'],
        'end' => $res['end'],
        'comment' => $res['comment'],
        'substatus' => $res['substatus'],
        'user' => $res['user'],
        'user_modified' => $res['user_modified'],
        'date_created' => $res['date_created'],
        'date_modified' => $res['date_modified']
      ];
  }
}
mysqli_close($conn);

if (empty($results)) {
  $results[] = [];
}
$quantity = array(
  'free eval' => 0,
  'evaluation' => 0,
  're-evaluation' => 0,
  'emergency' => 0,
  'vip' => 0,
  'missing-appointment' => 0
);
foreach ($results as $res) {
  if ($res['status'] == 'free eval') {
    $quantity['free eval']++;
  }
  if ($res['status'] == 'evaluation') {
    $quantity['evaluation']++;
  }
  if ($res['status'] == 're-evaluation') {
    $quantity['re-evaluation']++;
  }
  if ($res['status'] == 'emergency') {
    $quantity['emergency']++;
  }
  if ($res['status'] == 'vip') {
    $quantity['vip']++;
  }
  if ($res['status'] == 'missing-appointment') {
    $quantity['missing-appointment']++;
  }
}

$results = json_encode(array(
  'results' => $results,
  'quantity' => $quantity
));
echo $results;
