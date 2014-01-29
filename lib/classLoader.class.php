<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class Saigon_ClassLoader
{

    private static $_classMapping = array();
    
    /**
     * register - register function for Saigon autoloader 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function register()
    {
        self::_loadClassMapping();
        if ($loaders = spl_autoload_functions()) {
            array_map('spl_autoload_unregister', $loaders);
        } else {
            $loaders = function_exists('__autoload')?array('__autoload'):array();
        }
        array_unshift($loaders, array(__CLASS__, 'nagLoad'));
        array_map('spl_autoload_register', $loaders);
    }

    /**
     * nagLoad - Saigon autoload function
     * 
     * @param mixed $class class we are attempting to load
     *
     * @static
     * @access public
     * @return void
     */
    public static function nagLoad($class)
    {
        $class = strtolower($class);
        if (array_key_exists($class, self::$_classMapping)) {
            $file = BASE_PATH.'/'.self::$_classMapping[$class];
            if (file_exists($file)) {
                require_once $file;
            }
        }
    }

    /**
     * _loadClassMapping - internal function for parsing class_mapping file 
     * 
     * @static
     * @access private
     * @return void
     */
    private static function _loadClassMapping()
    {
        $class_map_ini = BASE_PATH.'/lib/class_mapping.ini';
        if (file_exists($class_map_ini)) {
            $class_mapping = parse_ini_file($class_map_ini);
            //keys in arrays are case sensitive, classnames are not!
            self::$_classMapping = array_change_key_case($class_mapping, CASE_LOWER);
        } else {
            error_log('Saigon Autoloader: Could not load class mapping file at: '.$class_map_ini);
            die();
        }
    }

}

