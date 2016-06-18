<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagiosPluginData extends RenderData
{

    public $nagiosplugin;
    public $nagiospluginInfo;
    public $action;
    public $revision;
    public $oldNagiosPluginInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment          deployment we are making the change too
     * @param mixed $revision            revision we are making the change too
     * @param mixed $nagiosplugin        nagios plugin we are referencing
     * @param array $nagiospluginInfo    nagios plugin information
     * @param mixed $action              action that was being requested
     * @param array $oldNagiosPluginInfo old nagios plugin information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $nagiosplugin, array $nagiospluginInfo,
        $action, array $oldNagiosPluginInfo = array()
    ) {
        parent::__construct($deployment);
        $this->nagiosplugin = $nagiosplugin;
        $this->nagiospluginInfo = $nagiospluginInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldNagiosPluginInfo = $oldNagiosPluginInfo;
        }
    }

}

class NagiosPluginDataRenderer implements LoggerRendererObject
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
        $nagiospluginInfo = array();
        foreach ($testData->nagiospluginInfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($nagiospluginInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=nagiosplugin action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " nagios_plugin_info=[".implode(", ", $nagiospluginInfo)."]";
        if ($testData->action == 'modify') {
            $oldNagiosPluginInfo = array();
            foreach ($testData->oldNagiosPluginInfo as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldNagiosPluginInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_nagios_plugin_info=[".implode(", ", $oldNagiosPluginInfo)."]";
        }
        return $msg;
    }

}

