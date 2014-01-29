<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBDatacenterListReturn extends Observer {

    public function execute($response) {
        $results = array();
        $jsonObj = json_decode($response);
        $searchResults = $jsonObj->facets->facet->terms;
        foreach ($searchResults as $idx => $idxObj) {
            if (($idxObj->term == 'zynga.com') || ($idxObj->term == 'com')) continue;
            if (!preg_match('/\.zynga\.com/', $idxObj->term)) continue;
            $dc = preg_replace('/\.zynga\.com/', '', $idxObj->term);
            $results[$dc]['domain'] = $idxObj->term;
            $results[$dc]['count'] = $idxObj->count;
        }
        return $results;
    }

}

