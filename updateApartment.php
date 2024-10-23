<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');

try {
  $servername = "localhost";
  $username = "root";
  $password = "Laravel2024!";
  $dbname = "scheduler";

  $id = $_GET['id'];
  $status = $_GET['status'];
  $id = preg_replace('~\D~', '', $id);

  if ($status == 'unlocked') {
    $newStatus = 'locked';
  }
  if ($status == 'locked') {
    $newStatus = 'unlocked';
  }
  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "UPDATE apartments SET status = ? WHERE  id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ss', $newStatus, $id);
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
