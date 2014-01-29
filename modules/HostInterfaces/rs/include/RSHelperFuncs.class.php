<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSHelperFuncs {

    private static $urlModifyRegex = array(
        '/\/api\/servers/' => 'https://my.rightscale.com/servers',
        '/\/api\/clouds/' => 'https://my.rightscale.com/clouds',
    );

    public static function buildCloudHostname($inputHost, $inputZone, $appendDomain = true) {
        /* Check for \s# and replace it with a - for fqdn generation */
        if (preg_match('/\s\#/', $inputHost)) {
            $hostname = preg_replace('/\s\#/', '-', $inputHost);
        } else {
            $hostname = $inputHost;
        }
        $hostname = trim($hostname);
        if ($appendDomain === true) {
            /* Determine Cloud this host lives in */
            $hostname .= ".{$inputZone}.zynga.com";
        }
        return $hostname;
    }

    public static function buildCloudHostUrl($links) {
        $href = null;
        foreach ($links as $link) {
            if ($link->rel == 'self') {
                $href = $link->href;
                break;
            }
        }
        $href = preg_replace(array_keys(self::$urlModifyRegex), array_values(self::$urlModifyRegex), $href);
        return $href;
    }

}

