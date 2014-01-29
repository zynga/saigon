<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ContactGroupData extends RenderData
{

    public $contactGroup;
    public $contactGroupInfo;
    public $action;
    public $revision;
    public $oldContactGroupInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment          deployment we are making the change too
     * @param mixed $revision            revision we are making the change too
     * @param mixed $contactGroup        contact group we are modifying
     * @param array $contactGroupInfo    contact group information we are inputting
     * @param mixed $action              action that was being requested
     * @param array $oldContactGroupInfo old contact group information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $contactGroup, array $contactGroupInfo,
        $action, array $oldContactGroupInfo = array()
    ) {
        parent::__construct($deployment);
        $this->contactGroup = $contactGroup;
        $this->contactGroupInfo = $contactGroupInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldContactGroupInfo = $oldContactGroupInfo;
        }
    }

}

class ContactGroupDataRenderer implements LoggerRendererObject
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
        $contactGroupInfo = array();
        foreach ($testData->contactGroupInfo as $key => $value) {
            array_push($contactGroupInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=contactgrp action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " contact_group_info=[".implode(", ", $contactGroupInfo)."]";
        if ($testData->action == 'modify') {
            $oldContactGroupInfo = array();
            foreach ($testData->oldContactGroupInfo as $key => $value) {
                array_push($oldContactGroupInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_contact_group_info=[".implode(", ", $oldContactGroupInfo)."]";
        }
        return $msg;
    }

}

