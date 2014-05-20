<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * Saigon HostInputs Class
 */

class HostInputs
{

    /**
     * _fetchLocations - fetch predetermined location information 
     * 
     * @access public
     * @static
     * @return void
     */
    public static function fetchLocations()
    {
        $locations = array();
        if (!defined('DEPLOYMENT_MODULES')) return $locations;
        $modules = explode(',', DEPLOYMENT_MODULES);
        if (empty($modules[0])) return $locations;
        foreach ($modules as $module) {
            $tmpObj = new $module;
            $return = $tmpObj->getList();
            if ($return == null) continue;
            if (preg_match("/^AWSEC2/", $module)) {
                foreach ($return as $key => $value) {
                    $locations[$key] = array_keys($value);
                }
            }
            else {
                $locations[$module] = array_keys($return);
            }
        }
        return $locations;
    }

    /**
     * _fetchInputs - fetch deployment glob / param input locations 
     * 
     * @access public
     * @static
     * @return void
     */
    public static function fetchInputs()
    {
        $inputs = array();
        if (!defined('INPUT_MODULES')) return $inputs;
        $modules = explode(',', INPUT_MODULES);
        if (empty($modules[0])) return $inputs;
        foreach ($modules as $module) {
            $tmpObj = new $module;
            $return = $tmpObj->getInput();
            if ($return == null) continue;
            if (!is_array($return)) {
                $inputs[$module] = $return;
            } else {
                foreach ($return as $key => $value) {
                    if ((!isset($inputs[$key])) || (empty($inputs[$key]))) {
                        $inputs[$key] = $value;
                    }
                }
            }
        }
        return $inputs;
    }

}

