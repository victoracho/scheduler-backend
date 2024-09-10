<?php
header("Access-Control-Allow-Origin: *");
date_default_timezone_set('America/New York');
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
  $deal = $_POST['deal_id'];
  $now = new DateTime("now");
  $now->format('Y-m-d\TH:i:sP');
  $deleted = true;
  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $stmt = $conn->prepare($sql = "UPDATE appointments SET  date_modified = ?, user_modified = ?, deleted = ? WHERE deal_id = ?");
  $stmt->bind_param('ssss', $now->date, $user,  $deleted, $deal);
  $result = $stmt->execute();
  $conn->close();
  $response = array(
    'message' => 'Deleted Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'an error occurred, try again'
  );
  echo json_encode($response);
}
