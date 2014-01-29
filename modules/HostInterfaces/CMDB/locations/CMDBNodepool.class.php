<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBNodepool extends CMDBLocation {

    const QUERY_SIZE = 1000;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setSearchParam(GlobalArgParser::getArgValue('nodepool'));
        $queryObj->setExecutorName('DataGrouper');
        $returnField = $queryObj->getReturnField();
        if (is_array($returnField)) {
            $returnField = implode('","', $returnField);
        }
        $queryObj->setQueryString('{"from":0,"size":'.self::QUERY_SIZE.',"fields":["'.$returnField.'"],"query":{"query_string":{"query":"nodepool:'.strtolower($queryObj->getSearchParam()).' AND state:'.strtolower($queryObj->getState()).'"}},"sort":[{"'.GlobalArgParser::getSortField().'":{"order":"'.GlobalArgParser::getSortOrder().'"}}]}');
    }

}

class CMDBNodepoolCount extends CMDBLocation {

    const QUERY_SIZE = 0;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $queryObj->setSearchParam(GlobalArgParser::getArgValue('nodepool'));
        $queryObj->setExecutorName('CMDBTotalReturn');
        $returnField = $queryObj->getReturnField();
        if (is_array($returnField)) {
            $returnField = implode('","', $returnField);
        }
        $queryObj->setQueryString('{"from":0,"size":'.self::QUERY_SIZE.',"fields":["'.$returnField.'"],"query":{"query_string":{"query":"nodepool:'.strtolower($queryObj->getSearchParam()).' AND state:'.strtolower($queryObj->getState()).'"}},"sort":[{"'.GlobalArgParser::getSortField().'":{"order":"'.GlobalArgParser::getSortOrder().'"}}]}');
    }
}
