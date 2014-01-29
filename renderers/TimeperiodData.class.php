<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class TimeperiodData extends RenderData
{

    public $timeperiod;
    public $timeperiodInfo;
    public $timeperiodData;
    public $action;
    public $revision;
    public $oldTimeperiodInfo;
    public $oldTimeperiodData;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment        deployment we are making the change too
     * @param mixed $revision          revision we are making the change too
     * @param mixed $timeperiod        timeperiod we are referencing
     * @param array $timeperiodInfo    timeperiod meta information
     * @param array $timeperiodData    timeperiod data
     * @param mixed $action            action that was being requested
     * @param array $oldTimeperiodInfo old timeperiod meta information
     * @param array $oldTimeperiodData old timeperiod data
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $timeperiod, array $timeperiodInfo,
        array $timeperiodData, $action,
        array $oldTimeperiodInfo = array(), array $oldTimeperiodData = array()
    ) {
        parent::__construct($deployment);
        $this->timeperiod = $timeperiod;
        $this->timeperiodInfo = $timeperiodInfo;
        $this->timeperiodData = $timeperiodData;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldTimeperiodInfo = $oldTimeperiodInfo;
            $this->oldTimeperiodData = $oldTimeperiodData;
        }
    }

}

class TimeperiodDataRenderer implements LoggerRendererObject
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
        $timeperiodInfo = array(); $timeperiodData = array();
        foreach ($testData->timeperiodInfo as $key => $value) {
            array_push($timeperiodInfo, "\"$key\" => \"$value\"");
        }
        foreach ($testData->timeperiodData as $md5Key => $tmpArray) {
            array_push($timeperiodData, '"'.$tmpArray['directive'].'" => "'.$tmpArray['range'].'"');
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=timeperiod action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " timeperiod={$testData->timeperiod}";
        $msg .= " timeperiod_info=[".implode(", ", $timeperiodInfo)."]";
        $msg .= " timeperiod_data=[".implode(", ", $timeperiodData)."]";
        if ($testData->action == 'modify') {
            $oldTimeperiodInfo = array(); $oldTimeperiodData = array();
            foreach ($testData->oldTimeperiodInfo as $key => $value) {
                array_push($oldTimeperiodInfo, "\"$key\" => \"$value\"");
            }
            foreach ($testData->oldTimeperiodData as $md5Key => $tmpArray) {
                array_push($oldTimeperiodData, '"'.$tmpArray['directive'].'" => "'.$tmpArray['range'].'"');
            }
            $msg .= " old_timeperiod_info=[".implode(", ", $oldTimeperiodInfo)."]";
            $msg .= " old_timeperiod_data=[".implode(", ", $oldTimeperiodData)."]";
        }
        return $msg;
    }

}

