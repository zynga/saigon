<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBDeploymentListReturn extends Observer {

    public function execute($response) {
        $results = array();
        $jsonObj = json_decode($response);
        $searchResults = $jsonObj->facets->facet->terms;
        foreach ($searchResults as $idx => $idxObj) {
            $results[$idxObj->term]['deployment'] = $idxObj->term;
            $results[$idxObj->term]['count'] = $idxObj->count;
        }
        return $results;
    }

}

