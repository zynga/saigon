<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class HostTemplateData extends RenderData
{

    public $hostTemplate;
    public $hostInfo;
    public $action;
    public $revision;
    public $oldHostInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment   deployment we are making the change too
     * @param mixed $revision     revision we are making the change too
     * @param mixed $hostTemplate host template we are modifying
     * @param array $hostInfo     host template information we are inputting
     * @param mixed $action       action that was being requested
     * @param array $oldHostInfo  old host template information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $hostTemplate, array $hostInfo,
        $action, array $oldHostInfo = array()
    ) {
        parent::__construct($deployment);
        $this->hostTemplate = $hostTemplate;
        $this->hostInfo = $hostInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldHostInfo = $oldHostInfo;
        }
    }

}

class HostTemplateDataRenderer implements LoggerRendererObject
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
        $hostInfo = array();
        foreach ($testData->hostInfo as $key => $value) {
            array_push($hostInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=hosttemp action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " host_template_info=[".implode(", ", $hostInfo)."]";
        if ($testData->action == 'modify') {
            $oldHostInfo = array();
            foreach ($testData->oldHostInfo as $key => $value) {
                array_push($oldHostInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_host_template_info=[".implode(", ", $oldHostInfo)."]";
        }
        return $msg;
    }

}

