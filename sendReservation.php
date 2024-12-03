<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');
require_once(__DIR__ . '/crest.php');

try {
  $ini = parse_ini_file('app.ini');
  $servername = $ini['servername'];
  $username = $ini['db_user'];
  $password = $ini['db_password'];
  $dbname = $ini['db_name'];

  $_POST = json_decode(file_get_contents("php://input"), true);

  /*
  $user = $_POST['user'];
  $deal = $_POST['deal_id'];
  $now = date('Y-m-d\TH:i:sP');
  $currentDeal = crest::call(
    'crm.deal.get',
    [
      'id' => $deal
    ],
  );
  */

  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

    $name = $_GET['name'];
    $status = "prebooked";
    $start = $_GET['start'];
    $end = $_GET['end'];
    $user_created = 'user.temp';
    $date_created = new DateTime();
    $date_created = $date_created->format('Y-m-d\TH:i:s');
    $comentary = $_GET['comentary'];
    $apartment_ID = $_GET['apartment_ID'];
    $crm = 'DASO TEST';
    $deal_id = "DEAL TEST";
    $visitors = $_GET['visitors'];

  $stmt = $conn->prepare($sql = "INSERT into reservations SET name= ?, status= ?, start = ?, end =?, user_created= ?, date_created = ?, comentary = ?,apartment_id = ?, crm = ? , deal_id = ?, visitors = ? ");
  $stmt->bind_param('sssssssssss', $name, $status, $start, $end, $user_created, $date_created, $comentary, $apartment_ID, $crm, $deal_id, $visitors);
  $result = $stmt->execute();

    function getDateRange($startDate, $endDate) {
        $startDate = new DateTime($startDate);
        $endDate = new DateTime($endDate);
        //$endDate->modify('+1 day');
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        return $dateRange;
    }

    $dateRange = getDateRange($start, $end);

    $lastId = $stmt->insert_id;
    $status = "pend";
    foreach ($dateRange as $date) {
        $date_s = $date->format('Y-m-d\T00:00:00');
        $stmt = $conn->prepare($sql = "INSERT into confirmantions SET date= ?, ID_reservations= ?,  status= ? ");
        $stmt->bind_param('sis',$date_s, $lastId, $status);
        $result = $stmt->execute();
    }



  $conn->close();
  $response = array(
    'message' => 'Added Succesfully'
  );

  /*
  $desde = $event['start'];
  $desde = new DateTime($desde);
  $desde = $desde->format('Y-m-d H:i');

  $hasta = $event['end'];
  $hasta = new DateTime($hasta);
  $hasta = $hasta->format('Y-m-d H:i');
  /*

  /*
  $comment = CRest::call(
    'crm.timeline.comment.add',
    [
      'fields' =>  [
        'ENTITY_ID' => $deal,
        'ENTITY_TYPE' => "deal",
        'COMMENT' => "A reservation has been created for the type: " . $event['BackgroundColor'] . ' From: ' . $desde . ' Until: ' . $hasta . ' created by: ' . $user . ' for Miami Calendar'
      ],
    ],
  );
  */

  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error has ocurred'
  );
  echo json_encode($response);
}
die();
