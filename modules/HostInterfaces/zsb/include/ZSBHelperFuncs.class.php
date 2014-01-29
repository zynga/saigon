<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBHelperFuncs {

    public static function buildCloudHostname($inputHost, $inputCloud) {
        /* Check for \s# and replace it with a - for fqdn generation */
        if (preg_match('/\s\#/', $inputHost)) {
            $hostname = preg_replace('/\s\#/', '-', $inputHost);
        } else {
            $hostname = $inputHost;
        }
        if (preg_match('/^zcloud-.+-(.*)$/', $inputCloud, $matches)) {
            $hostname .= '.' . $matches[1] . '.zynga.com';
        }
        return $hostname;
    }

}

