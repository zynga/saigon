<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CDC_RVMException extends Exception {}

class CDC_RVM {

    private $url;
    private $apipath = '/client/api?';
    private $command = 'command=listRouters&response=json';
    private $baseurl;
    private $apikey;
    private $secret;
    private $signature;

    public function __construct($dccreds) {
        $this->baseurl = $dccreds['baseurl'];
        $this->apikey = $dccreds['apikey'];
        $this->secret = $dccreds['secret'];
        $data = strtolower("apikey=" . $this->apikey . "&" . $this->command);
        $this->signature = hash_hmac("sha1", $data, $this->secret);
        $this->url = $this->baseurl . $this->apipath . $this->command . "&apiKey=" . $this->apikey . "&signature=" . $this->signature;
        return;
    }

    public function fetchData() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 900);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $errstr = curl_errno($ch);
        curl_close($ch);
        if ($errno) {
            throw new CDC_RVMException($errno." ".$errstr);
        } else {
            $responseDec = json_decode($response);
            return $responseDec;
        }
    }

}

