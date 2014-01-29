<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagImport
{

    /**
     * processNRPECfg 
     * 
     * @param mixed $filecontents contents of file that was read into memory
     *
     * @static
     * @access public
     * @return void
     */
    public static function processNRPECfg($filecontents)
    {
        $results = array();
        $results['meta'] = array();
        $results['cmds'] = array();
        $lines = explode("\n", $filecontents);
        foreach ($lines as $line) {
            if ((empty($line)) || (preg_match("/^(\s+|\t+)?#/", $line))) {
                continue;
            }
            if (!preg_match("/^command\[/", $line)) {
                $tmpdata = preg_split("/=/", $line);
                $results['meta'][$tmpdata[0]] = $tmpdata[1];
            } elseif (preg_match("/^command\[/", $line)) {
                preg_match("/^command\[(\w+)\]\=(.*)$/", $line, $matches);
                $results['cmds'][$matches[1]] = $matches[2];
            }
        }
        return $results;
    }

    /**
     * processSupNRPECfg 
     * 
     * @param mixed $filecontents contents of file that was read into memory
     *
     * @static
     * @access public
     * @return void
     */
    public static function processSupNRPECfg($filecontents)
    {
        $results = array();
        $lines = explode("\n", $filecontents);
        foreach ($lines as $line) {
            if ((empty($line)) || (preg_match("/^(\s+|\t+)?#/", $line))) {
                continue;
            }
            if (preg_match("/^command\[/", $line)) {
                preg_match("/^command\[(\w+)\]\=(.*)$/", $line, $matches);
                $results[$matches[1]] = $matches[2];
            }
        }
        return $results;
    }

}

