<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ResourceConfigData extends RenderData
{

    public $resources;
    public $action;
    public $revision;
    public $oldResources;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment   deployment we are making the change too
     * @param mixed $revision     revision we are making the change too
     * @param array $resources    resource config information
     * @param mixed $action       action that was being requested
     * @param array $oldResources old resource config information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, array $resources,
        $action, array $oldResources = array()
    ) {
        parent::__construct($deployment);
        $this->resources = $resources;
        $this->action = $action;
        $this->revision = $revision;
        if (($action == 'modify') || ($action == 'delete')) {
            $this->oldResources = $oldResources;
        }
    }

}

class ResourceConfigDataRenderer implements LoggerRendererObject
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
        $resources = array();
        foreach ($testData->resources as $key => $value) {
            array_push($resources, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=resourcecfg action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        if ($testData->action != 'delete') {
            $msg .= " resource_info=[".implode(", ", $resources)."]";
        }
        if (($testData->action == 'modify') || ($testData->action == 'delete')) {
            $oldResources = array();
            foreach ($testData->oldResources as $key => $value) {
                array_push($oldResources, "\"$key\" => \"$value\"");
            }
            $msg .= " old_resource_info=[".implode(", ", $oldResources)."]";
        }
        return $msg;
    }

}

