<?php
header("Access-Control-Allow-Origin: *");
error_reporting(E_ALL);
header("Access-Control-Allow-Headers: Content-Type");
ini_set('display_errors', 'On');

function getDatesInRangeA($startDate, $endDate) {
    $dates = [];

    $current = new DateTime($startDate);
    $end = new DateTime($endDate);

    $current->modify('+1 day');
    $end->modify('+1 day');

    while ($current < $end) {
        $dates[] = $current->format('Y-m-d\TH:i:s');
        $current->modify('+1 day');
    }

    return $dates;
}

function getDatesInRangeB($startDate, $endDate) {
    $dates = [];

    $current = new DateTime($startDate);
    $end = new DateTime($endDate);

    //$end->modify('+1 day');

    while ($current < $end) {
        $dates[] = $current->format('Y-m-d\TH:i:s');
        $current->modify('+1 day');
    }

    return $dates;
}


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

  $name = $_GET['name'];
  $start = $_GET['start'];
  $end = $_GET['end'];
  $comentary = $_GET['comentary'];
  $date_modified = new DateTime();
  $date_modified = $date_modified->format('Y-m-d\TH:i:s');
  $user_modified = 'j.noy';
  $visitors = $_GET['visitors'];

    // OBTENER FECHAS VIEJAS
    $sql = "SELECT start, end FROM reservations WHERE  id = " . $id;
    $stmt = $conn->prepare($sql);
    $result = mysqli_query($conn, $sql);
    $res = mysqli_fetch_assoc($result);
    //$old_start = new DateTime($res['start']);
    $old_start = $res['start'];
    //$old_end = new DateTime($res['end']);
    $old_end = $res['end'];

  $sql = "UPDATE reservations SET name= ?, start = ?, end =?, comentary = ?, date_modified = ?, user_modified = ?, visitors = ?  WHERE  id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sssssssi', $name, $start, $end, $comentary, $date_modified, $user_modified, $visitors, $id);
  $result = $stmt->execute();


    // SI S > OS = DELETE DONE
    if (new DateTime($start) > new DateTime($old_start)) {
        //echo "DELETE OS-S";
        $range = getDatesInRangeB($old_start, $start);
        foreach ($range as $date) {
            //var_dump($date);
            $sql = "DELETE FROM confirmantions WHERE date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $date);
            $result = $stmt->execute();
        }
    }
    // SI S < OS = INSERT DONE
    if (new DateTime($start) < new DateTime($old_start)) {
        //echo "INSERT S-OS";
        $range = getDatesInRangeB($start, $old_start);
        foreach ($range as $date) {
            //var_dump($date);
        }
    }
    // SI E > OE = DELETE DONE
    if (new DateTime($end) < new DateTime($old_end)) {
        //echo "DELETE E-OE";
        $range = getDatesInRangeA($end, $old_end);
        foreach ($range as $date) {
            //var_dump($date);
            $sql = "DELETE FROM confirmantions WHERE date = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $date);
            $result = $stmt->execute();
        }
    }
    // SI E > OE = INSERT DONE
    if (new DateTime($end) > new DateTime($old_end)) {
        //echo "INSERT OE-E";
        $range = getDatesInRangeA($old_end, $end);
        foreach ($range as $date) {
            //var_dump($date);
        }
    }

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
