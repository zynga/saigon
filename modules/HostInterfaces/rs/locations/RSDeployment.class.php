<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSDeployment extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $deployment = RSArgParser::getKeyValue('deployment');
        $queryObj->setExecutorName(RSArgParser::getexecutor());
        $queryUrl = RS_BASE_APIURL . "/deployments/{$deployment}/servers";
        $queryUrl .= "?view=instance_detail";
        $queryObj->setQueryUrl($queryUrl);
    }

}

class RSDeploymentList extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setExecutorName(RSArgParser::getexecutor());
        $queryUrl = RS_BASE_APIURL . "/deployments";
        $queryObj->setQueryUrl($queryUrl);
    }

}

class RSDeploymentMulti extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setLoop(RSArgParser::getKeyValue('deploymentmulti'));
        $queryObj->setExecutorName('DataGrouper');
    }

    public function buildQueryUrl($queryObj, $deployment) {
        $queryUrl = RS_BASE_APIURL . "/deployments/{$deployment}/servers";
        $queryUrl .= "?view=instance_detail";
        $queryObj->setQueryUrl($queryUrl);
    }

}

