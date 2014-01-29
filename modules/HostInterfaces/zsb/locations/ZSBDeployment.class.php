<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBDeployment extends ZSBLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $deployment = ZSBArgParser::getKeyValue('deployment');
        $queryObj->setExecutorName(ZSBArgParser::getexecutor());
        $queryUrl = 'https://somehost/Cloud/Search';
        $queryUrl .= '?deploy='.$deployment;
        $queryObj->setQueryUrl($queryUrl);
    }
}

class ZSBDeploymentList extends ZSBLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setExecutorName(ZSBArgParser::getexecutor());
        $queryUrl = 'https://somehost/Cloud/Meta?type=deploy';
        $queryObj->setQueryUrl($queryUrl);
    }
}

