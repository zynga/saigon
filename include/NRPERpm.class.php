<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPERpm
{

    /**
     * createSpec 
     * 
     * @param mixed $deployment deployment we are creating a spec file for
     *
     * @static
     * @access public
     * @return void
     */
    public static function createSpec($deployment)
    {
        require_once(BASE_PATH.'/conf/nrperpm.inc.php');
        $filecontents = SPECFILE;
        $filecontents = preg_replace("/\<deploymentname\>/", $deployment, $filecontents);
        $filecontents = preg_replace("/\<dateholder\>/", date("D M d Y"), $filecontents);
        return base64_encode($filecontents);
    }

}
