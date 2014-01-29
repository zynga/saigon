<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RightScale implements HostAPI
{

    public function getList()
    {
        return null;
    }

    public function getInput()
    {
        if (strtolower(MODE) == 'secure') {
            return null;
        }
        $results = array();
        $regions = RSArgParser::getRegions();
        foreach ($regions as $region) {
            $dkey = "RS-{$region}-deployment";
            $dval = "{$region} Deployment";
            $sakey = "RS-{$region}-serverarray";
            $saval = "{$region} Server Array";
            $results[$dkey] = $dval;
            $results[$sakey] = $saval;
        }
        return $results;
    }

    public function getSearchResults($input)
    {
        $results = array();
        if (preg_match("/^RS-(\w+)-(\w+)/", $input->location, $matches)) {
            if ($matches[2] == 'deployment') {
                $inputArray = array('exec' => 'rsnagios', 'region' => $matches[1], 'deployment' => $input->srchparam);
            } elseif ($matches[2] == 'serverarray') {
                $inputArray = array('exec' => 'rsnagios', 'region' => $matches[1], 'array' => $input->srchparam);
            } else {
                return $results;
            }
            RSArgParser::setRSArgs($inputArray);
            $output = RSQueryWrapper::execute(RSArgParser::getQueryLocation(), true);
            foreach ($output as $idx => $hostData) {
                if ((empty($hostData)) || (!isset($hostData['host_name'])) || (!isset($hostData['address']))) continue;
                $host = $hostData['host_name'];
                $results[$host] = $hostData;
            }
        }
        return $results;
    }

}

