<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSQueryWrapperException extends Exception {}

/**
 * RSQueryWrapper 
 *      Creates a RSQueryObj before passing it off to
 *      a Class for further processing before submitting
 *      the request to the RS.
 */
class RSQueryWrapper {

    const CLASSNAME_REGEX   = "/RS/i";
    
    const MISSING_CLASS     = " class does not exist";
    const COLLISION    = "Unable to detect appropriate class, system was fed only Prefix: ";

    /**
     * execute 
     *      Main wrapper command for executing rightscale fetches
     *
     * @param mixed $className 
     * @static
     * @access public
     * @return void
     */
    public static function execute($className, $forceRelogin = false) {
        if (!preg_match(self::CLASSNAME_REGEX, $className)) {
            $className = RS_LOC_PREFIX.$className;
        }

        if (!class_exists($className)) {
            throw new RSQueryWrapperException($className.self::MISSING_CLASS);
        } else if ($className == RS_LOC_PREFIX) {
            throw new RSQueryWrapperException(self::COLLISION.RS_LOC_PREFIX);
        }

        $queryObj = new RSQueryObj();
        new $className($queryObj);

        $rs = new RS(NULL);
        $rs->addObserver($queryObj);
        RSLoginWrapper::login($rs, $forceRelogin);
        return $rs->fetch($queryObj);
    }
}

