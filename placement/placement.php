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
  <!--  Latest compiled and minified CSS -->
  <link rel="stylesheet" href="../css/app.css">
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  <script src="//api.bitrix24.com/api/v1/"></script>
  <title>Placement</title>
</head>

<body class="container">
  <?php
  $results = [];
  $placement_options = json_decode($_REQUEST['PLACEMENT_OPTIONS'], true);
  $lead = CRest::call(
    'crm.lead.get',
    [
      'ID' => $placement_options['ID']
    ]
  );
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
          'values' => array(
            $phone['VALUE']
          )
        ],
      );
      $duplicates = $duplicates['result'];
      if (isset($duplicates['LEAD'])) {
        $duplicates = $duplicates['LEAD'];
        $results = array_merge($results, $duplicates);
      }
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
          'values' => array(
            $email['VALUE']
          )
        ],
      );
      $duplicates = $duplicates['result'];
      if (isset($duplicates['LEAD'])) {
        $duplicates = $duplicates['LEAD'];
        $results = array_merge($results, $duplicates);
      }
    }
  }
  // $results = array_diff($results, [$placement_options['ID']]);
  $results = array_unique($results);
  $leads = [];

  if (!empty($results)) {
    $leads = crest::call(
      'crm.lead.list',
      [
        'filter' => ["ID" => $results]
      ],
    );
    $status = crest::call(
      'crm.status.list',
    );
    $status = $status['result'];
    $leads = $leads['result'];
    $arr = [];
    $ids = [];
    foreach ($leads as $ld) {
      $newLead = $ld;
      $filteredStatus = array_filter($status, function ($value) use ($ld) {
        return $value['STATUS_ID'] === $ld['STATUS_ID'];
      });
      $filteredStatus = reset($filteredStatus);
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
      $newLead['STATUS'] = $filteredStatus['NAME'];
      $arr[] = $newLead;
    }
    $leads = $arr;
    $leads = array_reverse($leads);
  }
  ?>
  <?php if (count($leads) <= 1) : ?>
    <h1> Este lead no tiene duplicados asociados </h1>
  <?php else : ?>
    <div id="view">
      <h1> Listado de duplicados </h1>
      <button id="move" type="button" class="btn btn-primary pull-right">Mover a Duplicados</button>
      <table class="table table-striped">
        <tr>
          <th>ID</th>
          <th>TITULO</th>
          <th>RESPONSABLE</th>
          <th>STAGE</th>
          <th>ACCIONES</th>
        </tr>
        <?php foreach ($leads as  $key => $val) : ?>
          <?php
          if ($key === array_key_first($leads)) : ?>
            <tr class="bg-warning">
              <td><?= $val['ID']; ?></td>
              <td><?= $val['TITLE']; ?></td>
              <td><?= $val['RESPONSIBLE']; ?></td>
              <td><?= $val['STATUS']; ?></td>
              <td>
                <button id="<?= $val['ID'] ?>" class="click" type="button" class="btn btn-primary">Ver</button>
                <input class="move form-check-input" lead="<?= $val['ID'] ?>" type="checkbox" value="" id="flexCheckChecked" checked>
              </td>
            </tr>
          <?php else : ?>
            <tr>
              <td><?= $val['ID']; ?></td>
              <td><?= $val['TITLE']; ?></td>
              <td><?= $val['RESPONSIBLE']; ?></td>
              <td><?= $val['STATUS']; ?></td>
              <td>
                <button id="<?= $val['ID'] ?>" class="click" type="button" class="btn btn-primary">Ver</button>
                <input class="move form-check-input" lead="<?= $val['ID'] ?>" type="checkbox" value="" id="flexCheckChecked" checked>
              </td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </table>
      <div class="modal fade" id="mody" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <h1>Se han movido los leads.</h1>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div id="message">
      <h1>Los leads se han movido</h1>
    </div>
  <?php endif; ?>
</body>
<script>
  $(document).ready(function() {
    $('#message').hide();
    $(".click").on("click", function(e) {
      var id = event.target.id;
      window.open('https://btx.dds.miami/crm/lead/details/' + id + '/', '_blank').focus();
    });
    $("#move").on("click", function(e) {
      let arr = [];
      $('.move').each(function() {
        if ($(this).is(':checked')) {
          let value = $(this).attr('lead');
          moveLead(value);
        }
      });
    });
    async function moveLead(id) {
      await $.ajax({
        url: 'https://btx.dds.miami/rest/10476/1mewdlanh4kgrnos/crm.lead.update.json?ID=' + id + '&FIELDS[STATUS_ID]=9',
        type: 'GET',
        success: function(data) {
          return data;
        },
        dataType: 'json',
      });
      $('#view').hide();
      $('#message').show();
    }
  });
</script>
