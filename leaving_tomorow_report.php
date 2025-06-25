<?php
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

$start = date('Y-m-d\\T00:00:00', strtotime('tomorrow'));
$end   = date('Y-m-d\\T23:59:59', strtotime('tomorrow'));


$sql = "SELECT  a.name as apartment, r.name AS patient_name, b.comentary as building
        FROM reservations r
        JOIN apartments a ON r.apartment_id = a.id
        JOIN buildings b ON a.building_id = b.id
        WHERE r.end BETWEEN '$start' AND '$end'
          AND r.status != 'deleted'";

$result = mysqli_query($conn, $sql);


$reservations = [];

if (mysqli_num_rows($result) > 0) {
    while ($res = mysqli_fetch_assoc($result)) {
        $reservations[] =
            [
                'patient_name' => $res['patient_name'],
                'apartment' => $res['apartment'],
                'building' => $res['building'],
            ];
    }
}

//var_dump($reservations);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaving Tomorrow</title>
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
    <h2>Leaving Tomorrow</h2>
    <table id="tablaApt" class="display">
        <thead>
        <tr>
            <th>Name</th>
            <th>Apartment</th>
            <th>Building</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($reservations as $result) : ?>
            <tr>
                <td>
                    <?= $result['patient_name']; ?>
                </td>
                <td>
                    <?= $result['apartment']; ?>
                </td>
                <td>
                    <?= $result['building']; ?>
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
                order: [[2, 'asc']]     // Ordenar por la primera columna (ID) ascendente
            }
        );
    });

</script>
</body>

</html>