<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBNagios extends ZSBObserver {

    public function execute($response) {
        $jsonObj = json_decode($response);
        $results = array();
        if (empty($jsonObj)) return $results;
        foreach ($jsonObj as $idx => $hostObj) {
            if ((empty($hostObj)) || (!isset($hostObj->vname)) || (!isset($hostObj->vip)) ||
                (!isset($hostObj->cloud))
            ) {
                continue;
            }
            $host = ZSBHelperFuncs::buildCloudHostname($hostObj->vname, $hostObj->cloud);
            $results[$host]['host_name'] = $host;
            $results[$host]['address'] = $hostObj->vip;
        }
        return $results;
    }

}
