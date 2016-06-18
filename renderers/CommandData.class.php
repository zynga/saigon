<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CommandData extends RenderData
{

    public $command;
    public $cmdInfo;
    public $action;
    public $revision;
    public $oldCmdInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment     deployment we are making the change too
     * @param mixed $revision       revision we are making the change too
     * @param mixed $command        command we are modifying
     * @param array $commandInfo    configuration information we are inputting
     * @param mixed $action         action that was being requested
     * @param array $oldCommandInfo old configuration information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $command, array $commandInfo,
        $action, array $oldCommandInfo = array()
    ) {
        parent::__construct($deployment);
        $this->command = $command;
        $this->cmdInfo = $commandInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldCmdInfo = $oldCommandInfo;
        }
    }

}

class CommandDataRenderer implements LoggerRendererObject
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
        $cmdInfo = array();
        foreach ($testData->cmdInfo as $key => $value) {
            if ($key == 'command_line') {
                $value = base64_decode($value);
            }
            elseif (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($cmdInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=command action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " command_info=[".implode(", ", $cmdInfo)."]";
        if ($testData->action == 'modify') {
            $oldCmdInfo = array();
            foreach ($testData->oldCmdInfo as $key => $value) {
                if ($key == 'command_line') {
                    $value = base64_decode($value);
                }
                elseif (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldCmdInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_command_info=[".implode(", ", $oldCmdInfo)."]";
        }
        return $msg;
    }

}

