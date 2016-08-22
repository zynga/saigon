<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ContactTemplateData extends RenderData
{

    public $contactTemplate;
    public $contactInfo;
    public $action;
    public $revision;
    public $oldContactInfo;

    /**
     * __construct - object initialization routine
     *
     * @param mixed $deployment      deployment we are making the change too
     * @param mixed $revision        revision we are making the change too
     * @param mixed $contactTemplate contact template we are modifying
     * @param array $contactInfo     contact template information we are inputting
     * @param mixed $action          action that was being requested
     * @param array $oldContactInfo  old contact template information being replaced
     *
     * @access public
     * @return void
     */
    public function __construct(
        $deployment, $revision, $contactTemplate, array $contactInfo,
        $action, array $oldContactInfo = array()
    ) {
        parent::__construct($deployment);
        $this->contactTemplate = $contactTemplate;
        $this->contactInfo = $contactInfo;
        $this->action = $action;
        $this->revision = $revision;
        if ($action == 'modify') {
            $this->oldContactInfo = $oldContactInfo;
        }
    }

}

class ContactTemplateDataRenderer implements LoggerRendererObject
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
        $contactInfo = array();
        foreach ($testData->contactInfo as $key => $value) {
            if (is_array($value)) {
                $value = implode(",", $value);
            }
            array_push($contactInfo, "\"$key\" => \"$value\"");
        }
        $msg = "{$testData->user} {$testData->ip}";
        $msg .= " revision={$testData->revision}";
        $msg .= " controller=contacttemp action={$testData->action}";
        $msg .= " deployment={$testData->deployment}";
        $msg .= " contact_template_info=[".implode(", ", $contactInfo)."]";
        if ($testData->action == 'modify') {
            $oldContactInfo = array();
            foreach ($testData->oldContactInfo as $key => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                array_push($oldContactInfo, "\"$key\" => \"$value\"");
            }
            $msg .= " old_contact_template_info=[".implode(", ", $oldContactInfo)."]";
        }
        return $msg;
    }

}

