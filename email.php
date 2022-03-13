<?php

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$url = "https://app.argatech.com/api/sensors";

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$headers = array(
   "Api-key: HZzJfm0H_qVQ4sJO4ZPVNDt0Idn4Ciyv8FEfNoT2M5CSApLpj34JLWqWwFU=",
   "Api-signature: r1zScrPLszZJ2YKO8_",
   "Content-Type: application/json",
);

curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//for debug only!
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

$json = curl_exec($curl);
curl_close($curl);
$data = json_decode($json);
$timezone = 'Asia/Makassar';
date_default_timezone_set($timezone);
$time_stamp = date('m/d/Y h:i:s A', time());

$info = "<table style=\"width: 100%; text-align: center; border: 1px solid black; border-collapse: collapse; table-layout: fixed\">
            <tr >
                <th colspan = \"2\", 
                style=\"background-color: #ff2a15;
                color: #ffffff\">TSEL GEOVOS</th>
            </tr>
            <tr >
                <th colspan = \"2\", 
                style=\"background-color: #ff2a15;
                color: #ffffff\">$time_stamp</th>
            </tr>
            <tr >
                <th style=\"border: 1px solid black; 
                background-color:#999999;
                color: #ffffff\">Sensor</th>
                <th style=\"border: 1px solid black; 
                background-color: #999999;
                color: #ffffff\">Data</th>
            </tr>";
$temp = "";
$satuan = array ("","mg/L","m3/H","mm","°C","cm/s");
$index = 0;
foreach($data as $item) { 
    if($item->name === "Temp"){ // temporary
        $index++;
        continue;
    }
    $info .= "<tr><td style=\"border: 1px solid black\"><b>$item->name</b></td>";
    $temp = $item->data[0]." ".$satuan[$index];	
    $info .= "<td style=\"border: 1px solid black\"><b>$temp</b></td></tr>";
    $index++;
}
$info .= "</table>";

$mail = new PHPMailer(true);

try {
    //Server settings
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
    );                                   //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //Recipients
    $mail->setFrom('notifications@intank.id', 'Tsel Geovos');
    $mail->addAddress('abdan.idza2345@gmail.com');
    $mail->addAddress('danang.sulthoni@gmail.com');  
    $mail->addAddress('danang_sulthoni@telkomsel.co.id');  
    $mail->addAddress('ridwanssyh@gmail.com');  
    // $mail->addAddress('ellen@example.com');               //Name is optional
    // $mail->addReplyTo('info@example.com', 'Information');
    // $mail->addCC('cc@example.com');
    // $mail->addBCC('bcc@example.com');

    //Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name     $mail->addAddress('ridwanssyh@gmail.com');  

    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Hourly Sensor Data';
    $mail->Body    = $info;
    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>

