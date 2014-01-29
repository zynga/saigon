<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * ZSBArgParser 
 *      Class for parsing / providing a global argument array of information
 */
class ZSBArgParser {

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
        self::setZSBArgs($result);
    }

    /**
     * getZSBArgs 
     *      return entire globalArgs array contants
     *
     * @static
     * @access public
     * @return void
     */
    public static function getZSBArgs() {
        return self::$m_globalArgs;
    }

    /**
     * setZSBArgs 
     *      function that actually sets all of the key => value pairs in the globalArgs array
     *
     * @param array $argArray 
     * @static
     * @access public
     * @return void
     */
    public static function setZSBArgs(array $argArray) {
        self::$m_globalArgs = array(); /* Nullify incase of existance and processing a new request */
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
            if ($key == 'deployment') return ucfirst($key);
            else if ($key == 'deploymentlist') return 'DeploymentList';
            else if ($key == 'glob') return ucfirst($key);
            else if ($key == 'prefix') return ucfirst($key);
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
     *      returns the user that will be used to authenticate against the ZSB
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
     *      sets the user that will be used to authenticate against the ZSB
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
     *      sets the password that will be used to authenticate against the ZSB
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
     *      returns the password that will be used to authenticate against the ZSB
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
        return 'ZSBPDSH';
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

/* Closing Brace for Class */
}

