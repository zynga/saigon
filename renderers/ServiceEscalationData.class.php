<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ServiceEscalationData extends RenderData
{

    public $svcEsc;
    public $svcEscInfo;
    public $action;
    public $revision;
    public $oldSvcEscInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment    deployment we are making the change too
     * @param mixed $revision      revision we are making the change too
     * @param mixed $svcEsc        service escalation we are referencing
     * @param array $svcEscInfo    service escalation information
     * @param mixed $action        action that was being requested
     * @param array $oldSvcEscInfo old service escalation information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $svcEsc, array $svcEscInfo,
        $action, array $oldSvcEscInfo = array()
    ) {
        parent::__construct($deployment);
        $this->svcEsc = $svcEsc;
        $this->svcEscInfo = $svcEscInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldSvcEscInfo = $oldSvcEscInfo;
        }
    }

}

class ServiceEscalationDataRenderer implements LoggerRendererObject
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
        $svcEscInfo = array();
        foreach ($testData->svcEscInfo as $key => $value) {
            array_push($svcEscInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip} controller=svcesc";
        $msg .= " revision={$testData->revision}";
        $msg .= " action={$testData->action} deployment={$testData->deployment}";
        $msg .= " service_escalation_info=[".implode(", ", $svcEscInfo)."]";
        if ($testData->action == 'modify') {
            $oldSvcEscInfo = array();
            foreach ($testData->oldSvcEscInfo as $key => $value) {
                array_push($oldSvcEscInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_service_escalation_info=[".implode(", ", $oldSvcEscInfo)."]";
        }
        return $msg;
    }

}

