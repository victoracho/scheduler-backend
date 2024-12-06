<?php
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
  //$id = 5;
  $id = preg_replace('~\D~', '', $id);

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  $date_modified = new DateTime();
  $date_modified = $date_modified->format('Y-m-d\TH:i:s');
  $user_modified = $_GET['user'];
  $sql = "UPDATE reservations SET status = 'deleted', date_modified = ? , user_modified = ? WHERE  id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ssi', $date_modified, $user_modified, $id);
  $result = $stmt->execute();

    $sql = "UPDATE confirmantions SET status = 'deleted' WHERE  ID_reservations = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $result = $stmt->execute();

  $conn->close();

  $response = array(
    'message' => 'Deleted Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => 'An error ocurred, try again'
  );
  echo json_encode($response);
}
die();
