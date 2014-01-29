<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSNagios extends RSObserver {

    public function execute($response) {
        $jsonObj = json_decode($response);
        $results = array();
        $datacenter = RSArgParser::getRegion();
        foreach ($jsonObj as $tmpObj) {
            if ($tmpObj->state != RSArgParser::getState()) continue;
            if (isset($tmpObj->current_instance)) {
                /* Deployment Lookup */
                $tmpArray = array();
                $tmpArray['host_name'] = RSHelperFuncs::buildCloudHostname($tmpObj->name, $datacenter);
                if (count($tmpObj->current_instance->public_ip_addresses) > 0) {
                    $tmpArray['address'] = $tmpObj->current_instance->public_ip_addresses[0];
                } else {
                    $tmpArray['address'] = $tmpObj->current_instance->private_ip_addresses[0];
                }
                $tmpArray['action_url'] = RSHelperFuncs::buildCloudHostUrl($tmpObj->links);
            } else {
                /* Server Array Lookup */
                $tmpArray['host_name'] = RSHelperFuncs::buildCloudHostname($tmpObj->name, $datacenter);
                if (count($tmpObj->public_ip_addresses) > 0) {
                    $tmpArray['address'] = $tmpObj->public_ip_addresses[0];
                } else {
                    $tmpArray['address'] = $tmpObj->private_ip_addresses[0];
                }
                $tmpArray['action_url'] = RSHelperFuncs::buildCloudHostUrl($tmpObj->links);
            }
            $results[] = $tmpArray;
        }
        return $results;
    }

}
