<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SupNRPEPluginData extends RenderData
{

    public $supnrpeplugin;
    public $supnrpepluginInfo;
    public $action;
    public $revision;
    public $oldSupNRPEPluginInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment           deployment we are making the change too
     * @param mixed $revision             revision we are making the change too
     * @param mixed $supnrpeplugin        nrpe supplemental plugin we are referencing
     * @param array $supnrpepluginInfo    nrpe supplemental plugin information
     * @param mixed $action               action that was being requested
     * @param array $oldSupNRPEPluginInfo old nrpe supplemental plugin information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $supnrpeplugin, array $supnrpepluginInfo,
        $action, array $oldSupNRPEPluginInfo = array()
    ) {
        parent::__construct($deployment);
        $this->supnrpeplugin = $supnrpeplugin;
        $this->supnrpepluginInfo = $supnrpepluginInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldSupNRPEPluginInfo = $oldSupNRPEPluginInfo;
        }
    }

}

class SupNRPEPluginDataRenderer implements LoggerRendererObject
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
        $supnrpepluginInfo = array();
        foreach ($testData->supnrpepluginInfo as $key => $value) {
            array_push($supnrpepluginInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=nrpeplugin action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " nrpe_sup_plugin_info=[".implode(", ", $supnrpepluginInfo)."]";
        if ($testData->action == 'modify') {
            $oldSupNRPEPluginInfo = array();
            foreach ($testData->oldSupNRPEPluginInfo as $key => $value) {
                array_push($oldSupNRPEPluginInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_nrpe_sup_plugin_info=[".implode(", ", $oldSupNRPEPluginInfo)."]";
        }
        return $msg;
    }

}

