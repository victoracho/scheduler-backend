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
  //$id = 2;

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);

  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  $sql = "SELECT * FROM reservations WHERE  id = " . $id;
  $stmt = $conn->prepare($sql);
  $result = mysqli_query($conn, $sql);
  $res = mysqli_fetch_assoc($result);
  $reservation[] =
    [
      'id' => $res['id'],
      'name' => $res['name'],
      'start' => $res['start'],
      'end' => $res['end'],
      'comentary' => $res['comentary'],
      'visitors' => $res['visitors'],
    ];

  $conn->close();

  echo json_encode($reservation);
} catch (Exception $e) {
  $response = array(
    'message' => $e->getMessage()
  );
  echo json_encode($response);
}
die();
