<?php

class EC2ArgParser
{

    private static $m_globalArgs = array();

    /**
     * getGlobalArgs 
     *      return entire globalArgs array contants
     *
     * @static
     * @access public
     * @return void
     */
    public static function getGlobalArgs()
    {
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
    public static function setGlobalArgs(array $argArray)
    {
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
     * resetGlobalArgs
     *      function to reset global args back to empty array
     *
     * @static
     * @access public
     * @return void
     */
    public static function resetGlobalArgs()
    {
        self::$m_globalArgs = array();
    }

    /**
     * getQueryLocation 
     *      function for returning the query location / search parameter
     *
     * @static
     * @access public
     * @return void
     */
    public static function getQueryLocation()
    {
        foreach (self::$m_globalArgs as $key => $value) {
            $key = strtolower($key);
            if ($key == 'allinstances') return 'AllInstances';
            elseif ($key == 'filtertagkeyvalue') return 'FilterTagKeyValue';
            elseif ($key == 'filtergroupname') return 'FilterGroupName';
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
    public static function getState()
    {
        if ((isset(self::$m_globalArgs['state'])) && (!empty(self::$m_globalArgs['state']))) {
            return self::$m_globalArgs['state'];
        }
        return 'running';
    }

    /**
     * setState
     *      sets the operational state of the machines we are seraching for
     *
     * @param mixed $value
     * @static
     * @access public
     * @return void
     */
    public static function setState($value)
    {
        self::$m_globalArgs['state'] = $value;
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
    public static function setExecutor($value)
    {
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
    public static function getExecutor()
    {
        if ((isset(self::$m_globalArgs['exec'])) && (!empty(self::$m_globalArgs['exec']))) {
            return self::$m_globalArgs['exec'];
        }
        return false;
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
    public static function setKeyValue($key, $value)
    {
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
    public static function getKeyValue($key)
    {
        if ((isset(self::$m_globalArgs[$key])) && (!empty(self::$m_globalArgs[$key]))) {
            return self::$m_globalArgs[$key];
        }
        return false;
    }

    /**
     * setAccount
     *      set the account we want to use
     *
     * @param mixed $account
     * @static
     * @access public
     */
    public static function setAccount($account)
    {
        self::$m_globalArgs['account'] = $account;
    }

    /**
     * getAccountInfo
     *      returns the account credentials for account specified
     *
     * @param mixed $account
     * @static
     * @access public
     * @return void
     */

    public static function getAccountInfo()
    {
        if ((!isset(self::$m_globalArgs['account'])) || (empty(self::$m_globalArgs['account']))) {
            return false;
        }
        $account = self::$m_globalArgs['account'];
        $credentials = include(dirname(dirname(__FILE__)) . '/credentials.inc.php');
        if ((isset($credentials[$account])) && (!empty($credentials[$account]))) {
            return $credentials[$account];
        }
        return false;
    }

    /**
     * getAccounts
     *      return an array of the possible accounts we have set in our credentials file.
     *
     * @static
     * @access public
     */
    public static function getAccounts()
    {
        $credentials = include(dirname(dirname(__FILE__)) . '/credentials.inc.php');
        return array_keys($credentials);
    }

/* Closing Brace for Class */
}

