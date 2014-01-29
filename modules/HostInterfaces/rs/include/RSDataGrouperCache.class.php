<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSDataGrouperCache {

    private static $m_init = false;
    private static $m_cache = array();

    public static function init($reset = false) {
        if (self::$m_init === false) {
            self::$m_init = true;
        } elseif ($reset === true) {
            self::$m_cache = array();
        }
    }

    public static function getCache($jsonEnc = false) {
        if (self::$m_init === false) self::init();
        if ($jsonEnc === true) {
            return json_encode(self::$m_cache);
        }
        return self::$m_cache;
    }

    public static function addItem($item) {
        if (self::$m_init === false) self::init();
        self::$m_cache[] = $item;
    }

}

