<?php
//require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
ini_set('display_errors', '1');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scheduler";


//$day = $_GET['time'];
//$day = '2024-10-25T08:00:00';
$day = date("Y-m-d");

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM confirmantion where date = '$day'";
$result = mysqli_query($conn, $sql);

$confirmantions = [];

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    $moveDisabled = true;
    $resizeDisabled = true;
    while ($res = mysqli_fetch_assoc($result)) {
        $confirmantions[] =
            [
                'id' => $res['ID'],
                'date' => $res['date'],
                'reservation' => $res['ID_reservations'],
                'user' => $res['user'],
                'moveDisabled' => $moveDisabled,
                'resizeDisabled' => $resizeDisabled
            ];
    }
}

mysqli_close($conn);
$results = json_encode(array(
    'reservations' => $confirmantions,
));

echo $results;



