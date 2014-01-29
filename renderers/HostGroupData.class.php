<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class HostGroupData extends RenderData
{

    public $hostGroup;
    public $hostInfo;
    public $action;
    public $revision;
    public $oldHostInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment  deployment we are making the change too
     * @param mixed $revision    revision we are making the change too
     * @param mixed $hostGroup   host group we are modifying
     * @param array $hostInfo    host group information we are inputting
     * @param mixed $action      action that was being requested
     * @param array $oldHostInfo old host group information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $hostGroup, array $hostInfo,
        $action, array $oldHostInfo = array()
    ) {
        parent::__construct($deployment);
        $this->hostGroup = $hostGroup;
        $this->hostInfo = $hostInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldHostInfo = $oldHostInfo;
        }
    }

}

class HostGroupDataRenderer implements LoggerRendererObject
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
            array_push($hostGroupInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=hostgrp action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " host_group_info=[".implode(", ", $hostGroupInfo)."]";
        if ($testData->action == 'modify') {
            $oldHostGroupInfo = array();
            foreach ($testData->oldHostInfo as $key => $value) {
                array_push($oldHostGroupInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_host_group_info=[".implode(", ", $oldHostGroupInfo)."]";
        }
        return $msg;
    }

}

