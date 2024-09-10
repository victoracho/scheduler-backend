<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');

try {
  $servername = "localhost";
  $username = "root";
  $password = "Laravel2024!";
  $dbname = "calendar";
  $_POST = json_decode(file_get_contents("php://input"), true);
  $user = $_POST['user'];
  $eventId = $_POST['event_id'];

  $event = $_POST['event'];
  $now = date('Y-m-d\TH:i:sP');

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare($sql = "UPDATE appointments SET name= ?, status= ?, user_modified= ?, substatus= ?, start = ?, end = ?,  date_modified = ?, comment = ?  WHERE  id= ?");
  $stmt->bind_param('sssssssss', $event['title'], $event['BackgroundColor'], $user, $event['substatus'], $event['start'], $event['end'], $now, $event['text'], $eventId);
  $result = $stmt->execute();
  $conn->close();

  $response = array(
    'message' => 'Edited Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error ocurred, try again'
  );
  echo json_encode($response);
}

die();
