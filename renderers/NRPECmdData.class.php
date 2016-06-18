<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPECmdData extends RenderData
{

    public $nrpecmd;
    public $nrpecmdInfo;
    public $action;
    public $revision;
    public $oldNRPECmdInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment     deployment we are making the change too
     * @param mixed $revision       revision we are making the change too
     * @param mixed $nrpecmd        nrpe command we are referencing
     * @param array $nrpecmdInfo    nrpe command information
     * @param mixed $action         action that was being requested
     * @param array $oldNRPECmdInfo old nrpe command information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $nrpecmd, array $nrpecmdInfo,
        $action, array $oldNRPECmdInfo = array()
    ) {
        parent::__construct($deployment);
        $this->nrpecmd = $nrpecmd;
        $this->nrpecmdInfo = $nrpecmdInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldNRPECmdInfo = $oldNRPECmdInfo;
        }
    }

}

class NRPECmdDataRenderer implements LoggerRendererObject
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
        $nrpecmdInfo = array();
        foreach ($testData->nrpecmdInfo as $key => $value) {
            if ($key == 'cmd_line') {
                $value = base64_decode($value);
            }
            elseif (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($nrpecmdInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=nrpecmd action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " nrpe_cmd_info=[".implode(", ", $nrpecmdInfo)."]";
        if ($testData->action == 'modify') {
            $oldNRPECmdInfo = array();
            foreach ($testData->oldNRPECmdInfo as $key => $value) {
                if ($key == 'cmd_line') {
                    $value = base64_decode($value);
                }
                elseif (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldNRPECmdInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_nrpe_cmd_info=[".implode(", ", $oldNRPECmdInfo)."]";
        }
        return $msg;
    }

}

