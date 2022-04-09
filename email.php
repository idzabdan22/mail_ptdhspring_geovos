<?php

require_once 'PHPMailer-master/src/Exception.php';
require_once 'PHPMailer-master/src/PHPMailer.php';
require_once 'PHPMailer-master/src/SMTP.php';
require_once 'Util/Request.php';
require_once 'Util/Util.php';

include 'config_alert.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$ut = new Util();
$email_type = 'Hourly Sensor Data';
$mail_log = 'mailstatus.log';
$request_log = 'apierror.log';

$url = "https://app.argatech.com/api/sensors";
$headers = array(
   "Api-key: HZzJfm0H_qVQ4sJO4ZPVNDt0Idn4Ciyv8FEfNoT2M5CSApLpj34JLWqWwFU=",
   "Api-signature: r1zScrPLszZJ2YKO8_",
   "Content-Type: application/json",
   "Api-version: 2"
);

$req = new Request($url, $headers);
if ($req->get_code_request() != "200 OK") {
    $ut->send_to_log($request_log, array(
        $ut->get_server_time('Asia/Jakarta'),
        $url.' /GET',
        $req->get_code_request()
    ));
} else {
    $json = $req->get_response_data();
}

$data = json_decode($json);
$time = $ut->get_server_time('Asia/Makassar');

$info = "<table style=\"width: 100%; text-align: center; border: 1px solid black; table-layout: fixed; border-collapse: collapse\">
            <tr >
                <th colspan = \"2\", 
                style=\"background-color: #ff2a15;
                color: #ffffff\">TSEL GEOVOS</th>
            </tr>
            <tr >
                <th colspan = \"2\", 
                style=\"background-color: #ff2a15;
                color: #ffffff\">$time (WITA)</th>
            </tr>
            <tr >
                <th style=\"border: 1px solid black; 
                background-color:#999999;
                color: #ffffff\">Sensor</th>
                <th style=\"border: 1px solid black; 
                background-color: #999999;
                color: #ffffff\">Data</th>
            </tr>";

$satuan = array (" ","mg/L","m3/H","mm","°C","cm/s");
$index = 0;
foreach($data as $item) { 
    if($item->name === "Temp"){
        $index++;
        continue;
    }
    $info .= "<tr><td style=\"border :1px solid black\"><b>$item->name</b></td>";
    $temp = $item->data[0]->value." ".$satuan[$index];
    $info .= "<td style=\"border: 1px solid black\"><b>$temp</b></td></tr>";
    $index++;
}
$info .= "</table>";

$mail = new PHPMailer(true);
try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = gethostbyname('mail.intank.id');                     //Set the SMTP server to send through
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'notifications@intank.id';                     //SMTP username //notifications@intank.id //notif@intank.id
    $mail->Password   = 'YMPG2sAV2tczZv6';      
    $mail->SMTPSecure = 'ssl';            //Enable implicit TLS encryption
    $mail->Port       = 465; 
    $mail->SMTPOptions = array(
        'ssl' => array(
        'verify_peer' => false, //false
        'verify_peer_name' => false, //false
        'allow_self_signed' => true //true
        )
    );                                   

    $mail->setFrom('notifications@intank.id', 'Tsel Geovos');
    foreach($email as $en){
        $mail->addAddress($en);
    }
    $mail->addBCC('abdan.idza2345@gmail.com');

    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = $email_type;
    $mail->msgHTML($info);
    $mail->send();
} catch (Exception $e) {
    $st = $mail->ErrorInfo;
    $ut->send_to_log($mail_log, array(
        $ut->get_server_time('Asia/Jakarta'),
        $st
    ));
}

?>

