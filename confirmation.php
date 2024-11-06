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

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }


    //$date = $_GET['date'];
    $status = $_GET['status'];
    //$id_reservation = $_GET['ID_reservation'];
    $user = "johan.confirm";
    $sql = "UPDATE confirmantions SET status = ?, user = ? WHERE  id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $status, $user_modified, $id);
    $result = $stmt->execute();

    $sql = "SELECT date, id_reservations FROM confirmantions WHERE  id = " . $id;
    $stmt = $conn->prepare($sql);
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_assoc($result);
    $date = $res['date'];
    $id_reservation = $res['id_reservations'];



    if ($status == "EMPITY"){
        $sql = "UPDATE reservations SET end =?  WHERE  id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $date, $id_reservation);
        $result = $stmt->execute();

        $sql = "DELETE FROM confirmantions WHERE id_reservations = ? AND id > ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id_reservation, $id);
        $result = $stmt->execute();

    }

    /*
    $sql = "UPDATE reservations SET name= ?, start = ?, end =?, comentary = ?, date_modified = ?, user_modified = ?, visitors = ?  WHERE  id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssi', $name, $start, $end, $comentary, $date_modified, $user_modified, $visitors, $id);
    $result = $stmt->execute();
    */


    $conn->close();

    $response = array(
        'message' => 'CONFIRMED Succesfully'
    );
    echo json_encode($response);
} catch (Exception $e) {
    $response = array(
        'message' => $e->getMessage()
    );
    echo json_encode($response);
}
die();
