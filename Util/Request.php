<?php

class Request {
    public $url, $headers, $response_data, $response;
    private $session;

    public function __construct($url, $header) {
        $this->session = curl_init($this->url=$url);
        curl_setopt_array($this->session, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER =>  $this->headers=$header,
            CURLOPT_SSL_VERIFYHOST => false,            
            CURLOPT_SSL_VERIFYPEER => false
        ));
    }

    public function get_code_request() {
        $this->response_data = curl_exec($this->session);
        switch ($this->response = curl_getinfo($this->session, CURLINFO_HTTP_CODE)) {
            case 404:
                $this->response = "404 Not Found";
                break;
            case 403:
                $this->response = "403 Forbidden";
                break;
            case 301:
                $this->response = "301 Moved Permanently";
                break;
            case 200:
                $this->response = "200 OK";
                break;    
            default:
                $this->response = "Couldn't Resolve Host";
                break;
        }
        curl_close($this->session);
        return $this->response;
    }

    public function get_response_data(){
        return $this->response_data;
    }
}

?>