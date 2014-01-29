<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBQueryWrapperException extends Exception {}

/**
 * ZSBQueryWrapper 
 *      Creates a ZSBQueryObj before passing it off to
 *      a Class for further processing before submitting
 *      the request to the ZSB.
 */
class ZSBQueryWrapper {

    const CLASSNAME_REGEX   = "/ZSB/i";
    
    const MISSING_CLASS     = " class does not exist";
    const ZSB_COLLISION    = "Unable to detect appropriate class, system was fed only Prefix: ";

    /**
     * execute 
     *      Main wrapper command for executing zsb fetches
     *
     * @param mixed $className 
     * @static
     * @access public
     * @return void
     */
    public static function execute($className) {
        if (!preg_match(self::CLASSNAME_REGEX, $className)) {
            $className = ZSB_LOC_PREFIX.$className;
        }

        if (!class_exists($className)) {
            throw new ZSBQueryWrapperException($className.self::MISSING_CLASS);
        } else if ($className == ZSB_LOC_PREFIX) {
            throw new ZSBQueryWrapperException(self::ZSB_COLLISION.ZSB_LOC_PREFIX);
        }

        $queryObj = new ZSBQueryObj();

        new $className($queryObj);
        $zsb = new ZSB(NULL);
        $zsb->addObserver($queryObj);
        return $zsb->fetch($queryObj);
    }
}

