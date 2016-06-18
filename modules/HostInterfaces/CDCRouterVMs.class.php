<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CDCRouterVMs implements HostAPI
{

    public function getList()
    {
        if (strtolower(MODE) == 'secure') {
            return null;
        }
        $output = RevDeploy::getCDCRouterZones();
        $results = array_flip($output);
        return $results;
    }

    public function getInput()
    {
        return null;
    }

    public function getSearchResults($input)
    {
        $results = array();
        $routerInfo = NagRequest::fetchRouterVMZone($input->srchparam);
        $routerDec = json_decode($routerInfo);
        foreach ($routerDec as $host => $hostparams) {
            $results[$host]['host_name'] = $host;
            $results[$host]['alias'] = $host;
            $results[$host]['address'] = $hostparams->ipaddress;
        }
        return $results;
    }

}

