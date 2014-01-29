<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBGlob extends CMDBLocation {

    const QUERY_SIZE = 1000;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        if (!preg_match("/\*$/", GlobalArgParser::getArgValue('glob'))) {
            $queryObj->setSearchParam(GlobalArgParser::getArgValue('glob').'*');
        } else {
            $queryObj->setSearchParam(GlobalArgParser::getArgValue('glob'));
        }
        $queryObj->setExecutorName('DataGrouper');
        $returnField = $queryObj->getReturnField();
        if (is_array($returnField)) {
            $returnField = implode('","', $returnField);
        }
        $queryObj->setQueryString('{"from":0,"size":'.self::QUERY_SIZE.',"fields":["'.$returnField.'"],"query":{"query_string":{"query":"config.fqdn:'.strtolower($queryObj->getSearchParam()).' AND state:'.strtolower($queryObj->getState()).'"}},"sort":[{"'.GlobalArgParser::getSortField().'":{"order":"'.GlobalArgParser::getSortOrder().'"}}]}');
    }

}

class CMDBGlobCount extends CMDBLocation {

    const QUERY_SIZE = 0;

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        if (!preg_match("/\*$/", GlobalArgParser::getArgValue('glob'))) {
            $queryObj->setSearchParam(GlobalArgParser::getArgValue('glob').'*');
        } else {
            $queryObj->setSearchParam(GlobalArgParser::getArgValue('glob'));
        }
        $queryObj->setExecutorName('CMDBTotalReturn');
        $returnField = $queryObj->getReturnField();
        if (is_array($returnField)) {
            $returnField = implode('","', $returnField);
        }
        $queryObj->setQueryString('{"from":0,"size":'.self::QUERY_SIZE.',"fields":["'.$returnField.'"],"query":{"query_string":{"query":"config.fqdn:'.strtolower($queryObj->getSearchParam()).' AND state:'.strtolower($queryObj->getState()).'"}},"sort":[{"'.GlobalArgParser::getSortField().'":{"order":"'.GlobalArgParser::getSortOrder().'"}}]}');
    }
}
