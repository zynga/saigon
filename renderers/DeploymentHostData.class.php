<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class DeploymentHostData extends RenderData
{

    public $action;
    public $type;
    public $hostInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment  deployment we are making the change too
     * @param mixed $action      action that was being requested
     * @param mixed $type        type of host we are interacting with
     * @param array $hostInfo    host group information we are inputting
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $action, $type, array $hostInfo
    ) {
        parent::__construct($deployment);
        $this->action = $action;
        $this->type = $type;
        $this->hostInfo = $hostInfo;
    }

}

class DeploymentHostDataRenderer implements LoggerRendererObject
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
        $hostGroupInfo = array();
        foreach ($testData->hostInfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($hostGroupInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " controller=deploymenthost";
        if ($testData->action == 'add') {
            $msg .= " action=add/modify";
        }
        elseif ($testData->action == 'del') {
            $msg .= " action=delete";
        }
        $msg .= " deployment={$testData->deployment}";
        $msg .= " host_info=[".implode(", ", $hostGroupInfo)."]";
        return $msg;
    }

}

