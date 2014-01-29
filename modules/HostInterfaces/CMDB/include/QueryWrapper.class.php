<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class QueryWrapperException extends Exception {}

/**
 * QueryWrapper 
 *      Creates a QueryObj before passing it off to
 *      a Class for further processing before submitting
 *      the request to the CMDB.
 */
class QueryWrapper {

    const QUERY_URL         = "https://cmdb/servers/_search.js?raw=true&scroll=1m";
    const CLASSNAME_REGEX   = "/CMDB/i";
    
    const MISSING_CLASS     = " class does not exist";
    const CMDB_COLLISION    = "Unable to detect appropriate class, system was fed only Prefix: ";

    /**
     * execute 
     *      Main wrapper command for executing cmdb fetches
     *
     * @param mixed $className 
     * @static
     * @access public
     * @return void
     */
    public static function execute($className) {
        if (!preg_match(self::CLASSNAME_REGEX, $className)) {
            $className = CMDB_LOC_PREFIX.$className;
        }

        if (!class_exists($className)) {
            throw new QueryWrapperException($className.self::MISSING_CLASS);
        } else if ($className == CMDB_LOC_PREFIX) {
            throw new QueryWrapperException(self::CMDB_COLLISION.CMDB_LOC_PREFIX);
        }

        $queryObj = new QueryObj();
        $queryObj->setQueryUrl(self::QUERY_URL);

        new $className($queryObj);
        $cmdb = new CMDB(NULL);
        $cmdb->addObserver($queryObj);
        return $cmdb->fetch($queryObj);
    }
}

