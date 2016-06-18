<?php
//
// Copyright (c) 2014, Pinterest
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class PCMDBServiceMappingsException extends Exception {}

class PCMDBServiceMappings implements HostAPI
{

    public function getList()
    {
        $results = array();
        $urlappend = 'ui/getsvcmappings.json';
        $data = $this->getdata($urlappend);
        foreach ($data['svcmap'] as $key => $keydata) {
            if (!in_array($key, $results)) {
                array_push($results, $key);
            }
        }
        sort($results);
        return array_flip($results);
    }

    public function getInput()
    {
        return null;
    }

    public function getSearchResults($input)
    {
        $param = $input->srchparam;
        $urlappend = 'api/cmdb/getnagioshosts/servicemapping/' . $param;
        $results = $this->getdata($urlappend);
        return $results;
    }

    private function getdata($urlappend)
    {
        $url = PCMDB_URL . $urlappend;
        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new PCMDBServiceMappingsException($errno." ".$errstr);
        }
        return json_decode($response, true);
    }

}
