<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * NagMisc 
 *
 *      Just a class of misc functions used in the code, but don't belong to any
 *      other class really.
 */

class NagMisc {

    /**
     * getIP 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getIP() {
        if (!isset($_SERVER) || !is_array($_SERVER))
            $ip = '127.0.0.1';
        elseif (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) //to check ip is passed from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (array_key_exists('REMOTE_ADDR', $_SERVER))
            $ip = $_SERVER['REMOTE_ADDR'];
        elseif (CONSUMER === true)
            $ip = '127.0.0.1';
        return $ip;
    }

    /**
     * encodeIP 
     *      - used for consistent sharding buildouts
     * 
     * @param mixed $ip 
     * @static
     * @access public
     * @return void
     */
    public static function encodeIP($ip) {
        if (!preg_match('/\d+.\d+.\d+.\d+/', $ip)) {
            return null;
        }
        $iparr = explode('.', $ip);
        $iphex = sprintf("%02x%02x%02x%02x", $iparr[0], $iparr[1], $iparr[2], $iparr[3]);
        return $iphex;
    }

    /**
     * recursive_object_to_array 
     * 
     * @param mixed $obj 
     * @static
     * @access public
     * @return void
     */
    public static function recursive_object_to_array($obj) {
        $arr = array();
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? self::recursive_object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }

    /**
     * getInterface 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getInterface() {
        $command = '/sbin/ifconfig eth0';
        exec($command, $output, $exitcode);
        foreach ($output as $line) {
            if (preg_match("/inet addr:((\d+)\.(\d+)\.(\d+)\.(\d+))/", $line, $matches)) {
                return $matches[1];
            }
        }
        return "0.0.0.0";
    }

}

