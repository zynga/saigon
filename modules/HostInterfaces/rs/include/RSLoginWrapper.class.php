<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSLoginWrapperException extends Exception {}

class RSLoginWrapper {

    private static $loginObj;
    private static $create_loginObj = true;
    private static $loggedin = false;

    public static function login($rsObj, $force = false) {
        if ((self::$create_loginObj === true) || ($force === true)) {
            self::$loginObj = new RSLoginObj();
            self::$create_loginObj = false;
        }
        if ((self::$loggedin === false) || ($force === true)) {
            $rsObj->login(self::$loginObj);
            self::$loggedin = true;
        }
    }

}

