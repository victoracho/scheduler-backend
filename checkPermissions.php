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

    $name = $_GET['name'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM users WHERE  name = '$name'";
    $stmt = $conn->prepare($sql);
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_assoc($result);

    $conn->close();
    if($res == null){
        $permissions = "DENIED";
    }else{
        $permissions = $res['permissions'];
    }
    var_dump($permissions);
} catch (Exception $e) {
    $response = array(
        'message' => $e->getMessage()
    );
    echo json_encode($response);
}
die();
