<?php
$crm = $_GET['crm'];
if ($crm == "DASO"){
    require_once(__DIR__ . '/CRestDASO.php');
}if ($crm == "DDS"){
    require_once(__DIR__ . '/CRestDDS.php');
}if ($crm == "ECL"){
    require_once(__DIR__ . '/CRestECL.php');
}
//require_once(__DIR__ . '/crest.php');

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

    $code = $_GET['code'];
    $deal_id = $_GET['deal_id'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE reservations set  code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $code);
    $result = $stmt->execute();



    $conn->close();

} catch (Exception $e) {
    $response = array(
        'message' => $e->getMessage()
    );
    echo json_encode($response);
}

//$deal_id = '55066';

if ($crm == "DASO"){
    $sms = CRestDASO::call(
        'bizproc.workflow.start',
        [
            'TEMPLATE_ID' => 233,
            'DOCUMENT_ID' => [
                'crm',
                'CCrmDocumentDeal',
                $deal_id
            ],
            'PARAMETERS' => [
                'TEXT' => 'Your Room Code is '.$code
            ]
        ]
    );
    echo "DASO MESSENGE SUCCESSFULLY";
}

if ($crm == "DDS"){
    $sms = CRestDDS::call(
        'bizproc.workflow.start',
        [
            'TEMPLATE_ID' => 426,
            'DOCUMENT_ID' => [
                'crm',
                'CCrmDocumentDeal',
                $deal_id
            ],
            'PARAMETERS' => [
                'TEXT' => 'Your Room Code is '.$code
            ]
        ]
    );
    echo "DDS MESSENGE SUCCESSFULLY";
}

if ($crm == "ECL"){
    $sms = CRestECL::call(
        'bizproc.workflow.start',
        [
            'TEMPLATE_ID' => 164,
            'DOCUMENT_ID' => [
                'crm',
                'CCrmDocumentDeal',
                $deal_id
            ],
            'PARAMETERS' => [
                'TEXT' => 'Your Room Code is '.$code
            ]
        ]
    );
    echo "ECL MESSENGE SUCCESSFULLY";
}


die();