<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSMultiAD extends RSLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setArrayLoop(RSArgParser::getKeyValue('arrays'));
        $queryObj->setDeploymentLoop(RSArgParser::getKeyValue('deployments'));
        $queryObj->setExecutorName('DataGrouper');
    }

    public function buildQueryUrl($mode, $queryObj, $data) {
        if ($mode == 'array') {
            $region = RSArgParser::getRegionAccount();
            $queryUrl = RS_BASE_APIURL . "/clouds/{$region}/instances";
            $queryUrl .= "?filter[]=parent_href==/api/server_arrays/{$data}";
            $queryObj->setQueryUrl($queryUrl);
        } elseif ($mode == 'deployment') {
            $queryUrl = RS_BASE_APIURL . "/deployments/{$data}/servers";
            $queryUrl .= "?view=instance_detail";
            $queryObj->setQueryUrl($queryUrl);
        }
    }

}

