<?php
require_once(__DIR__ . '/crest.php');
function displayValue($value)
{
  if (is_array($value)) {
    $result = '';
    foreach ($value as $item) $result .= $item . ', ';
    return $result;
  } else return $value;
}
?>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="css/app.css">
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  <script src="//api.bitrix24.com/api/v1/"></script>

  <title>Placement</title>
</head>

<body class="container">
  <?php
  $placement_options = json_decode($_REQUEST['PLACEMENT_OPTIONS'], true);

  $lead = CRest::call(
    'crm.lead.get',
    [
      'ID' => $placement_options['ID']
    ]
  );
  $results = [];
  $lead = $lead['result'];
  if ($lead['HAS_PHONE'] == 'Y') {
    $phones = $lead['PHONE'];
    // por cada celular, llamo al api para verificar duplicados
    foreach ($phones as $phone) {
      $duplicates = CRest::call(
        'crm.duplicate.findbycomm',
        [
          'entity_type' => "LEAD",
          'type' => "PHONE",
          'values' => [$phone['VALUE']]
        ],
      );
      $duplicates = $duplicates['result'];
      if (isset($duplicates['LEAD'])) {
        $duplicates = $duplicates['LEAD'];
      }
      $results = array_merge($results, $duplicates);
    }
  }
  if ($lead['HAS_EMAIL'] == 'Y') {
    $emails = $lead['EMAIL'];
    // por cada email se llama al api para verificar duplicados
    foreach ($emails as $email) {
      $duplicates = CRest::call(
        'crm.duplicate.findbycomm',
        [
          'entity_type' => "LEAD",
          'type' => "EMAIL",
          'values' => [$email['VALUE']]
        ],
      );
      $duplicates = $duplicates['result'];
      if (isset($duplicates['LEAD'])) {
        $duplicates = $duplicates['LEAD'];
      }
      $results = array_merge($results, $duplicates);
    }
  }
  $results = array_diff($results, [$placement_options['ID']]);
  $results = array_unique($results);
  $leads = [];

  if (!empty($results)) {
    $leads = crest::call(
      'crm.lead.list',
      [
        'filter' => ["ID" => $results]
      ],
    );
    $leads = $leads['result'];
    $arr = [];
    $ids = [];
    foreach ($leads as $ld) {
      $newLead = $ld;
      $contact = crest::call(
        'user.get',
        [
          "id" => (string) $ld['ASSIGNED_BY_ID']
        ]
      );
      $ids[] = $ld['ID'];
      $contact = $contact['result'][0];
      $fullName = $contact['NAME'] . ' ' . $contact['LAST_NAME'];
      $newLead['RESPONSIBLE'] = $fullName;
      $arr[] = $newLead;
    }
    $leads = $arr;
  }
  ?>
  <?php if (count($leads) < 1) : ?>
    <h1> Este lead no tiene duplicados asociados </h1>
  <?php else : ?>
    <h1> Listado de duplicados </h1>
    <table class="table table-striped">
      <tr>
        <th>ID</th>
        <th>TITULO</th>
        <th>RESPONSABLE</th>
        <th>ACCIONES</th>
      </tr>
      <?php foreach ($leads as  $val) : ?>
        <tr>
          <td><?= $val['ID']; ?></td>
          <td><?= $val['TITLE']; ?></td>
          <td><?= $val['RESPONSIBLE']; ?></td>
          <td><button id="<?= $val['ID'] ?>" class="click" type="button" class="btn btn-primary">VER</button></td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php endif; ?>
</body>
<script>
  $(document).ready(function() {
    $(".click").on("click", function(e) {
      var id = event.target.id;
      window.open('https://btx.dds.miami/crm/lead/details/' + id + '/', '_blank').focus();
    });
  });
</script>
