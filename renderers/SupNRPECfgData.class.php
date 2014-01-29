<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SupNRPECfgData extends RenderData
{

    public $supnrpecfg;
    public $action;
    public $revision;
    public $oldsupnrpecfg;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment        deployment we are making the change too
     * @param mixed $revision          revision we are making the change too
     * @param array $supnrpecfgInfo    supplemental nrpe config information
     * @param mixed $action            action that was being requested
     * @param array $oldsupnrpecfgInfo old supplemental nrpe config information
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, array $supnrpecfgInfo,
        $action, array $oldsupnrpecfgInfo = array()
    ) {
        parent::__construct($deployment);
        $this->supnrpecfg = $supnrpecfgInfo;
        $this->action = $action;
        $this->revision = $revision;
        if (($action == 'modify') || ($action == 'delete')) {
            $this->oldsupnrpecfg = $oldsupnrpecfgInfo;
        }
    }

}

class SupNRPECfgDataRenderer implements LoggerRendererObject
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
        $supnrpecfgInfo = array();
        foreach ($testData->supnrpecfg as $key => $value) {
            array_push($supnrpecfgInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=nrpecfg action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " sup_nrpe_cfg_info=[".implode(", ", $supnrpecfgInfo)."]";
        if (($testData->action == 'modify') || ($testData->action == 'delete')) {
            $oldsupnrpecfgInfo = array();
            foreach ($testData->oldsupnrpecfg as $key => $value) {
                array_push($oldsupnrpecfgInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_sup_nrpe_cfg_info=[".implode(", ", $oldsupnrpecfgInfo)."]";
        }
        return $msg;
    }

}

