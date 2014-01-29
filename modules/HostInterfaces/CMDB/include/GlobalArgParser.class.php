<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * GlobalArgParser 
 *      Class for parsing / providing a global argument array of information
 */
class GlobalArgParser {

    private static $m_globalArgs = array();

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
                } else if ((isset($result[$pname])) && (is_array($result[$pname]))) {
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
        self::setGlobalArgs($result);
    }

    /**
     * getGlobalArgs 
     *      return entire globalArgs array contants
     *
     * @static
     * @access public
     * @return void
     */
    public static function getGlobalArgs() {
        return self::$m_globalArgs;
    }

    /**
     * setGlobalArgs 
     *      function that actually sets all of the key => value pairs in the globalArgs array
     *
     * @param array $argArray 
     * @static
     * @access public
     * @return void
     */
    public static function setGlobalArgs(array $argArray) {
        self::$m_globalArgs = array();
        foreach ($argArray as $key => $value) {
            if ((isset(self::$m_globalArgs[$key])) && (!is_array(self::$m_globalArgs[$key]))) {
                $results = array();
                array_push($results, self::$m_globalArgs[$key]);
                array_push($results, $value);
                self::$m_globalArgs[$key] = $results;
            } else if ((isset(self::$m_globalArgs[$key])) && (is_array(self::$m_globalArgs[$key]))) {
                array_push(self::$m_globalArgs[$key], $value);
            } else {
                self::$m_globalArgs[$key] = $value;
            }
        }
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
            if ($key == 'datacenter') return $key;
            else if ($key == 'nodepool') return $key;
            else if ($key == 'deployment') return $key;
            else if ($key == 'glob') return $key;
            else if ($key == 'prefix') return $key;
            else if ($key == 'rsdeployment') return $key;
            else if ($key == 'rsserverarray') return $key;
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
        return 'active';
    }

    /**
     * getUser 
     *      returns the user that will be used to authenticate against the CMDB
     *
     * @static
     * @access public
     * @return void
     */
    public static function getUser() {
        if ((isset(self::$m_globalArgs['user'])) && (!empty(self::$m_globalArgs['user']))) {
            return self::$m_globalArgs['user'];
        }
        $userInfo = posix_getpwuid(posix_getuid());
        return $userInfo['name'];
    }

    /**
     * setUser 
     *      sets the user that will be used to authenticate against the CMDB
    *
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    public static function setUser($value) {
        self::$m_globalArgs['user'] = $value;
    }

    /**
     * setPassword 
     *      sets the password that will be used to authenticate against the CMDB
     *
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    public static function setPassword($value) {
        self::$m_globalArgs['_password'] = $value;
    }

    /**
     * getPassword 
     *      returns the password that will be used to authenticate against the CMDB
     * @static
     * @access public
     * @return void
     */
    public static function getPassword() {
        if ((isset(self::$m_globalArgs['_password'])) && (!empty(self::$m_globalArgs['_password']))) {
            return self::$m_globalArgs['_password'];
        }
        return null;
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
        return 'PDSH';
    }

    /**
     * setSortOrder 
     *      sets the sort order for the return results from the CMDB
     * 
     * @param mixed $value [asc|desc]
     * @static
     * @access public
     * @return void
     */
    public static function setSortOrder($value) {
        self::$m_globalArgs['sortorder'] = $value;
    }

    /**
     * getSortOrder 
     *      returns the sort order for the return results from the CMDB
     *
     * @static
     * @access public
     * @return void
     */
    public static function getSortOrder() {
        if ((isset(self::$m_globalArgs['sortorder'])) && (!empty(self::$m_globalArgs['sortorder']))) {
            if ((self::$m_globalArgs['sortorder'] == 'asc') || (self::$m_globalArgs['sortorder'] == 'desc')) {
                return self::$m_globalArgs['sortorder'];
            }
        } else if ((isset(self::$m_globalArgs['so'])) && (!empty(self::$m_globalArgs['so']))) {
            if ((self::$m_globalArgs['so'] == 'asc') || (self::$m_globalArgs['so'] == 'desc')) {
                return self::$m_globalArgs['so'];
            }
        }
        return 'asc';
    }

    /**
     * setSortField 
     *      sets the sort field that we will use to sort results on from the CMDB
     *
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    public static function setSortField($value) {
        self::$m_globalArgs['sortfield'] = $value;
    }

    /**
     * getSortField 
     *      returns the sort field that we will use to sort results on from the CMDB
     *
     * @static
     * @access public
     * @return void
     */
    public static function getSortField() {
        if ((isset(self::$m_globalArgs['sortfield'])) && (!empty(self::$m_globalArgs['sortfield']))) {
            return self::checkSortField(self::$m_globalArgs['sortfield']);
        } else if ((isset(self::$m_globalArgs['sf'])) && (!empty(self::$m_globalArgs['sf']))) {
            return self::checkSortField(self::$m_globalArgs['sf']);
        }
        return '_id';
    }

    /**
     * checkSortField 
     *      Does some sanity checking on the sort field value specified so the CMDB knows
     *      which field we are wanting to sort on
     *
     * @param mixed $value 
     * @static
     * @access private
     * @return void
     */
    private static function checkSortField($value) {
        if ($value == 'fqdn') return 'config.fqdn';
        else if ($value == 'ip') return 'config.ipaddress';
        else if ($value == 'state') return 'state';
        else return '_id';
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
     * getArgValue 
     *      returns the value of the key specified
     *
     * @param mixed $key 
     * @static
     * @access public
     * @return void
     */
    public static function getArgValue($key) {
        if ((isset(self::$m_globalArgs[$key])) && (!empty(self::$m_globalArgs[$key]))) {
            return self::$m_globalArgs[$key];
        }
        return null;
    }

    /**
     * getInputPassword 
     *      gets a password from the shell
     *      this function only works on *nix systems for password masking
     *
     * @param bool $stars Wether or not to output stars for password
     * @static
     * @access public
     * @return void
     */
    public static function getInputPassword($stars = false) {
        // Get current style
        $oldStyle = shell_exec('stty -g');
        if ($stars === false) {
            shell_exec('stty -echo');
            $password = rtrim(fgets(STDIN), "\n");
        } else {
            shell_exec('stty -icanon -echo min 1 time 0');
            $password = '';
            while (true) {
                $char = fgetc(STDIN);
                if ($char === "\n") {
                    break;
                } else if (ord($char) === 127) {
                    if (strlen($password) > 0) {
                        fwrite(STDERR, "\x08 \x08");
                        $password = substr($password, 0, -1);
                    }
                } else {
                    fwrite(STDERR, "*");
                    $password .= $char;
                }
            }
        }
        // Reset old style
        shell_exec('stty ' . $oldStyle);
        // Return the password
        return $password;
    }

    /**
     * setReturnFields 
     *      Shim for allowing us to set the return fields from the cmdb
     *
     * @param mixed $value 
     * @static
     * @access public
     * @return void
     */
    public static function setReturnFields($value) {
        self::$m_globalArgs['rf'] = $value;
    }

    /**
     * getReturnFields 
     *      returns the fields we are interested in for this cmdb query
     *
     * @static
     * @access public
     * @return void
     */
    public static function getReturnFields() {
        if ((isset(self::$m_globalArgs['rf'])) && (!empty(self::$m_globalArgs['rf']))) {
            return self::$m_globalArgs['rf'];
        } else if ((isset(self::$m_globalArgs['ip'])) && (self::$m_globalArgs['ip'] === true)) {
            return 'config.ipaddress';
        }
        return 'config.fqdn';
    }

/* Closing Brace for Class */
}

