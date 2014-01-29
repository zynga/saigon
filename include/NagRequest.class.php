<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagRequest
{

    /**
     * fetchDeploymentData - fetch nagios configuration info for deployment specified 
     *
     * @param mixed $deployment deployment we are attempting to fetch
     *
     * @static
     * @access public
     * @return void
     */
    public static function fetchDeploymentData($deployment, $subdeployment = false)
    {
        $url = SAIGONAPI_URL . '/getNagiosCfg/' . $deployment;
        if (($subdeployment !== false) && (!empty($subdeployment))) {
            $url .= '/' . $subdeployment;
        }
        return self::_fetchData($url);
    }

    /**
     * fetchRouterVMZone - fetch routervm zone info for zone / deployment specified
     * 
     * @param mixed $zone zone we are attempting to pull back data for
     *
     * @static
     * @access public
     * @return void
     */
    public static function fetchRouterVMZone($zone)
    {
        $url = SAIGONAPI_URL . '/getRouterVM/' . $zone;
        return self::_fetchData($url);
    }

    /**
     * _fetchData - core fetch data function for NagRequest class
     * 
     * @param mixed $url url we are wanting to fetch and return
     *
     * @static
     * @access private
     * @return void
     */
    private static function _fetchData($url)
    {
        $interface = NagMisc::getInterface();
        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Cache-Control: no-cache"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Saigon Nagios Data Fetcher/'.VERSION."/$interface");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        /* Response or No Response ? */
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
            curl_close($ch);
        } else {
            curl_close($ch);
        }
        return $result;
    }

}

