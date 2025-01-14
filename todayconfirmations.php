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

//$day = $_GET['time'];
//$day = '2024-10-25T08:00:00';
$day = date("Y-m-d\T00:00:00");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

//$sql = "SELECT * FROM confirmantions where date = '$day' AND status = 'unconfirmed'";


$sql = "SELECT confirmantions.*, reservations.name AS name, reservations.start AS start, apartments.name AS apartment
       FROM confirmantions
       JOIN 
         reservations ON confirmantions.ID_reservations = reservations.id
        JOIN 
        apartments ON reservations.apartment_id = apartments.id
       where confirmantions.date = '$day' AND confirmantions.status = 'unconfirmed'";


$result = mysqli_query($conn, $sql);

$confirmantions = [];

if (mysqli_num_rows($result) > 0) {
  // output data of each row
  $moveDisabled = true;
  $resizeDisabled = true;
  while ($res = mysqli_fetch_assoc($result)) {
    $color = "";
    if ($res['prestatus'] == "CONFIRMED"){
        $color = "🟢";
    }
    if ($res['prestatus'] == "EMPTY"){
        $color = "🔴";
    }
    $confirmantions[] =
      [
        'id' => $res['ID'],
        'date' => $res['date'],
        'reservation' => $res['ID_reservations'],
        'user' => $res['user'],
        'name' => $res['name'],
        'apartment' => $res['apartment'],
        'status' => $res['status'],
        'prestatus' => $res['prestatus'],
        'color' => $color,
        'start' => $res['start'],
      ];
  }
}

mysqli_close($conn);
$results = json_encode(array(
  'reservations' => $confirmantions,
));

echo $results;
