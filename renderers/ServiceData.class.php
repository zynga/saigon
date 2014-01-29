<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ServiceData extends RenderData
{

    public $svc;
    public $svcInfo;
    public $action;
    public $revision;
    public $oldServiceInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment     deployment we are making the change too
     * @param mixed $revision       revision we are making the change too
     * @param mixed $svc            service we are referencing
     * @param array $svcInfo        service config information
     * @param mixed $action         action that was being requested
     * @param array $oldServiceInfo old service config information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $svc, array $svcInfo,
        $action, array $oldServiceInfo = array()
    ) {
        parent::__construct($deployment);
        $this->svc = $svc;
        $this->svcInfo = $svcInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldServiceInfo = $oldServiceInfo;
        }
    }

}

class ServiceDataRenderer implements LoggerRendererObject
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
        $svcInfo = array();
        foreach ($testData->svcInfo as $key => $value) {
            array_push($svcInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=svc action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " service_info=[".implode(", ", $svcInfo)."]";
        if ($testData->action == 'modify') {
            $oldServiceInfo = array();
            foreach ($testData->oldServiceInfo as $key => $value) {
                array_push($oldServiceInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_service_info=[".implode(", ", $oldServiceInfo)."]";
        }
        return $msg;
    }

}

