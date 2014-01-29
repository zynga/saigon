<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//


class NagPhean
{

    protected static $init = false;
    protected static $pheanstalk;

    /**
     * init - initialize the connection to beanstalkd
     * 
     * @param mixed $server        server ip to connect too
     * @param mixed $defaultTube   specify a default tube to watch
     * @param bool  $ignoreDefault ignore the 'default' tube
     *
     * @access public
     * @return void
     */
    public static function init($server, $defaultTube = false, $ignoreDefault = false)
    {
        if (self::$init === false) {
            self::$pheanstalk = new Pheanstalk_Pheanstalk($server);
            if (($ignoreDefault === true) && ($defaultTube !== false)) {
                if (preg_match('/,/', $defaultTube)) {
                    $tubes = preg_split("/\s?,\s?/", $defaultTube);
                    foreach ( $tubes as $tube ) {
                        self::$pheanstalk
                            ->watch($tube);
                    }
                    self::$pheanstalk
                        ->ignore('default');
                }
                else {
                    self::$pheanstalk
                        ->watch($defaultTube)
                        ->ignore('default');
                }
            } else if (($ignoreDefault === false) && ($defaultTube !== false)) {
                if (preg_match('/,/', $defaultTube)) {
                    $tubes = preg_split("/\s?,\s?/", $defaultTube);
                    foreach ( $tubes as $tube ) {
                        self::$pheanstalk
                            ->watch($tube);
                    }
                }
                else {
                    self::$pheanstalk
                        ->watch($defaultTube);
                }
            }
            self::$init = true;
        }
        return;
    }

    /**
     * addJob - add a job to beanstalkd tube
     * 
     * @param mixed $tube     tube we want to add the job into
     * @param mixed $payload  data we need to store
     * @param int   $priority job priority (lower the priority, faster it floats to the top)
     * @param int   $delay    time delay before job enters ready state
     * @param int   $ttr      time to retry if job isn't removed from tube
     *
     * @access public
     * @return void
     */
    public static function addJob($tube, $payload, $priority = 1024, $delay = 120, $ttr = 900)
    {
        if (self::$init === false) return false;
        $id = self::$pheanstalk
            ->useTube($tube)
            ->put($payload, $priority, $delay, $ttr);
        return $id;
    }

    /**
     * reserveJob - reserve job from the tubes we are watching 
     * 
     * @access public
     * @return void
     */
    public static function reserveJob()
    {
        if (self::$init === false) return false;
        $job = self::$pheanstalk
            ->reserve();
        return $job;
    }

    /**
     * delJob - delete job from beanstalkd after done processing it 
     * 
     * @param mixed $job job object that is returned from reserveJob
     *
     * @access public
     * @return void
     */
    public static function delJob($job)
    {
        if (self::$init === false) return false;
        $results = self::$pheanstalk
            ->delete($job);
        return $results;
    }

    /**
     * statsJob - return stats on job, like tube, ttr, priority, etc 
     * 
     * @param mixed $job job object that is returned from reserveJob
     *
     * @access public
     * @return void
     */
    public static function statsJob($job)
    {
        if (self::$init === false) return false;
        $results = self::$pheanstalk
            ->statsJob($job);
        return $results;
    }

    /**
     * watchTube - update the list of tubes you are watching 
     * 
     * @param mixed $tube tube(s) you are attempting to watch for jobs
     *
     * @access public
     * @return void
     */
    public static function watchTube($tube)
    {
        if (self::$init === false) return false;
        if (is_array($tube)) {
            $results = array();
            foreach ($tube as $single_tube) {
                $results[] = self::$pheanstalk
                    ->watch($single_tube);
            }
        } else {
            $results = self::$pheanstalk
                ->watch($tube);
        }
        return $results;
    }

    /**
     * ignoreTube - ignore a list of tubes so you don't attempt to process a job in them
     * 
     * @param mixed $tube tube(s) you are wanting to ignore
     *
     * @access public
     * @return void
     */
    public static function ignoreTube($tube)
    {
        if (self::$init === false) return false;
        if (is_array($tube)) {
            $results = array();
            foreach ($tube as $single_tube) {
                $results[] = self::$pheanstalk
                    ->ignore($single_tube);
            }
        } else {
            $results = self::$pheanstalk
                ->ignore($tube);
        }
        return $results;
    }

    /**
     * listTubes - return a list of tubes available on the beanstalkd server 
     * 
     * @access public
     * @return void
     */
    public static function listTubes()
    {
        if (self::$init === false) return false;
        $results = self::$pheanstalk
            ->listTubes();
        return $results;
    }

    /**
     * watchingTubes - return a list of tubes we are watching 
     * 
     * @access public
     * @return void
     */
    public static function watchingTubes()
    {
        if (self::$init === false) return false;
        $results = self::$pheanstalk
            ->listTubesWatched();
        return $results;
    }

    /**
     * watchingTubesCount - return count of tubes we are watching 
     * 
     * @access public
     * @return void
     */
    public static function watchingTubesCount()
    {
        if (self::$init === false) return false;
        $tmpArray = self::$pheanstalk
            ->listTubesWatched();
        return count($tmpArray);
    }

}

