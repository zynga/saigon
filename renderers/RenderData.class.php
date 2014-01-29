<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RenderData
{

    public $ip;
    public $deployment;
    public $user;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment deployment we are making the change too
     *
     * @access public
     * @return void
     */
    public function __construct($deployment)
    {
        $amodule = AUTH_MODULE;
        $this->user = $amodule::getUser();
        $this->ip = NagMisc::getIP();
        $this->deployment = $deployment;
    }

}

