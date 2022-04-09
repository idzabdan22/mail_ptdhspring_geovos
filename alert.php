<?php

    ini_set('display_errors', 1);
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
    $request_log = 'alertapierror.log';
    $database = 'db.alert';

    function update_state($st){
        $text = file_get_contents('db.alert');
        $data = json_decode($text);
        switch ($st) {
            case "c":
                $data->status = "c";
                break;
            case "w":
                $data->status = "w";
                break;
            case "":
                $data->status = "";
                break;
            default:
                break;
        }
        file_put_contents('db.alert', json_encode($data));
    }

    function update_cnt($s){
        // r = reset counter
        // o = reset counter to 1
        // i = increment 1 to counter
        $text = file_get_contents('db.alert');
        $data = json_decode($text);
        switch ($s) {
            case 'r':
                $data->counter = 0;
                break;
            case 'o':
                $data->counter = 1;
                break;
            case 'i':
                $data->counter++;
                break;
            default:
                break;
        }
        file_put_contents('db.alert', json_encode($data));
    }

    function get_cnt(){
        $text = file_get_contents('db.alert');
        $data = json_decode($text);
        return $data->counter;
    }

    function get_stat(){
        $text = file_get_contents('db.alert');
        $data = json_decode($text);
        return $data->status;
    }

    function process_alert($ph){
        global $warning_period, $critical_period, $warning_ph, $critical_ph, $period;
        if($ph >= $warning_ph){ 
            if($ph >= $critical_ph){
                if(get_stat() == "w" || get_stat() == ""){
                    update_cnt('r');
                    update_state("c");
                }
                $period = $critical_period;
            }
            else{
                if(get_stat() == "c" || get_stat() == ""){
                    update_cnt('r');
                    update_state("w");
                }
                $period = $warning_period;
            }
            update_cnt('i');
            $period = ($period/2) + 1;
        }
        else {
            update_state("");
            update_cnt('r');
            $period = 0;
        }
        if(get_cnt() == 1 || (get_cnt() == $period && $period != 0)){
            if(get_stat() == "w"){
                $email_typ = 'Warning Alert Geovos';
                $inf = 'Warning Alert';
                $clr = '#f9e00a';
                $icn = 'https://i.ibb.co/Jv3WwTr/alert-warning.png';
            } else {
                $email_typ = 'Critical Alert Geovos';
                $inf = 'Critical Alert';
                $clr = '#ff2a15';
                $icn = 'https://i.ibb.co/FKpTF58/alert-critical.png';
            }
            send_email($email_typ, $inf, $clr, $icn, $ph);
            if(get_cnt() == $period) update_cnt('o');
        }
    }

    function send_email($email_type, $info, $color, $icon, $ph){
        global $email, $ut;
        $mail_log = 'alertmailstatus.log';
        $timeinfo = explode(' ', $ut->get_server_time('Asia/Makassar'));
        $calendar = $timeinfo[0];
        $time = $timeinfo[1].' '.$timeinfo[2].' (WITA)';

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

            $mail->isHTML(true);                                  
            $mail->Subject = $email_type;
            $template = file_get_contents('templates/alert_mail.html');
            $attr = array('timestamp'=>$calendar, 'time'=>$time, 'ph'=>$ph,
                        'gambar_alert'=>$icon, 'status'=>$info, 'colors'=>$color
            );

            foreach($attr as $name => $val){
                $template = str_replace($name, $val, $template);
            }

            $mail->msgHTML($template);
            $mail->send();
        }
        catch (Exception $e){
            $st = $mail->ErrorInfo;
            $ut->send_to_log($mail_log, array(
                $ut->get_server_time('Asia/Jakarta'),
                $st
            ));
        }
    }

    $url = "https://app.argatech.com/api/sensors?limitData=20";
    $headers = array(
        "Api-key: HZzJfm0H_qVQ4sJO4ZPVNDt0Idn4Ciyv8FEfNoT2M5CSApLpj34JLWqWwFU=",
        "Api-signature: r1zScrPLszZJ2YKO8_",
        "Api-version: 2",
        "Content-Type: application/json"
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
    foreach($data as $item) { 
        if($item->name === "pH"){
            process_alert($item->data[0]->value);
            break;
        }
    }

?>