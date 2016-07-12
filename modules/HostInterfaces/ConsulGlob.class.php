<?php
//
// Copyright (c) 2016
// https://github.com/mhwest13/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//
class ConsulGlobException extends Exception {}

class ConsulGlob implements HostAPI
{

    public function getList()
    {
        return null;
    }

    public function getInput()
    {
        return 'Consul Glob';
    }

    public function getSearchResults($input)
    {
        $results = array();
        $param = $input->srchparam;
        $urlappend = '/v1/catalog/nodes'
        $consul_data = $this->getdata($urlappend);
        $nodes = array();
        foreach ($consul_data as $node_data) {
          // Skip nodes that don't match our glob regex
          if (preg_match($param, $node_data['Node'])) {
            $results[$node_data['Node']]['host_name'] = $node_data['Node'];
            $results[$node_data['Node']]['alias'] = $node_data['Node'];
          }
        }
        return $results;
    }

    private function getdata($urlappend)
    {
        $url = CONSUL_URL . $urlappend;
        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new ConsulGlobException($errno." ".$errstr);
        }
        return json_decode($response, true);
    }

}

