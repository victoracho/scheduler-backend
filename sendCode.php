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

$conn = new mysqli($servername, $username, $password, $dbname);

$sql = "SELECT 
    b.comentary as address, a.name as apt, rv.start as start_date, rv.end as end_date, b.wifi_user as wifi_user, b.wifi_pass as wifi_pass, b.code as build_code
FROM 
    reservations AS rv
    INNER JOIN apartments AS a 
        ON rv.apartment_id = a.id
    INNER JOIN buildings AS b
        ON a.building_id = b.id
WHERE rv.id =".$id;
$stmt = $conn->prepare($sql);
$result = mysqli_query($conn, $sql);
$res = mysqli_fetch_assoc($result);

$address = $res['address'];
$apt_number = $res['apt'];
$start = $res['start_date'];
$end = $res['end_date'];
$wifi_user = $res['wifi_user'];
$wifi_pass = $res['wifi_pass'];
$build_code_txt = $res['build_code']."\n" ?? "\n";
$conn->close();

$desde = new DateTime($start);
$desde = $desde->format('F j, Y, \a\t g:i A');

$hasta = new DateTime($end);
$hasta = $hasta->format('F j, Y, \a\t g:i A');


if ($crm == "DDS"){
    $crm_text = "Dental Design Smile";
}if ($crm == "ECL"){
    $crm_text = "Eye Color Lab";
}if ($crm == "DASO"){
    $crm_text = "Daso Plastic Surgery";
}
$sms_template = "Thank you for Staying at ".$crm_text." Apartments. Located at:\n
".$address."\n
Apartment # ".$apt_number."\n
I have added an access code for you to use my lock.\n
Here's when you can use your access code:\n
Door Lock: ".$code."
".$build_code_txt."\n
".$desde." till\n  
".$hasta."\n
CHECK-OUT TIME: 11:00 AM!!\n
TO UNLOCK:\n
From the outside, press the Home logo and enter code!\n
From the inside, turn the thumb turn.\n
TO LOCK:\n
From the outside,press the Lock logo.\n
From the inside, turn the thumb to turn\n
WiFi:\n
User: ".$wifi_user."\n
Password: ".$wifi_pass."\n
";

//$sms_end = 'Today is your reservation in Apartment '.$apt.' your Entry Code is '.$code;

if ($crm == "DASO"){
    $sms_text = $sms_template;
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
    $sms_text = $sms_template;
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
    $sms_text = $sms_template;
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