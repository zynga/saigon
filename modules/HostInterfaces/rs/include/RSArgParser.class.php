<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * RSArgParser 
 *      Class for parsing / providing a global argument array of information
 */
class RSArgParser {

    private static $m_globalArgs = array();
    private static $m_regions = array();

    /**
     * parseParameters 
     *      parse incoming array, used mainly by cli client
     *
     * @param mixed $params 
     * @static
     * @access public
     * @return void
     */
    public static function parseParameters($params) {
        $noopt = array();
        $result = array();
        // could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly (in PHP <5.2.x)
        reset($params);
        while (list($tmp, $p) = each($params)) {
            if ($p{0} == '-') {
                $pname = substr($p, 1);
                $value = true;
                if ($pname{0} == '-') {
                    // long-opt (--<param>)
                    $pname = substr($pname, 1);
                    if (strpos($p, '=') !== false) {
                        // value specified inline (--<param>=<value>)
                        list($pname, $value) = explode('=', substr($p, 2), 2);
                    }
                }
                // check if next parameter is a descriptor or a value
                $nextparm = current($params);
                if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-') list($tmp, $value) = each($params);
                if ((isset($result[$pname])) && (!is_array($result[$pname]))) {
                    $results = array();
                    array_push($results, $result[$pname]);
                    if (preg_match('/,/', $value)) {
                        $valuearray = preg_split('/,/', $value);
                        foreach ($valuearray as $tvalue) {
                            array_push($results, $tvalue);
                        }
                    } else {
                        array_push($results, $value);
                    }
                    $result[$pname] = $results;
                } elseif ((isset($result[$pname])) && (is_array($result[$pname]))) {
                    if (preg_match('/,/', $value)) {
                        $valuearray = preg_split('/,/', $value);
                        foreach ($valuearray as $tvalue) {
                            array_push($result[$pname], $tvalue);
                        }
                    } else {
                        array_push($result[$pname], $value);
                    }
                } else {
                    if (preg_match('/,/', $value)) {
                        $results = array();
                        $matches = preg_split('/,/', $value);
                        foreach ($matches as $match) {
                            array_push($results, $match);
                        }
                        $result[$pname] = $results;
                    } else {
                        $result[$pname] = $value;
                    }
                }
            } else {
                // param doesn't belong to any option
                // $result[] = $p;
            }
        }
        self::setRSArgs($result);
    }

    /**
     * getRSArgs 
     *      return entire globalArgs array contants
     *
     * @static
     * @access public
     * @return void
     */
    public static function getRSArgs() {
        return self::$m_globalArgs;
    }

    /**
     * setRSArgs 
     *      function that actually sets all of the key => value pairs in the globalArgs array
     *
     * @param array $argArray 
     * @static
     * @access public
     * @return void
     */
    public static function setRSArgs(array $argArray) {
        self::$m_globalArgs = array(); /* Nullify incase of existance and processing a new request */
        foreach ($argArray as $key => $value) {
            if ((isset(self::$m_globalArgs[$key])) && (!is_array(self::$m_globalArgs[$key]))) {
                $results = array();
                array_push($results, self::$m_globalArgs[$key]);
                array_push($results, $value);
                self::$m_globalArgs[$key] = $results;
            } elseif ((isset(self::$m_globalArgs[$key])) && (is_array(self::$m_globalArgs[$key]))) {
                array_push(self::$m_globalArgs[$key], $value);
            } else {
                self::$m_globalArgs[$key] = $value;
            }
        }
        self::buildRegions();
    }

    /**
     * getQueryLocation 
     *      function for returning the query location / search parameter
     *
     * @static
     * @access public
     * @return void
     */
    public static function getQueryLocation() {
        foreach (self::$m_globalArgs as $key => $value) {
            if ($key == 'deployment') return RS_LOC_PREFIX . 'Deployment';
            elseif ($key == 'deploymentlist') return RS_LOC_PREFIX . 'DeploymentList';
            elseif ($key == 'deploymentmulti') return RS_LOC_PREFIX . 'DeploymentMulti';
            elseif ($key == 'array') return RS_LOC_PREFIX . 'Array';
            elseif ($key == 'arraylist') return RS_LOC_PREFIX . 'ArrayList';
            elseif ($key == 'arraymulti') return RS_LOC_PREFIX . 'ArrayMulti';
            elseif ($key == 'multiad') return RS_LOC_PREFIX . 'MultiAD';
            else continue;
        }
        return false;
    }

    /**
     * getState 
     *      returns the operational state of the machines we are searching for
     *
     * @static
     * @access public
     * @return void
     */
    public static function getState() {
        if ((isset(self::$m_globalArgs['state'])) && (!empty(self::$m_globalArgs['state']))) {
            return self::$m_globalArgs['state'];
        }
        return 'operational';
    }

    /**
     * setExecutor 
     *      sets the executor that will be used to process the job results
     *
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    public static function setExecutor($value) {
        self::$m_globalArgs['exec'] = $value;
    }

    /**
     * getExecutor 
     *      returns the executor that will be used to process the job results
     *
     * @static
     * @access public
     * @return void
     */
    public static function getExecutor() {
        if ((isset(self::$m_globalArgs['exec'])) && (!empty(self::$m_globalArgs['exec']))) {
            return self::$m_globalArgs['exec'];
        }
        return 'RSPDSH';
    }

    /**
     * setKeyValue 
     *      function for setting some unspecified key to a value
     *
     * @param mixed $key 
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    public static function setKeyValue($key, $value) {
        self::$m_globalArgs[$key] = $value;
    }
        
    /**
     * getKeyValue 
     *      returns the value of the key specified
     *
     * @param mixed $key 
     * @static
     * @access public
     * @return void
     */
    public static function getKeyValue($key) {
        if ((isset(self::$m_globalArgs[$key])) && (!empty(self::$m_globalArgs[$key]))) {
            return self::$m_globalArgs[$key];
        }
        return null;
    }

    /**
     * getRegion 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getRegion() {
        if ((isset(self::$m_globalArgs['region'])) && (!empty(self::$m_globalArgs['region']))) {
            return strtolower(self::$m_globalArgs['region']);
        }
        throw new Exception("Unable to determine region to address");
    }

    /**
     * getRegions 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getRegions() {
        $results = array();
        $regionMetas = preg_split('/,/', RS_REGION_MAP);
        foreach ($regionMetas as $regionMeta) {
            list($region, $accnt, $token) = preg_split('/:/', $regionMeta);
            array_push($results, $region);
        }
        return $results;
    }

    /**
     * getRegionAccount 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getRegionAccount() {
        $region = self::getRegion();
        if ((isset(self::$m_regions[$region]['account'])) && (!empty(self::$m_regions[$region]['account']))) {
            return self::$m_regions[$region]['account'];
        }
        throw new Exception("Unable to detect $region account id");
    }

    /**
     * getRegionToken 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getRegionToken() {
        $region = self::getRegion();
        if ((isset(self::$m_regions[$region]['token'])) && (!empty(self::$m_regions[$region]['token']))) {
            return self::$m_regions[$region]['token'];
        }
        throw new Exception("Unable to detect $region authorization token");
    }

    /**
     * buildRegions 
     * 
     * @static
     * @access private
     * @return void
     */
    private static function buildRegions() {
        $regionMetas = preg_split('/,/', RS_REGION_MAP);
        foreach ($regionMetas as $regionMeta) {
            list($region, $accnt, $token) = preg_split('/:/', $regionMeta);
            self::$m_regions[$region]['account'] = $accnt;
            self::$m_regions[$region]['token'] = $token;
        }
    }

/* Closing Brace for Class */
}

