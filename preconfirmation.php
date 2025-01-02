<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'vendor/autoload.php';


// Handle form submission


// obtengo todos los eventos
$results = [];

  $desde =  date('Y-m-d');

  $hasta =  date('Y-m-d');


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


    if ($build == 0){
      $sql = "SELECT confirmantions.id AS confirm_id, confirmantions.date AS confirm_date, confirmantions.ID_reservations AS ID_reservations, reservations.id AS reservation_id, reservations.name AS reservation_name, reservations.status, reservations.start, reservations.end, reservations.deal_id, reservations.comentary, reservations.crm, apartments.name AS apartment_name, buildings.name AS building_name
    FROM confirmantions 
    JOIN 
    reservations ON confirmantions.ID_reservations = reservations.id
    JOIN 
    apartments ON reservations.apartment_id = apartments.id
    JOIN 
    buildings ON apartments.building_id = buildings.id
    where CURDATE() = confirmantions.date AND reservations.status = 'reserved' AND confirmantions.prestatus = 'unconfirmed' ";
  }

  else{
      $sql = "SELECT confirmantions.id AS confirm_id, confirmantions.date AS confirm_date, confirmantions.ID_reservations AS ID_reservations, reservations.id AS reservation_id, reservations.name AS reservation_name, reservations.status, reservations.start, reservations.end, reservations.deal_id, reservations.comentary, reservations.crm, apartments.name AS apartment_name, buildings.name AS building_name
    FROM confirmantions 
    JOIN 
    reservations ON confirmantions.ID_reservations = reservations.id
    JOIN 
    apartments ON reservations.apartment_id = apartments.id
    JOIN 
    buildings ON apartments.building_id = buildings.id
    where CURDATE() = confirmantions.date AND reservations.status = 'reserved' AND buildings.id =".$build ;
  }


  $result = mysqli_query($conn, $sql);
  $results = [];

  if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while ($res = mysqli_fetch_assoc($result)) {


        $results[] =
        [
          'id' => $res['reservation_id'],
          'confirm_id' => $res['confirm_id'],
          'deal_id' => $res['deal_id'],
          'name' => $res['reservation_name'],
          'start' => $res['start'],
          'end' => $res['end'],
          'comentary' => $res['comentary'],
          'apartment_name' => $res['apartment_name'],
            'button_green' => "<form method='POST' style='display:inline;' onsubmit='handleFormSubmit(event)'>
                    <input type='hidden' name='confirm_id' value='{$res['confirm_id']}'>
                    <input type='hidden' name='prestatus' value='CONFIRMED'>
                    <button type='submit' class='buttonGreen'>Room in Use</button>
                   </form>",
            'button_red' => "<form method='POST' style='display:inline;' onsubmit='handleFormSubmit(event)'>
                   <input type='hidden' name='confirm_id' value='{$res['confirm_id']}'>
                   <input type='hidden' name='prestatus' value='EMPTY'>
                   <button type='submit' class='buttonRed'>Empty Room</button>
                 </form>",


        ];
    }
  }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_id'])) {
    $confirm_id = $_POST['confirm_id'];
    $prestatus = $_POST['prestatus'];
    myPhpMethod($confirm_id, $conn, $prestatus);
}

    function myPhpMethod($confirm_id , $conn, $prestatus) {
        //echo "PHP Method executed for Confirm ID: $confirm_id";
        $sql = "UPDATE confirmantions SET prestatus = ? WHERE id = ?";

        // Prepare the SQL statement
        $stmt = $conn->prepare($sql);

        // Bind parameters: s for string, i for integer
        $stmt->bind_param("si", $prestatus, $confirm_id);

        // Execute the query
        $stmt->execute();
        //var_dump($result);

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
              'apartment_name' => "",
              'button_green' => "",
              'button_red' => ""
          ];
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
    <h2>Today Reservations</h2>
    <!-- Filtros de Fecha -->
    <form action="preconfirmation.php" autocomplete="off" method="GET">
        <label for="build">Building:</label>
        <select name="build" id="build-select">
            <option value="0" <?= isset($_GET['build']) && $_GET['build'] == '0' ? 'selected' : '' ?>>All</option>
            <option value="1" <?= isset($_GET['build']) && $_GET['build'] == '1' ? 'selected' : '' ?>>2268 NW</option>
            <option value="2" <?= isset($_GET['build']) && $_GET['build'] == '2' ? 'selected' : '' ?>>North Miami</option>
        </select>
      <button submit id="filtrar">Filter</button>
    </form>
    <table id="tablaPacientes" class="display">
      <thead>
        <tr>
          <th>Name</th>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Comment</th>
          <th>Apartment</th>
          <th></th>
          <th></th>
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
              <?= $result['apartment_name']; ?>
            </td>
            <td>
                <?= $result['button_green']; ?>
            </td>
            <td>
                <?= $result['button_red']; ?>
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

<style>
    .buttonGreen{
        background-color: #04AA6D; /* Green */
        border: none;
        border-radius: 8px;
        color: white;
        padding: 10px 24px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
    }
    .buttonRed{
        background-color: #ef0606; /* Green */
        border: none;
        border-radius: 8px;
        color: white;
        padding: 10px 10px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
    }
</style>

<script>
    function handleFormSubmit(event) {
        event.preventDefault(); // Prevent the default form submission
        const form = event.target; // Get the form element that triggered the event

        // Submit the form data manually
        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
        }).then(response => {
            if (response.ok) {
                // Reload the page after successful submission
                location.reload();
            } else {
                alert('Something went wrong. Please try again.');
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please check your network connection.');
        });
    }
</script>

