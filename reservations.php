<?php
//require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', '1');

$servername = "localhost";
$username = "root";
$password = "Laravel2024!";
$dbname = "scheduler";

$firstDay = $_GET['time'];
$date = new DateTime($firstDay);
$date->modify('last day of this months');
$lastDay = $date->format('Y-m-d\TH:i:s');

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM reservations where start between '$firstDay' and  '$lastDay' ";
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
        'deal_id' => $res['deal_id'],
        'resource' => 'a' . $res['apartment_id'],
        'start' => $res['start'],
        'end' => $res['end'],
        'text' => $res['comentary'],
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
