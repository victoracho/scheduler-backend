<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');
require_once(__DIR__ . '/crest.php');

try {
  $servername = "localhost";
  $username = "root";
  $password = "Laravel2024!";
  $dbname = "scheduler";

  $_POST = json_decode(file_get_contents("php://input"), true);
  $user = $_POST['user'];
  $deal = $_POST['deal_id'];
  $event = $_POST['reservation'];
  $now = date('Y-m-d\TH:i:sP');
  $currentDeal = crest::call(
    'crm.deal.get',
    [
      'id' => $deal
    ],
  );

  $allPhones = null;
  $leadName = null;
  $edad = null;
  $state = null;
  if ($currentDeal['result']) {
    // se checa la edad y el estado
    $currentDeal = $currentDeal['result'];
    if (isset($currentDeal['TITLE'])) {
      $leadName = $currentDeal['TITLE'];
    }
    $contactId = $currentDeal['CONTACT_ID'];
    if ($contactId) {
      $contactData = crest::call(
        'crm.contact.get',
        [
          'id' => $contactId
        ],
      );
      if ($contactData && isset($contactData['result'])) {
        $contact = $contactData['result'];
        $phones = $contact['PHONE'];
        $allPhones = '';
        foreach ($phones as $phone) {
          $allPhones .= ' ' .  $phone['VALUE'];
        }
      }
    }
  }

  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare($sql = "INSERT into reservations SET name= ?, status= ?, start = ?, end =?, user_created= ?, date_created = ?, comentary = ?,apartment_id = ?, crm = ? , deal_id = ?, building_id = ?, visitors = ? ");
  $stmt->bind_param('ssssssssss', $event['title'], $event['BackgroundColor'], $user, $event['substatus'], $event['start'], $event['end'], $now, $event['text'], $deal, $allPhones, $leadName, $event['lodging'], $event['transportation'], $event['more_invoices'], $event['amount'], $event['invoice_number'], $edad, $state);

  $result = $stmt->execute();
  $conn->close();
  $response = array(
    'message' => 'Added Succesfully'
  );
  $desde = $event['start'];
  $desde = new DateTime($desde);
  $desde = $desde->format('Y-m-d H:i');

  $hasta = $event['end'];
  $hasta = new DateTime($hasta);
  $hasta = $hasta->format('Y-m-d H:i');

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
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error has ocurred'
  );
  echo json_encode($response);
}
die();
