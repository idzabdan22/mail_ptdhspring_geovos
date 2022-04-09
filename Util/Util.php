<?php

class Util {
    public function get_server_time($zone) {
        date_default_timezone_set($zone);
        $time_stamp = date('m/d/Y h:i:s A', time());
        return $time_stamp;
    }

    public function send_to_log($filename, $data){
        $this->data = implode(" - ", $data);
        $this->data .= PHP_EOL;
        file_put_contents($filename, $this->data, FILE_APPEND);
    }
}

?>