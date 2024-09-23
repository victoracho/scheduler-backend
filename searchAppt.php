<?php
header("Access-Control-Allow-Origin: *");
require_once(__DIR__ . '/crest.php');
//date_default_timezone_set('America/New York');
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$servername = "16.171.204.95";
$username = "bitrix";
$password = "8726231";
$dbname = "newJersey";
$name = $_GET['name'];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM appointments where name LIKE '%$name%' OR  phone LIKE '%$name%' OR lead_name LIKE '%$name%'";

$result = mysqli_query($conn, $sql);
$res = [];
if (mysqli_num_rows($result) > 0) {
  while ($row = $result->fetch_assoc()) {
    $res[] = $row;
  }
  mysqli_close($conn);
  echo json_encode($res);
} else {
  mysqli_close($conn);
  echo json_encode([]);
}
