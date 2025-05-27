<?php
//require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', '1');

$ini = parse_ini_file('app.ini');
$servername = $ini['servername'];
$username = $ini['db_user'];
$password = $ini['db_password'];
$dbname = $ini['db_name'];

$input = $_GET['time'];
$date = new DateTime($input);

//$firstDay = $_GET['time'];
//$date = new DateTime($firstDay);
//$date->modify('last day of this months');
//$lastDay = $date->format('Y-m-d\TH:i:s');

$firstDay = (clone $date)
    ->modify('first day of previous month')
    ->setTime(0, 0, 0)
    ->format('Y-m-d\TH:i:s');

$lastDay = (clone $date)
    ->modify('last day of next month')
    ->setTime(23, 59, 59)
    ->format('Y-m-d\TH:i:s');

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM reservations where start between '$firstDay' and  '$lastDay' AND status <> 'deleted'";
$result = mysqli_query($conn, $sql);

$reservations = [];

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  $moveDisabled = true;
  $resizeDisabled = true;
  while ($res = mysqli_fetch_assoc($result)) {
    $reservations[] =
      [
        'id' => $res['id'],
        'name' => $res['name'],
        'status' => $res['status'],
        'start' => $res['start'],
        'end' => $res['end'],
        'user_created' => $res['user_created'],
        'date_created' => $res['date_created'],
        'text' => $res['comentary'],
        'user_modified' => $res['user_modified'],
        'date_modified' => $res['date_modified'],
        'crm' => $res['crm'],
        'deal_id' => $res['deal_id'],
        'visitors' => $res['visitors'],
        'code' => $res['code'],
        'resource' => 'a' . $res['apartment_id'],


        'moveDisabled' => $moveDisabled,
        'resizeDisabled' => $resizeDisabled
      ];
  }
}

$sql = "SELECT * FROM buildings";
$result = mysqli_query($conn, $sql);
$buildings = [];
if (mysqli_num_rows($result) > 0) {
  // output data of each row
  while ($res = mysqli_fetch_assoc($result)) {
    $buildings[] =
      [
        'id' =>  $res['id'],
        'expanded' => $res['expanded'],
        'name' => $res['name'],
        'comentary' => $res['comentary'],
        'status' => $res['status']
      ];
  }
}

foreach ($buildings as &$building) {
  $building['children'] = [];
  $id = $building['id'];
  $building['id'] = 'e' . $building['id'];
  $sql = "SELECT * FROM apartments where building_id = $id";
  $result = mysqli_query($conn, $sql);
  $apartments = [];
  if (mysqli_num_rows($result) > 0) {
    while ($res = mysqli_fetch_assoc($result)) {
      $apartments[] =
        [
          'id' => 'a' . $res['id'],
          'name' => $res['name'],
          'status' => $res['status'],
          'location' => $res['location'],
          'rooms' => $res['rooms'],
        ];
    }
  }
  $building['children'] = $apartments;
}

mysqli_close($conn);
$results = json_encode(array(
  'reservations' => $reservations,
  'buildings' => $buildings,
));

echo $results;
