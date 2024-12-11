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
  $id = preg_replace('~\D~', '', $id);

  $status = $_GET['status'];

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
die();
