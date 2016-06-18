<?php
//
// Copyright (c) 2014, Pinterest
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ClusterCmdsData extends RenderData
{

    public $clustercmd;
    public $clustercmdInfo;
    public $action;
    public $revision;
    public $oldClusterCmdInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment        deployment we are making the change too
     * @param mixed $revision          revision we are making the change too
     * @param mixed $clustercmd        cluster command we are referencing
     * @param array $clustercmdInfo    cluster command information
     * @param mixed $action            action that was being requested
     * @param array $oldClusterCmdInfo old cluster command information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $clustercmd, array $clustercmdInfo,
        $action, array $oldClusterCmdInfo = array()
    ) {
        parent::__construct($deployment);
        $this->clustercmd = $clustercmd;
        $this->clustercmdInfo = $clustercmdInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldClusterCmdInfo = $oldClusterCmdInfo;
        }
    }

}

class ClusterCmdsDataRenderer implements LoggerRendererObject
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
        $clustercmdInfo = array();
        foreach ($testData->clustercmdInfo as $key => $value) {
            if ($key == 'cmd_line') {
                $value = base64_decode($value);
            }
            elseif (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($clustercmdInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=clustercmds action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " cluster_cmd_info=[".implode(", ", $clustercmdInfo)."]";
        if ($testData->action == 'modify') {
            $oldClusterCmdInfo = array();
            foreach ($testData->oldClusterCmdInfo as $key => $value) {
                if ($key == 'cmd_line') {
                    $value = base64_decode($value);
                }
                elseif (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldClusterCmdInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_cluster_cmd_info=[".implode(", ", $oldClusterCmdInfo)."]";
        }
        return $msg;
    }

}

