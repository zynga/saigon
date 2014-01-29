<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSArray extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $array = RSArgParser::getKeyValue('array');
        $queryObj->setExecutorName(RSArgParser::getexecutor());
        $queryUrl = RS_BASE_APIURL . "/server_arrays/{$array}/current_instances";
        $queryObj->setQueryUrl($queryUrl);
    }

}

class RSArrayList extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $fid = RSArgParser::getKeyValue('filter');
        $queryObj->setExecutorName(RSArgParser::getexecutor());
        $queryUrl = RS_BASE_APIURL . "/server_arrays";
        if ($fid != null) $queryUrl .= "?filter[]=deployment_href==/api/deployments/{$fid}";
        $queryObj->setQueryUrl($queryUrl);
    }

}

class RSArrayMulti extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setLoop(RSArgParser::getKeyValue('arraymulti'));
        $queryObj->setExecutorName('DataGrouper');
    }

    public function buildQueryUrl($queryObj, $array) {
        $region = RSArgParser::getRegionAccount();
        $queryUrl = RS_BASE_APIURL . "/clouds/{$region}/instances";
        $queryUrl .= "?filter[]=parent_href==/api/server_arrays/{$array}";
        $queryObj->setQueryUrl($queryUrl);
    }

}

