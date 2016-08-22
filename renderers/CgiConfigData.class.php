<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CgiConfigData extends RenderData
{

    public $configinfo;
    public $action;
    public $revision;
    public $oldConfiginfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment    deployment we are making the change too
     * @param mixed $revision      revision we are making the change too
     * @param array $configinfo    cgi configuration information we are inputting
     * @param mixed $action        action that was being requested
     * @param array $oldConfiginfo old cgi configuration information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, array $configinfo,
        $action, array $oldConfiginfo = array()
    ) {
        parent::__construct($deployment);
        $this->configinfo = $configinfo;
        $this->action = $action;
        $this->revision = $revision;
        if (($action == 'modify') || ($action == 'delete')) {
            $this->oldConfiginfo = $oldConfiginfo;
        }
    }

}

class CgiConfigDataRenderer implements LoggerRendererObject
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
        $configinfo = array();
        foreach ($testData->configinfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($configinfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=cgicfg action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        if ($testData->action != 'delete') {
            $msg .= " cgi_cfg_info=[".implode(", ", $configinfo)."]";
        }
        if (($testData->action == 'modify') || ($testData->action =='delete')) {
            $oldConfiginfo = array();
            foreach ($testData->oldConfiginfo as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldConfiginfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_cgi_cfg_info=[".implode(", ", $oldConfiginfo)."]";
        }
        return $msg;
    }

}

