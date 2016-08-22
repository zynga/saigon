<?php
//
// Copyright (c) 2014, Pinterest
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//
class PCMDBGlobException extends Exception {}

class PCMDBGlob implements HostAPI
{

    public function getList()
    {
        return null;
    }

    public function getInput()
    {
        return 'PCMDB Glob';
    }

    public function getSearchResults($input)
    {
        $param = $input->srchparam;
        $urlappend = 'api/cmdb/getnagioshosts';
        $payload = array();
        $payload['type'] = 'nameglob';
        $payload['value'] = 'state:running AND config.name:' . $param;
        $results = $this->getdata($urlappend, $payload);
        return $results;
    }

    private function getdata($urlappend, $payload)
    {
        $url = PCMDB_URL . $urlappend;
        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new PCMDBGlobException($errno." ".$errstr);
        }
        return json_decode($response, true);
    }

}

