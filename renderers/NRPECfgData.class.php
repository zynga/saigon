<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPECfgData extends RenderData
{

    public $nrpecfgInfo;
    public $action;
    public $revision;
    public $oldNRPECfgInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment     deployment we are making the change too
     * @param mixed $revision       revision we are making the change too
     * @param array $nrpecfgInfo    nrpe configuration information
     * @param mixed $action         action that was being requested
     * @param array $oldNRPECfgInfo old nrpe config information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, array $nrpecfgInfo,
        $action, array $oldNRPECfgInfo = array()
    ) {
        parent::__construct($deployment);
        $this->nrpecfgInfo = $nrpecfgInfo;
        $this->action = $action;
        $this->revision = $revision;
        if (($action == 'modify') || ($action == 'delete')) {
            $this->oldNRPECfgInfo = $oldNRPECfgInfo;
        }
    }

}

class NRPECfgDataRenderer implements LoggerRendererObject
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
        $nrpecfgInfo = array();
        foreach ($testData->nrpecfgInfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($nrpecfgInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=nrpecfg action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " nrpe_cfg_info=[".implode(", ", $nrpecfgInfo)."]";
        if (($testData->action == 'modify') || ($testData->action == 'delete')) {
            $oldNRPECfgInfo = array();
            foreach ($testData->oldNRPECfgInfo as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldNRPECfgInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_nrpe_cfg_info=[".implode(", ", $oldNRPECfgInfo)."]";
        }
        return $msg;
    }

}

