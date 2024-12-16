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

    $id = $_GET['id'];
    $id = preg_replace('~\D~', '', $id);
    $code = $_GET['code'];
    $apt = $_GET['apt'];
    $deal_id = $_GET['deal_id'];

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "UPDATE reservations set  code = ? WHERE  id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $code, $id);
    $result = $stmt->execute();



    $conn->close();

} catch (Exception $e) {
    $response = array(
        'message' => $e->getMessage()
    );
    echo json_encode($response);
}

//$deal_id = '55066';
$sms_end = 'Today is your reservation in Apartment '.$apt.' your Entry Code is '.$code;

if ($crm == "DASO"){
    $sms_text = "Hi we are you Plastic Surgery Clinic, " . $sms_end;
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
                'TEXT' => $sms_text
            ]
        ]
    );


    $result = CRestDASO::call(
        'crm.activity.list',
        [
            'order' => [ 'ID' => 'DESC' ],
            'filter' => [
                'DESCRIPTION' => $sms_text,
                'SUBJECT' => "Outbound SMS message",
            ],
            'select' => [ 'ID', 'DESCRIPTION' , 'SUBJECT', ],
        ]
    );


    $act_id =$result["result"][0]["ID"];

    $result = CRestDASO::call(
        'crm.activity.delete',
        [
            'id' =>  $act_id
        ]
    );


    echo "DASO MESSENGE SUCCESSFULLY";
}

if ($crm == "DDS"){
    $sms_text = "Hi we are Dental Design Smile, " . $sms_end;
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
                'TEXT' => $sms_text
            ]
        ]
    );

    $result = CRestDDS::call(
        'crm.activity.list',
        [
            'order' => [ 'ID' => 'DESC' ],
            'filter' => [
                'DESCRIPTION' => $sms_text,
                'SUBJECT' => "Outbound SMS message",
            ],
            'select' => [ 'ID', 'DESCRIPTION' , 'SUBJECT', ],
        ]
    );


    $act_id =$result["result"][0]["ID"];

    $result = CRestDDS::call(
        'crm.activity.delete',
        [
            'id' =>  $act_id
        ]
    );

    echo "DDS MESSENGE SUCCESSFULLY";
}

if ($crm == "ECL"){
    $sms_text = "Hi we are Eye Color Lab, " . $sms_end;
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
                'TEXT' => $sms_text
            ]
        ]
    );

    $result = CRestECL::call(
        'crm.activity.list',
        [
            'order' => [ 'ID' => 'DESC' ],
            'filter' => [
                'DESCRIPTION' => $sms_text,
                'SUBJECT' => "Outbound SMS message",
            ],
            'select' => [ 'ID', 'DESCRIPTION' , 'SUBJECT', ],
        ]
    );


    $act_id =$result["result"][0]["ID"];

    $result = CRestECL::call(
        'crm.activity.delete',
        [
            'id' =>  $act_id
        ]
    );
    echo "ECL MESSENGE SUCCESSFULLY";
}


die();