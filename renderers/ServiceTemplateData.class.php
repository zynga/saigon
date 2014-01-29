<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ServiceTemplateData extends RenderData
{

    public $svcTemplate;
    public $svcTempInfo;
    public $action;
    public $revision;
    public $oldSvcTempInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment     deployment we are making the change too
     * @param mixed $revision       revision we are making the change too
     * @param mixed $svcTemplate    service escalation we are referencing
     * @param array $svcTempInfo    service escalation information
     * @param mixed $action         action that was being requested
     * @param array $oldSvcTempInfo old service escalation information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $svcTemplate, array $svcTempInfo,
        $action, array $oldSvcTempInfo = array()
    ) {
        parent::__construct($deployment);
        $this->svcTemplate = $svcTemplate;
        $this->svcTempInfo = $svcTempInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldSvcTempInfo = $oldSvcTempInfo;
        }
    }

}

class ServiceTemplateDataRenderer implements LoggerRendererObject
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
        $svcTempInfo = array();
        foreach ($testData->svcTempInfo as $key => $value) {
            array_push($svcTempInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=svctemp action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " service_template_info=[".implode(", ", $svcTempInfo)."]";
        if ($testData->action == 'modify') {
            $oldSvcTempInfo = array();
            foreach ($testData->oldSvcTempInfo as $key => $value) {
                array_push($oldSvcTempInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_service_template_info=[".implode(", ", $oldSvcTempInfo)."]";
        }
        return $msg;
    }

}

