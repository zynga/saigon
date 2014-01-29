<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPEPluginData extends RenderData
{

    public $nrpeplugin;
    public $nrpepluginInfo;
    public $action;
    public $revision;
    public $oldNRPEPluginInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment        deployment we are making the change too
     * @param mixed $revision          revision we are making the change too
     * @param mixed $nrpeplugin        nrpe core plugin we are referencing
     * @param array $nrpepluginInfo    nrpe core plugin information
     * @param mixed $action            action that was being requested
     * @param array $oldNRPEPluginInfo old nrpe core plugin information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $nrpeplugin, array $nrpepluginInfo,
        $action, array $oldNRPEPluginInfo = array()
    ) {
        parent::__construct($deployment);
        $this->nrpeplugin = $nrpeplugin;
        $this->nrpepluginInfo = $nrpepluginInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldNRPEPluginInfo = $oldNRPEPluginInfo;
        }
    }

}

class NRPEPluginDataRenderer implements LoggerRendererObject
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
        $nrpepluginInfo = array();
        foreach ($testData->nrpepluginInfo as $key => $value) {
            array_push($nrpepluginInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=nrpeplugin action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " nrpe_plugin_info=[".implode(", ", $nrpepluginInfo)."]";
        if ($testData->action == 'modify') {
            $oldNRPEPluginInfo = array();
            foreach ($testData->oldNRPEPluginInfo as $key => $value) {
                array_push($oldNRPEPluginInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_nrpe_plugin_info=[".implode(", ", $oldNRPEPluginInfo)."]";
        }
        return $msg;
    }

}

