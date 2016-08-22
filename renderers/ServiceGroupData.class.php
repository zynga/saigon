<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ServiceGroupData extends RenderData
{

    public $svcGroup;
    public $svcGrpInfo;
    public $action;
    public $revision;
    public $oldSvcGrpInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment    deployment we are making the change too
     * @param mixed $revision      revision we are making the change too
     * @param mixed $svcGroup      service escalation we are referencing
     * @param array $svcGrpInfo    service escalation information
     * @param mixed $action        action that was being requested
     * @param array $oldSvcGrpInfo old service escalation information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $svcGroup, array $svcGrpInfo,
        $action, array $oldSvcGrpInfo = array()
    ) {
        parent::__construct($deployment);
        $this->svcGroup = $svcGroup;
        $this->svcGrpInfo = $svcGrpInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldSvcGrpInfo = $oldSvcGrpInfo;
        }
    }

}

class ServiceGroupDataRenderer implements LoggerRendererObject
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
        $svcGroupInfo = array();
        foreach ($testData->svcGrpInfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($svcGroupInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=svcgrp action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " service_group_info=[".implode(", ", $svcGroupInfo)."]";
        if ($testData->action == 'modify') {
            $oldServiceGroupInfo = array();
            foreach ($testData->oldSvcGrpInfo as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldServiceGroupInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_service_group_info=[".implode(", ", $oldServiceGroupInfo)."]";
        }
        return $msg;
    }

}

