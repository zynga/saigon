<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagTester
{

    protected static $init = false;
    protected static $log;
    protected static $locktime = 900;

    /**
     * init 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function init()
    {
        /* Initial Redis Information */
        if (self::$init === false) {
            NagRedis::init();
            self::$log = new NagLogger();
            self::$init = true;
        }
        return;
    }

    /**
     * getDeploymentBuildLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function getDeploymentBuildLock($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            return NagRedis::get(md5('deployment:'.$deployment).':'.$revision.':buildlock');
        }
        else {
            return NagRedis::get(md5('deployment:'.$deployment).':'.$revision.':buildlock:'.$subdeployment);
        }
    }

    /**
     * setDeploymentBuildLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function setDeploymentBuildLock($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            if (NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':buildlock')) {
                return false;
            }
            NagRedis::set(md5('deployment:'.$deployment).':'.$revision.':buildlock', 1);
            NagRedis::setTTL(md5('deployment:'.$deployment).':'.$revision.':buildlock', self::$locktime);
        }
        else {
            if (NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':buildlock:'.$subdeployment)) {
                return false;
            }
            NagRedis::set(md5('deployment:'.$deployment).':'.$revision.':buildlock:'.$subdeployment, 1);
            NagRedis::setTTL(md5('deployment:'.$deployment).':'.$revision.':buildlock:'.$subdeployment, self::$locktime);
        }
        return true;
    }

    /**
     * deleteDeploymentBuildLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function deleteDeploymentBuildLock($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':buildlock');
        }
        else {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':buildlock:'.$subdeployment);
        }
    }

    /**
     * getDeploymentBuildInfo 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function getDeploymentBuildInfo($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':buildoutput');
        }
        else {
            return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':buildoutput:'.$subdeployment);
        }
    }

    /**
     * setDeploymentBuildInfo 
     * 
     * @param mixed $deployment     deployment we are working on 
     * @param mixed $subdeployment  subdeployment we are working on 
     * @param mixed $revision       revision we are working on
     * @param mixed $deploymentInfo deployment build meta information 
     *
     * @static
     * @access public
     * @return void
     */
    public static function setDeploymentBuildInfo($deployment, $subdeployment, $revision, $deploymentInfo)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':buildoutput');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':buildoutput', $deploymentInfo);
        }
        else {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':buildoutput:'.$subdeployment);
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':buildoutput:'.$subdeployment, $deploymentInfo);
        }
    }

    /**
     * getDeploymentTestLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function getDeploymentTestLock($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            return NagRedis::get(md5('deployment:'.$deployment).':'.$revision.':testlock');
        }
        else {
            return NagRedis::get(md5('deployment:'.$deployment).':'.$revision.':testlock:'.$subdeployment);
        }
    }

    /**
     * setDeploymentTestLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function setDeploymentTestLock($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            if (NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':testlock')) {
                return false;
            }
            NagRedis::set(md5('deployment:'.$deployment).':'.$revision.':testlock', 1);
            NagRedis::setTTL(md5('deployment:'.$deployment).':'.$revision.':testlock', self::$locktime);
        }
        else {
            if (NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':testlock:'.$subdeployment)) {
                return false;
            }
            NagRedis::set(md5('deployment:'.$deployment).':'.$revision.':testlock:'.$subdeployment, 1);
            NagRedis::setTTL(md5('deployment:'.$deployment).':'.$revision.':testlock:'.$subdeployment, self::$locktime);
        }
        return true;
    }

    /**
     * deleteDeploymentTestLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function deleteDeploymentTestLock($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':testlock');
        }
        else {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':testlock:'.$subdeployment);
        }
    }

    /**
     * getDeploymentTestInfo 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     * @param mixed $revision      revision we are working on
     *
     * @static
     * @access public
     * @return void
     */
    public static function getDeploymentTestInfo($deployment, $subdeployment, $revision)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':testoutput');
        }
        else {
            return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':testoutput:'.$subdeployment);
        }
    }

    /**
     * setDeploymentTestInfo 
     * 
     * @param mixed $deployment     deployment we are working on 
     * @param mixed $subdeployment  subdeployment we are working on 
     * @param mixed $revision       revision we are working on
     * @param mixed $deploymentInfo deployment test output information
     *
     * @static
     * @access public
     * @return void
     */
    public static function setDeploymentTestInfo($deployment, $subdeployment, $revision, $deploymentInfo)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':testoutput');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':testoutput', $deploymentInfo);
        }
        else {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':testoutput:'.$subdeployment);
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':testoutput:'.$subdeployment, $deploymentInfo);
        }
    }

    /**
     * getDeploymentDiffLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     *
     * @static
     * @access public
     * @return void
     */
    public static function getDeploymentDiffLock($deployment, $subdeployment)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            return NagRedis::get(md5('deployment:'.$deployment).':difflock');
        }
        else {
            return NagRedis::get(md5('deployment:'.$deployment).':difflock:'.$subdeployment);
        }
    }

    /**
     * setDeploymentDiffLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     *
     * @static
     * @access public
     * @return void
     */
    public static function setDeploymentDiffLock($deployment, $subdeployment)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            if (NagRedis::exists(md5('deployment:'.$deployment).':difflock')) {
                return false;
            }
            NagRedis::set(md5('deployment:'.$deployment).':difflock', 1);
            NagRedis::setTTL(md5('deployment:'.$deployment).':difflock', self::$locktime);
        }
        else {
            if (NagRedis::exists(md5('deployment:'.$deployment).':difflock:'.$subdeployment)) {
                return false;
            }
            NagRedis::set(md5('deployment:'.$deployment).':difflock:'.$subdeployment, 1);
            NagRedis::setTTL(md5('deployment:'.$deployment).':difflock:'.$subdeployment, self::$locktime);
        }
        return true;
    }

    /**
     * deleteDeploymentDiffLock 
     * 
     * @param mixed $deployment    deployment we are working on 
     * @param mixed $subdeployment subdeployment we are working on 
     *
     * @static
     * @access public
     * @return void
     */
    public static function deleteDeploymentDiffLock($deployment, $subdeployment)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            NagRedis::del(md5('deployment:'.$deployment).':difflock');
        }
        else {
            NagRedis::del(md5('deployment:'.$deployment).':difflock:'.$subdeployment);
        }
    }

    /**
     * getDeploymentDiffInfo 
     * 
     * @param mixed $deployment    deployment we need to fetch the diff for 
     * @param mixed $subdeployment subdeployment we are working on 
     *
     * @static
     * @access public
     * @return void
     */
    public static function getDeploymentDiffInfo($deployment, $subdeployment)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            return NagRedis::hGetAll(md5('deployment:'.$deployment).':diffoutput');
        }
        else {
            return NagRedis::hGetAll(md5('deployment:'.$deployment).':diffoutput:'.$subdeployment);
        }
    }

    /**
     * setDeploymentDiffInfo 
     * 
     * @param mixed $deployment     deployment we are setting the diff for
     * @param mixed $subdeployment  subdeployment we are working on 
     * @param mixed $deploymentInfo deployment diff information
     *
     * @static
     * @access public
     * @return void
     */
    public static function setDeploymentDiffInfo($deployment, $subdeployment, $deploymentInfo)
    {
        if (self::$init === false) self::init();
        if ($subdeployment === false) {
            NagRedis::del(md5('deployment:'.$deployment).':diffoutput');
            NagRedis::hMSet(md5('deployment:'.$deployment).':diffoutput', $deploymentInfo);
        }
        else {
            NagRedis::del(md5('deployment:'.$deployment).':diffoutput:'.$subdeployment);
            NagRedis::hMSet(md5('deployment:'.$deployment).':diffoutput:'.$subdeployment, $deploymentInfo);
        }
    }

    public static function setDeploymentHostAuditInfo($deployment, $deploymentInfo)
    {
        if (self::$init === false) self::init();
        NagRedis::set(md5('deployment:'.$deployment).':hostaudit', $deploymentInfo);
    }

    public static function getDeploymentHostAuditInfo($deployment)
    {
        if (self::$init === false) self::init();
        return NagRedis::get(md5('deployment:'.$deployment).':hostaudit');
    }

}

