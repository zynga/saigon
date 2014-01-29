<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSMultiQueryWrapperException extends Exception {}

class RSMultiQueryWrapper {

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
        $result = null;
        $queryObj = new RSQueryObj();
        $rootClass = new $className($queryObj);
        if ($className == 'RSMultiAD') {
            $rs = new RS(NULL);
            RSDataGrouperCache::init();
            foreach ($queryObj->getArrayLoop() as $loop) {
                $rootClass->buildQueryUrl('array', $queryObj, $loop);
                $rs->addObserver($queryObj);
                RSLoginWrapper::login($rs);
                $rs->fetch($queryObj);
            }
            foreach ($queryObj->getDeploymentLoop() as $loop) {
                $rootClass->buildQueryUrl('deployment', $queryObj, $loop);
                $rs->addObserver($queryObj);
                RSLoginWrapper::login($rs);
                $rs->fetch($queryObj);
            }
            $cache = RSDataGrouperCache::getCache(true);
            if (!!$executor = RSArgParser::getExecutor()) {
                $executorObj = new $executor();
                $result = $executorObj->execute($cache);
            }
        } else {
            $rs = new RS(NULL);
            RSDataGrouperCache::init();
            foreach ($queryObj->getLoop() as $loop) {
                $rootClass->buildQueryUrl($queryObj, $loop);
                $rs->addObserver($queryObj);
                RSLoginWrapper::login($rs, $forceRelogin);
                $rs->fetch($queryObj);
            }
            $cache = RSDataGrouperCache::getCache(true);
            if (!!$executor = RSArgParser::getExecutor()) {
                $executorObj = new $executor();
                $result = $executorObj->execute($cache);
            }
        }
        return $result;
    }
}

