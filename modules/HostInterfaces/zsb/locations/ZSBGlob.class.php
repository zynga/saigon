<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBGlob extends ZSBLocation {

    public function __construct($queryObj) {
        parent::__construct($queryObj);
        $glob = ZSBArgParser::getKeyValue('glob');
        if (!preg_match('/\*$/', $glob)) $glob .= '*';
        $queryObj->setExecutorName(ZSBArgParser::getexecutor());
        $queryUrl = 'https://somehost/Cloud/Search';
        $queryUrl .= '?vname='.$glob;
        $queryObj->setQueryUrl($queryUrl);
    }
}
