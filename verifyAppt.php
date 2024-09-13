<?php
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/crest.php');
//date_default_timezone_set('America/New York');
ini_set('display_errors', 'On');

try {
  $servername = "16.171.204.95";
  $username = "bitrix";
  $password = "8726231";
  $dbname = "miami";
  $deal_id = $_GET['deal_id'];

  // Create connection
  $conn = new mysqli($servername, $username, $password, $dbname);
  // Check connection
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }
  $sql = "SELECT * FROM appointments where deal_id = $deal_id ";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    $res = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    $response = array(
      'message' => 'found',
      'result' => $res
    );
    echo json_encode($response);
  } else {
    mysqli_close($conn);
    $response = array(
      'message' => 'not found'
    );
    echo json_encode($response);
  }
} catch (Exception $e) {
  $response = array(
    'message' => 'not found'
  );
  echo json_encode($response);
}
