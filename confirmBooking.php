<?php

$crm = $_GET['crm'];
if ($crm == "DASO") {
    require_once(__DIR__ . '/CRestDASO.php');
}
if ($crm == "DDS") {
    require_once(__DIR__ . '/CRestDDS.php');
}
if ($crm == "ECL") {
    require_once(__DIR__ . '/CRestECL.php');
}

header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');


try {
  $ini = parse_ini_file('app.ini');
  $servername = $ini['servername'];
  $username = $ini['db_user'];
  $password = $ini['db_password'];
  $dbname = $ini['db_name'];

  $id = $_GET['id'];
  $id = preg_replace('~\D~', '', $id);

  $status = $_GET['status'];
  $start = $_GET['start'];
  $end = $_GET['end'];
  $apt = $_GET['apt'];
  $deal_id = $_GET['deal_id'];
  $address = '';

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }



  $sql = "UPDATE reservations SET status= ? WHERE  id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('si', $status, $id);
  $result = $stmt->execute();

  $conf_status = "unconfirmed";
  $sql = "UPDATE confirmantions SET status= ? WHERE  ID_reservations = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('si', $conf_status, $id);
  $result = $stmt->execute();

    $sql = "SELECT buildings.name FROM buildings INNER JOIN apartments ON buildings.id = apartments.building_id WHERE apartments.id = ".$apt;
    $stmt = $conn->prepare($sql);
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_assoc($result);

    $address = $res['name'];

  $conn->close();

  $response = array(
    'message' => 'Confirmed Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => $e->getMessage()
  );
  echo json_encode($response);
}

$desde = new DateTime($start);
$desde = $desde->format('M d, Y');

$hasta = new DateTime($end);
$hasta = $hasta->format('M d, Y');

$sms_end = 'Your have and reservation in '. $address.' from '.$desde. ' to '.$hasta;

if ($crm == "DASO"){
    $sms_text = "Hi we are you Plastic Surgery Clinic, " . $sms_end;
    $sms = CRestDASO::call(
        'bizproc.workflow.start',
        [
            'TEMPLATE_ID' => 233,
            'DOCUMENT_ID' => [
                'crm',
                'CCrmDocumentDeal',
                $deal_id
            ],
            'PARAMETERS' => [
                'TEXT' => $sms_text
            ]
        ]
    );
    echo "DASO MESSENGE SUCCESSFULLY";
}

if ($crm == "DDS"){
    $sms_text = "Hi we are Dental Design Smile, " . $sms_end;
    $sms = CRestDDS::call(
        'bizproc.workflow.start',
        [
            'TEMPLATE_ID' => 426,
            'DOCUMENT_ID' => [
                'crm',
                'CCrmDocumentDeal',
                $deal_id
            ],
            'PARAMETERS' => [
                'TEXT' => $sms_text
            ]
        ]
    );
    echo "DDS MESSENGE SUCCESSFULLY";
}

if ($crm == "ECL"){
    $sms_text = "Hi we are Dental Design Smile, " . $sms_end;
    $sms = CRestECL::call(
        'bizproc.workflow.start',
        [
            'TEMPLATE_ID' => 164,
            'DOCUMENT_ID' => [
                'crm',
                'CCrmDocumentDeal',
                $deal_id
            ],
            'PARAMETERS' => [
                'TEXT' => $sms_text
            ]
        ]
    );
    echo "ECL MESSENGE SUCCESSFULLY";
}

die();
