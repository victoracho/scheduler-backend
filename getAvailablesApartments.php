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

$build = $_GET['build'];
$start = $_GET['start'];
$end = $_GET['end'];

$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT r.start, r.end , a.id AS apartment_id FROM reservations r JOIN apartments a ON r.apartment_id = a.id JOIN buildings b ON a.building_id = b.id WHERE r.end >= CURDATE() AND b.id = $build AND r.status != 'deleted'";
$result = mysqli_query($conn, $sql);

$reservations = [];

if (mysqli_num_rows($result) > 0) {
    while ($res = mysqli_fetch_assoc($result)) {
        $reservations[] =
            [
                'start' => $res['start'],
                'end' => $res['end'],
                'apartment_id' => $res['apartment_id'],
            ];
    }
}

$apts = [];

$sql = "SELECT id as apartment_id, name as apartment_name FROM apartments WHERE building_id = $build AND status != 'locked'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($res = mysqli_fetch_assoc($result)) {
        $apts[] = [
            'apartment_id' => $res['apartment_id'],
            'apartment_name' => $res['apartment_name'],
        ];
    }
}

mysqli_close($conn);

function getAvailablesApt($reservations, $start, $end, $apts) {
    $start = strtotime($start);
    $end = strtotime($end);

    $available = [['N/A', 'N/A']]; // Siempre el primer elemento es ['N/A', 'N/A']

    foreach ($apts as $apartment) {
        $apartment_id = $apartment['apartment_id'];
        $apartment_name = $apartment['apartment_name'];

        $overlap = false;
        foreach ($reservations as $reservation) {
            if ($reservation['apartment_id'] === $apartment_id) {
                $start_reservation = strtotime($reservation['start']);
                $end_reservation = strtotime($reservation['end']);

                if ($start <= $end_reservation && $end >= $start_reservation) {
                    $overlap = true;
                    break;
                }
            }
        }

        if (!$overlap) {
            $available[] = [$apartment_id, $apartment_name];
        }
    }

    return $available;
}

$disponibles = getAvailablesApt($reservations, $start, $end, $apts);

$results = json_encode($disponibles);



echo $results;

//var_dump($disponibles);




