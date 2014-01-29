<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBDatacenter extends CMDBLocation {

    const QUERY_SIZE = 1000;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setSearchParam(GlobalArgParser::getArgValue('datacenter'));
        $queryObj->setExecutorName('DataGrouper');
        $returnField = $queryObj->getReturnField();
        if (is_array($returnField)) {
            $returnField = implode('","', $returnField);
        }
        $queryObj->setQueryString('{"from":0,"size":'.self::QUERY_SIZE.',"fields":["'.$returnField.'"],"filter":{"and":[{"term":{"state":"'.strtolower($queryObj->getState()).'"}},{"term":{"location":"'.strtoupper($queryObj->getSearchParam()).'"}}]},"sort":[{"'.GlobalArgParser::getSortField().'":{"order":"'.GlobalArgParser::getSortOrder().'"}}]}');
    }
}

class CMDBDatacenterCount extends CMDBLocation {

    const QUERY_SIZE = 0;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setSearchParam(GlobalArgParser::getArgValue('datacentercount'));
        $queryObj->setExecutorName('CMDBTotalReturn');
        $returnField = $queryObj->getReturnField();
        if (is_array($returnField)) {
            $returnField = implode('","', $returnField);
        }
        $queryObj->setQueryString('{"from":0,"size":'.self::QUERY_SIZE.',"fields":["'.$returnField.'"],"filter":{"and":[{"term":{"state":"'.strtolower($queryObj->getState()).'"}},{"term":{"location":"'.strtoupper($queryObj->getSearchParam()).'"}}]},"sort":[{"'.GlobalArgParser::getSortField().'":{"order":"'.GlobalArgParser::getSortOrder().'"}}]}');
    }
}

class CMDBDatacenterList extends CMDBLocation {

    const QUERY_SIZE = 0;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setExecutorName(GlobalArgParser::getExecutor());
        $queryObj->setQueryString('{"from":0,"size":0,"fields":["_id","state","config.fqdn"],"query":{"query_string":{"query":"state:active"}},"facets":{"facet":{"terms":{"size":100,"field":"domain"}}}}');
    }
}
