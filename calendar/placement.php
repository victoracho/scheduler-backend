<?php
require_once(__DIR__ . '/crest.php');
$placement_options = json_decode($_REQUEST['PLACEMENT_OPTIONS'], true);
$lead = CRest::call(
  'crm.deal.get',
  [
    'ID' => $placement_options['ID']
  ]
);

$user = CRest::call(
  'user.current',
  [
    'auth' => $_REQUEST['AUTH_ID'],
  ],
);

$lead = $lead['result'];
$user = $user['result'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Calendario de Appointments</title>
  <script src="//api.bitrix24.com/api/v1/"></script>
  <script type="module" crossorigin src="assets/index-7ea0a578.js"></script>
  <script type="module" crossorigin src="assets/index-7ea0a578.js"></script>
  <link rel="stylesheet" href="assets/index-7cf7529a.css">
  <script>
    var currentSize = BX24.getScrollSize();
    BX24.resizeWindow(currentSize.scrollWidth, currentSize.scrollHeight);
    let user = <?php echo json_encode($user); ?>;
    let deal = <?php echo json_encode($lead); ?>;
  </script>
</head>

<body>
  <div id="app"></div>
</body>

</html>
