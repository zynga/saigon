<?php
//
// Copyright (c) 2015, Pinterest 
// https://github.com/mhwest13/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

// This class is here for the saigon-data-migrator script
// it provides answers to lower level code when writing to
// the log file.


// Do not enable this as your Auth Module, use NoAuth instead

class ConsumerAuth implements Auth
{

    public static function getUser()
    {
        return 'consumer';
    }

    public function getTitle()
    {
        return '';
    }

    public function checkAuthByDeployment($deployment)
    {
        return true;
    }

    public function checkAuthByGroup($authgroup)
    {
        return true;
    }

}

