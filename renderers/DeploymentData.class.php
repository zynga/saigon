<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class DeploymentData extends RenderData
{

    public $deployInfo;
    public $dynhosts;
    public $action;
    public $statichosts;
    public $oldInfo;
    public $oldDynhosts;
    public $oldStatichosts;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment           deployment we are making the change too
     * @param array $deployInfo           deployment meta information
     * @param array $deployDynHosts       deployment dynamic host data
     * @param array $deployStaticHosts    deployment static hosts
     * @param mixed $action               action that was being requested
     * @param array $oldDeployInfo        old deployment meta information
     * @param array $oldDeployDynHosts    old deployment dynamic host data
     * @param array $oldDeployStaticHosts old deployment static hosts
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, array $deployInfo, array $deployDynHosts,
        array $deployStaticHosts, $action, array $oldDeployInfo = array(),
        array $oldDeployDynHosts = array(), array $oldDeployStaticHosts = array()
    ) {
        parent::__construct($deployment);
        $this->deployInfo = $deployInfo;
        $this->dynhosts = $deployDynHosts;
        $this->action = $action;
        $this->statichosts = $deployStaticHosts;
        if ($action == 'modify') {
            $this->oldInfo = $oldDeployInfo;
            $this->oldDynhosts = $oldDeployDynHosts;
            $this->oldStatichosts = $oldDeployStaticHosts;
        }
    }

}

class DeploymentDataRenderer implements LoggerRendererObject
{
    
    /**
     * render - render function called up after initializing data object class
     * 
     * @param mixed $testData data object put together by data class 
     *
     * @access public
     * @return void
     */
    public function render($testData)
    {
        $deployInfo = array(); $dynhostsInfo = array(); $staticHosts = array();
        foreach ($testData->deployInfo as $key => $value) {
            array_push($deployInfo, "\"$key\" => \"$value\"");
        }
        foreach ($testData->dynhosts as $md5Key => $tmpArray) {
            array_push($dynhostsInfo, '"'.$tmpArray['location'].'" => "'.$tmpArray['srchparam'].'"');
        }
        foreach ($testData->statichosts as $encIP => $tmpArray) {
            array_push($staticHosts, '"'.$tmpArray['host'].'" => "'.$tmpArray['ip'].'"');
        }
        $msg = "{$testData->user} {$testData->ip} controller=deployment";
        $msg .= " action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " deployment_info=[".implode(", ", $deployInfo)."]";
        $msg .= " deployment_dyn_hosts=[".implode(", ", $dynhostsInfo)."]";
        $msg .= " deployment_static_hosts=[".implode(", ", $staticHosts)."]";
        if ($testData->action == 'modify') {
            $oldDeployInfo = array(); $oldDynHostsInfo = array(); $oldStaticHosts = array();
            foreach ($testData->oldInfo as $key => $value) {
                array_push($oldDeployInfo, "\"$key\" => \"$value\"");
            }
            foreach ($testData->oldDynhosts as $md5Key => $tmpArray) {
                array_push($oldDynHostsInfo, '"'.$tmpArray['location'].'" => "'.$tmpArray['srchparam'].'"');
            }
            foreach ($testData->oldStatichosts as $encIP => $tmpArray) {
                array_push($oldStaticHosts, '"'.$tmpArray['host'].'" => "'.$tmpArray['ip'].'"');
            }
            $msg .= " old_deployment_info=[".implode(", ", $oldDeployInfo)."]";
            $msg .= " old_deployment_dyn_hosts=[".implode(", ", $oldDynHostsInfo)."]";
            $msg .= " old_deployment_static_hosts=[".implode(", ", $oldStaticHosts)."]";
        }
        return $msg;
    }

}

