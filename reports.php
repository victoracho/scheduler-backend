<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function exportar($results)
{

  // Crear un nuevo archivo de Excel
  $spreadsheet = new Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();

  // Escribir los encabezados de la tabla
  $sheet->setCellValue('A1', 'Name');
  $sheet->setCellValue('B1', 'Start Date');
  $sheet->setCellValue('C1', 'End Date');
  $sheet->setCellValue('D1', 'Comment');
  $sheet->setCellValue('E1', 'CRM');
  $sheet->setCellValue('F1', 'Apartment');
  $sheet->setCellValue('G1', 'Building');
  $sheet->setCellValue('H1', 'Deal_ID');

  // Escribir los datos de los pacientes
  $row = 2; // Empezamos en la fila 2 porque la 1 es para los encabezados
  foreach ($results as $paciente) {

    $sheet->setCellValue('A' . $row, $paciente['name']);
    $sheet->setCellValue('B' . $row, $paciente['start']);
    $sheet->setCellValue('C' . $row, $paciente['end']);
    $sheet->setCellValue('D' . $row, $paciente['comentary']);
    $sheet->setCellValue('E' . $row, $paciente['crm']);
    $sheet->setCellValue('F' . $row, $paciente['apartment_name']);
    $sheet->setCellValue('G' . $row, $paciente['building_name']);
    $sheet->setCellValue('H' . $row, $paciente['deal_id']);
    $row++;
  }

  // Configurar el archivo para la descarga
  $filename = "reservations resport " . date('Y-m-d') . ".xlsx";

  // Enviar el archivo al navegador para la descarga
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition: attachment;filename='.$filename);
  header('Cache-Control: max-age=0');
  header('Cache-Control: max-age=1'); // Requerido para IE11 y versiones anteriores

  // Evitar almacenamiento en caché
  header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
  header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
  header('Cache-Control: cache, must-revalidate'); // Para HTTP/1.1
  header('Pragma: public'); // Para HTTP/1.0

  // Crear el escritor de Excel y guardar el archivo en la salida
  $writer = new Xlsx($spreadsheet);
  $writer->save('php://output');

}



// obtengo todos los eventos
$results = [];
if (isset($_GET['desde']) && $_GET['desde'] != null) {

  $desde =  $_GET['desde'];
  $fechaObj = DateTime::createFromFormat('d/m/Y', $desde);
  $formatoISO = $fechaObj->format('Y-m-d\TH:i:s');


  $desde = DateTime::createFromFormat('d/m/Y', $desde);
  $desde = $desde->format('Y-m-d');

  $hasta =  $_GET['hasta'];
  $hasta = DateTime::createFromFormat('d/m/Y', $hasta);
  $hasta = $hasta->format('Y-m-d');

  if ($desde == $hasta) {
    $desde = $desde . 'T00:00:00';
    $hasta = $hasta . 'T23:59:00';
  }

    $ini = parse_ini_file('app.ini');
    $servername = $ini['servername'];
    $username = $ini['db_user'];
    $password = $ini['db_password'];
    $dbname = $ini['db_name'];

  // Create connection
  $conn = mysqli_connect($servername, $username, $password, $dbname);
  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  if (empty($status)) {
    $status = null;
  }

    $build =  $_GET['build'];
    $apt =  $_GET['apt'];

    if ( $apt == '' && $build == 0){
        $sql = "SELECT reservations.id AS reservation_id, reservations.name AS reservation_name, reservations.status, reservations.start, reservations.end, reservations.deal_id, reservations.comentary, reservations.crm, apartments.name AS apartment_name, buildings.name AS building_name
    FROM reservations 
    JOIN 
    apartments ON reservations.apartment_id = apartments.id
    JOIN 
    buildings ON apartments.building_id = buildings.id
    where start between '$desde' AND '$hasta' AND reservations.status = 'reserved'";
    }

  else if ($build == 0){
      $sql = "SELECT reservations.id AS reservation_id, reservations.name AS reservation_name, reservations.status, reservations.start, reservations.end, reservations.deal_id, reservations.comentary, reservations.crm, apartments.name AS apartment_name, buildings.name AS building_name
    FROM reservations 
    JOIN 
    apartments ON reservations.apartment_id = apartments.id
    JOIN 
    buildings ON apartments.building_id = buildings.id
    where start between '$desde' AND '$hasta' AND reservations.status = 'reserved' AND apartments.name =".$apt;
  }

  else if ($apt == ''){
      $sql = "SELECT reservations.id AS reservation_id, reservations.name AS reservation_name, reservations.status, reservations.start, reservations.end, reservations.deal_id, reservations.comentary, reservations.crm, apartments.name AS apartment_name, buildings.name AS building_name
    FROM reservations 
    JOIN 
    apartments ON reservations.apartment_id = apartments.id
    JOIN 
    buildings ON apartments.building_id = buildings.id
    where start between '$desde' AND '$hasta' AND reservations.status = 'reserved' AND buildings.id =".$build;
  }

  else{
      $sql = "SELECT reservations.id AS reservation_id, reservations.name AS reservation_name, reservations.status, reservations.start, reservations.end, reservations.deal_id, reservations.comentary, reservations.crm, apartments.name AS apartment_name, buildings.name AS building_name
    FROM reservations 
    JOIN 
    apartments ON reservations.apartment_id = apartments.id
    JOIN 
    buildings ON apartments.building_id = buildings.id
    where start between '$desde' AND '$hasta' AND reservations.status = 'reserved' AND buildings.id =".$build." AND apartments.name =".$apt ;
  }


  $result = mysqli_query($conn, $sql);
  $results = [];

  if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while ($res = mysqli_fetch_assoc($result)) {

      $from = new DateTime($res['start']);
      $from = $from->format("Y/m/d H:i");

      $end = new DateTime($res['end']);
      $end = $end->format("Y/m/d H:i");

      $link = '';
      if ($res['crm'] == 'DASO'){
          $link = "https://daso.dds.miami/crm/deal/details/".$res['deal_id']."/";
      }
      if ($res['crm'] == 'DDS'){
            $link = "https://dds.miami/crm/deal/details/".$res['deal_id']."/";
      }
      if ($res['crm'] == 'ECL'){
            $link = "https://crm.eyescolorlab.com/crm/deal/details/".$res['deal_id']."/";
      }

        $results[] =
        [
          'id' => $res['reservation_id'],
          'deal_id' => $res['deal_id'],
          'name' => $res['reservation_name'],
          'start' => $from,
          'end' => $end,
          'comentary' => $res['comentary'],
          'crm' => $res['crm'],
          'apartment_name' => $res['apartment_name'],
          'building_name' => $res['building_name'],
          'link' => $link,
        ];
    }
  }
  mysqli_close($conn);

  if (empty($results)) {
    //$results[] = [];
      $results[] =
          [
              'id' => "",
              'deal_id' => "",
              'name' => "",
              'start' => "",
              'end' => "",
              'comentary' => "No data available in table",
              'crm' => "",
              'apartment_name' => "",
              'building_name' => "",
          ];
  }
}

if (isset($_GET['exportar'])) {
  exportar($results);
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pacientes - DataTable</title>
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
    <h2>Reservations Report</h2>
    <!-- Filtros de Fecha -->
    <form action="reports.php" autocomplete="off" method="GET">
      <label for="fecha_desde">FROM DATE:</label>
      <input type="text" id="desde" name="desde" placeholder="Select Date From">
      <label for="fecha_hasta">TO DATE:</label>
      <input type="text" id="hasta" name="hasta" placeholder="Select Date To">
        <label for="build">Building:</label>
        <select name="build" id="build-select">
            <option value="0">All</option>
            <option value="1">2268 NW</option>
            <option value="2">North Miami</option>
        </select>
        <label for="apt" >Apt:</label>
        <input type="number" placeholder="All" id="apt" name="apt">
      <button submit id="filtrar">Filter</button>
      <button submit id="exportar" name="exportar" type="submit">Export</button>
    </form>
    <table id="tablaPacientes" class="display">
      <thead>
        <tr>
          <th>Name</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Comment</th>
          <th>CRM</th>
          <th>Apartment</th>
          <th>Building</th>
          <th>Deal_ID</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results as $result) : ?>
          <tr>
            <td>
              <?= $result['name']; ?>
            </td>
            <td>
              <?= $result['start']; ?>
            </td>
            <td>
              <?= $result['end']; ?>
            </td>
            <td>
              <?= $result['comentary']; ?>
            </td>
            <td>
              <?= $result['crm']; ?>
            </td>
            <td>
              <?= $result['apartment_name']; ?>
            </td>
            <td>
              <?= $result['building_name']; ?>
            </td>
            <td>
                <a href=<?= $result['link']; ?> target="_blank"><?= $result['deal_id']; ?></a>
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

        $("#desde").datepicker({
        dateFormat: 'dd/mm/yy'
      });
      $("#hasta").datepicker({
        dateFormat: 'dd/mm/yy'
      });
      // Insertar datos en la tabla
      var table = $('#tablaPacientes').DataTable();
    });
  </script>
</body>

</html>
