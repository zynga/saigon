<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBHelperFuncs
{
    private static $urlModifyRegex = array(
        '/\/api\/servers/' => 'https://my.rightscale.com/servers',
        '/\/api\/clouds/' => 'https://my.rightscale.com/clouds',
    );

    public static function buildCloudHostUrl($link)
    {
        $href = preg_replace(array_keys(self::$urlModifyRegex), array_values(self::$urlModifyRegex), $link);
        return $href;
    }

}

