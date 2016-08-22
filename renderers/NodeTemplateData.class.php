<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NodeTemplateData extends RenderData
{

    public $nodeTemplate;
    public $nodeTemplateInfo;
    public $action;
    public $revision;
    public $oldNodeTemplateInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment          deployment we are making the change too
     * @param mixed $revision            revision we are making the change too
     * @param mixed $nodeTemplate        nagios plugin we are referencing
     * @param array $nodeTemplateInfo    nagios plugin information
     * @param mixed $action              action that was being requested
     * @param array $oldNodeTemplateInfo old nagios plugin information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $nodeTemplate, array $nodeTemplateInfo,
        $action, array $oldNodeTemplateInfo = array()
    ) {
        parent::__construct($deployment);
        $this->nodeTemplate = $nodeTemplate;
        $this->nodeTemplateInfo = $nodeTemplateInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldNodeTemplateInfo = $oldNodeTemplateInfo;
        }
    }

}

class NodeTemplateDataRenderer implements LoggerRendererObject
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
        $nodeTemplateInfo = array();
        foreach ($testData->nodeTemplateInfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($nodeTemplateInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=ngnt action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " node_template_info=[".implode(", ", $nodeTemplateInfo)."]";
        if ($testData->action == 'modify') {
            $oldNodeTemplateInfo = array();
            foreach ($testData->oldNodeTemplateInfo as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldNodeTemplateInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_node_template_info=[".implode(", ", $oldNodeTemplateInfo)."]";
        }
        return $msg;
    }

}

