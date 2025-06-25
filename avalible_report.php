<?php

//require_once(__DIR__ . '/crest.php');
header("Access-Control-Allow-Origin: *");
error_reporting(0);
ini_set('display_errors', '0');

$ini = parse_ini_file('app.ini');
$servername = $ini['servername'];
$username = $ini['db_user'];
$password = $ini['db_password'];
$dbname = $ini['db_name'];

if (!$_REQUEST['AUTH_ID']) {
    die("Open from Bitrix24");
}

$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT r.start, r.end , a.id AS apartment_id FROM reservations r JOIN apartments a ON r.apartment_id = a.id JOIN buildings b ON a.building_id = b.id WHERE r.end >= CURDATE() AND r.status != 'deleted'";
$result = mysqli_query($conn, $sql);

$reservations = [];

if (mysqli_num_rows($result) > 0) {
    while ($res = mysqli_fetch_assoc($result)) {
        $reservations[] =
            [
                'start' => $res['start'],
                'end' => $res['end'],
                'apartment_id' => $res['apartment_id'],
            ];
    }
}

$apts = [];

$sql = "SELECT a.id as apartment_id, a.name as apartment_name, b.name as build_name  FROM apartments a JOIN buildings b ON a.building_id = b.id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($res = mysqli_fetch_assoc($result)) {
        $apts[] = [
            'apartment_id' => $res['apartment_id'],
            'apartment_name' => $res['apartment_name'],
            'build_name' => $res['build_name'],
        ];
    }
}

mysqli_close($conn);

function getAvailablesAptForTomorrow($reservations, $apts) {
    // Calcula el rango de fechas para mañana (desde 00:00:00 hasta 23:59:59)
    $start = strtotime('today 00:00:00');
    $end = strtotime('today 23:59:59');

    //$available = [['N/A', 'N/A']];

    foreach ($apts as $apartment) {
        $apartment_id = $apartment['apartment_id'];
        $apartment_name = $apartment['apartment_name'];
        $build_name = $apartment['build_name'];

        $overlap = false;
        foreach ($reservations as $reservation) {
            if ($reservation['apartment_id'] === $apartment_id) {
                $start_reservation = strtotime($reservation['start']);
                $end_reservation = strtotime($reservation['end']);

                if ($start <= $end_reservation && $end >= $start_reservation) {
                    $overlap = true;
                    break;
                }
            }
        }

        if (!$overlap) {
            $available[] = [$apartment_id, $apartment_name, $build_name];
        }
    }

    return $available;
}

$disponibles = getAvailablesAptForTomorrow($reservations, $apts);

$results = json_encode($disponibles);

//var_dump($disponibles);



//echo $results;

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Today Available Apartments</title>
  <!-- Incluir DataTables CSS y jQuery -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/jquery.dataTables.min.css">
  <!-- jQuery UI CSS para el Datepicker -->
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
  <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
</head>

<body>
  <div class="container">
    <h2>Today Available Apartments</h2>
    <table id="tablaApt" class="display">
      <thead>
        <tr>
          <th>Apartment</th>
          <th>Building</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($disponibles as $result) : ?>
          <tr>
            <td>
              <?= $result[1]; ?>
            </td>
            <td>
              <?= $result[2]; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
      <br>
      <br>
      <br>
    </table>
  </div>
  <script>
    // Inicializar el DataTable cuando la página esté lista
    $(document).ready(function() {
      // Insertar datos en la tabla
      var table = $('#tablaApt').DataTable(
          {
              pageLength: 100,         // Cantidad de filas por página
              order: [[1, 'asc']]     // Ordenar por la primera columna (ID) ascendente
          }
      );
    });

  </script>
</body>

</html>
