<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CDC_DS {

    protected static $init = false;

    public static function init() {
        if (self::$init === false) {
            NagRedis::init();
            self::$init = true;
        }
        return;
    }

    public static function getRouterInfo($zone) {
        if (self::$init === false) self::init();
        return NagRedis::get(md5('cdcroutervms:'.$zone));
    }

    public static function writeRouterInfo($zone, $zoneinfo) {
        if (self::$init === false) self::init();
        NagRedis::set(md5('cdcroutervms:'.$zone), $zoneinfo);
    }

    public static function isRouterZone($zone) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('cdcrouterzones'), $zone);
    }

    public static function getRouterZones() {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('cdcrouterzones'));
    }

    public static function writeRouterZones(array $zones) {
        if (self::$init === false) self::init();
        NagRedis::del(md5('cdcrouterzones'));
        NagRedis::sAdd(md5('cdcrouterzones'), $zones);
    }

}

