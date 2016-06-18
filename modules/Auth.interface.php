<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

interface Auth
{
    // Return false if function is not used in your class 
    public function checkAuthByDeployment($deployment);
    public function checkAuthByGroup($authgroup);
    public function getTitle();
    public static function getUser();
}

