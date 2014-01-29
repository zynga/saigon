<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NoAuth implements Auth
{

    public static function getUser()
    {
        return '-';
    }

    public function getTitle()
    {
        return 'Authorization Disabled:';
    }

    public function checkAuth($deployment)
    {
        return true;
    }

}

