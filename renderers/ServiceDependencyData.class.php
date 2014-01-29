<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ServiceDependencyData extends RenderData
{

    public $svcDep;
    public $svcDepInfo;
    public $action;
    public $revision;
    public $oldSvcDepInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment    deployment we are making the change too
     * @param mixed $revision      revision we are making the change too
     * @param mixed $svcDep        service dependency we are referencing
     * @param array $svcDepInfo    service dependency information
     * @param mixed $action        action that was being requested
     * @param array $oldSvcDepInfo old service dependency information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $svcDep, array $svcDepInfo,
        $action, array $oldSvcDepInfo = array()
    ) {
        parent::__construct($deployment);
        $this->svcDep = $svcDep;
        $this->svcDepInfo = $svcDepInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldSvcDepInfo = $oldSvcDepInfo;
        }
    }

}

class ServiceDependencyDataRenderer implements LoggerRendererObject
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
        $svcDepInfo = array();
        foreach ($testData->svcDepInfo as $key => $value) {
            array_push($svcDepInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip} controller=svcdep";
        $msg .= " revision={$testData->revision}";
        $msg .= " action={$testData->action} deployment={$testData->deployment}";
        $msg .= " service_dependency_info=[".implode(", ", $svcDepInfo)."]";
        if ($testData->action == 'modify') {
            $oldSvcDepInfo = array();
            foreach ($testData->oldSvcDepInfo as $key => $value) {
                array_push($oldSvcDepInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_service_dependency_info=[".implode(", ", $oldSvcDepInfo)."]";
        }
        return $msg;
    }

}

