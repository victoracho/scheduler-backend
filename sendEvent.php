<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');
require_once(__DIR__ . '/crest.php');

try {
  $servername = "16.171.204.95";
  $username = "bitrix";
  $password = "8726231";
  $dbname = "miami";
  $_POST = json_decode(file_get_contents("php://input"), true);
  $user = $_POST['user'];
  $deal = $_POST['deal_id'];
  $event = $_POST['event'];
  $now = date('Y-m-d\TH:i:sP');
  // se obtiene el deal para capturar los campos
  $currentDeal = crest::call(
    'crm.deal.get',
    [
      'id' => $deal
    ],
  );


  $allPhones = null;
  $leadName = null;
  if ($currentDeal['result']) {
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

  $stmt = $conn->prepare($sql = "INSERT into appointments SET name= ?, status= ?, user= ?, substatus= ?, start = ?, end =?,  date_created = ?, comment = ?, deal_id = ?, phone = ? , lead_name = ? ");
  $stmt->bind_param('sssssssssss', $event['title'], $event['BackgroundColor'], $user, $event['substatus'], $event['start'], $event['end'], $now, $event['text'], $deal, $allPhones, $leadName);
  $result = $stmt->execute();
  $conn->close();
  $response = array(
    'message' => 'Added Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error ocurred, try again'
  );
  echo json_encode($response);
}


die();
