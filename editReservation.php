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

  //test campos
  $name = $_GET['name'];
  $start = $_GET['start'];
  $end = $_GET['end'];
  $comentary = $_GET['comentary'];
  $date_modified = new DateTime();
  $date_modified = $date_modified->format('Y-m-d\TH:i:s');
  $user_modified = 'j.noy';
  $visitors = $_GET['visitors'];

  $sql = "UPDATE reservations SET name= ?, start = ?, end =?, comentary = ?, date_modified = ?, user_modified = ?, visitors = ?  WHERE  id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssssssi', $name, $start, $end, $comentary, $date_modified, $user_modified, $visitors, $id);
  $result = $stmt->execute();
  $conn->close();

  $response = array(
    'message' => 'EDITED Succesfully'
  );
  echo json_encode($response);
} catch (Exception $e) {
  $response = array(
    'message' => $e->getMessage()
  );
  echo json_encode($response);
}
die();
