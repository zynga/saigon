<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ContactTempController extends Controller {

    private function fetchContactTemplateInfo($deployment, $action, $modrevision) {
        $keys = array('hostnotifenabled','hostnotifperiod','hostnotifopts','hostnotifcmd','svcnotifenabled','svcnotifperiod','svcnotifopts','svcnotifcmd');
        $contactInfo = array();
        $contactInfo['name'] = $this->getParam('contactName');
        $contactInfo['alias'] = $this->getParam('contactAlias');
        $contactInfo['use'] = $this->getParam('usetemplate');
        foreach ($keys as $key) {
            $value = $this->getParam($key);
            if ($value === false) continue;
            if ($value == 'on') $value = 1;
            if ($value == 'off') $value = 0;
            switch ($key) {
                case 'hostnotifenabled':
                    $contactInfo['host_notifications_enabled'] = $value; break;
                case 'hostnotifperiod':
                    $contactInfo['host_notification_period'] = $value; break;
                case 'hostnotifopts':
                    $contactInfo['host_notification_options'] = $value; break;
                case 'hostnotifcmd':
                    $contactInfo['host_notification_commands'] = $value; break;
                case 'svcnotifenabled':
                    $contactInfo['service_notifications_enabled'] = $value; break;
                case 'svcnotifperiod':
                    $contactInfo['service_notification_period'] = $value; break;
                case 'svcnotifopts':
                    $contactInfo['service_notification_options'] = $value; break;
                case 'svcnotifcmd':
                    $contactInfo['service_notification_commands'] = $value; break;
                default:
                    break;
            }
        }
        if (($contactInfo['name'] === false) || ($contactInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming contact parameters, make sure all of your input fields are filled in';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('contact_template_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $contactInfo['name'], $forbidden)) 
                || (preg_match_all('/[^a-zA-Z0-9_-\s]/s', $contactInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use contact template name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('contact_template_action_stage', $viewData);
        } else if ($contactInfo['use'] === false) {
            /* We need to verify all incoming params because there is no template specified */
            foreach ($contactInfo as $key => $value) {
                if (($key == 'alias') || ($key == 'name') || ($key == 'use')) continue;
                if ($value === false) {
                    $viewData = new ViewData();
                    $viewData->error = 'If not using a template, please ensure all fields are filled in';
                    $viewData->contactInfo = $contactInfo;
                    $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
                    $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
                    $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
                    $viewData->action = $action;
                    $viewData->deployment = $deployment;
                    $this->sendResponse('contact_template_action_stage', $viewData);
                }
            }
        }
        $contactInfo['contact_name'] = $contactInfo['name'];
        $contactInfo['register'] = 0;
        return $contactInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $deployContacts = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->contacts = $deployContacts;
        $this->sendResponse('contact_template_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('contact_template_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactName = $this->getParam('contactName');
        $contactInfo = $this->fetchContactTemplateInfo($deployment, 'add_write', $modrevision);
        if (RevDeploy::existsDeploymentContactTemplate($deployment, $contactName, $modrevision) === true) {
            $viewData->error = 'Contact template information exists for '.$contactName.' in '.$deployment.' Deployment';
            $viewData->action = 'add_write';
            $viewData->contacttemplate = $contactName;
            $viewData->deployment = $deployment;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contactInfo = $contactInfo;
            $this->sendResponse('contact_template_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentContactTemplate($deployment, $contactName, $contactInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to write contact information for '.$contactName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->contact = $contactName;
        $this->sendResponse('contact_template_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $contactTemplate = $this->getParam('contacttemp');
        if ($contactTemplate === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to detect contact template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contactInfo = RevDeploy::getDeploymentContactTemplate($deployment, $contactTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->contacttemplate = $contactTemplate;
        $viewData->action = 'modify_write';
        $viewData->modifyFlag = true;
        $this->sendResponse('contact_template_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactName = $this->getParam('contactName');
        $contactInfo = $this->fetchContactTemplateInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentContactTemplate($deployment, $contactName, $contactInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to write contact template information for '.$contactName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->contact = $contactName;
        $this->sendResponse('contact_template_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $contactTemplate = $this->getParam('contacttemp');
        if ($contactTemplate === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to detect contact template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contactInfo = RevDeploy::getDeploymentContactTemplate($deployment, $contactTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->contacttemplate = $contactTemplate;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('contact_template_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $contactTemplate = $this->getParam('contacttemp');
        if ($contactTemplate === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to detect contact template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentContactTemplate($deployment, $contactTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->contact = $contactTemplate;
        $this->sendResponse('contact_template_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $contactTemplate = $this->getParam('contacttemp');
        if ($contactTemplate === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to detect contact template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contactInfo = RevDeploy::getDeploymentContactTemplate($deployment, $contactTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->contacttemplate = $contactTemplate;
        $viewData->action = 'copy_write';
        $this->sendResponse('contact_template_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $contactTemplate = $this->getParam('contacttemp');
        if ($contactTemplate === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to detect contact template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contactInfo = RevDeploy::getCommonMergedDeploymentContactTemplate($deployment, $contactTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->contacttemplate = $contactTemplate;
        $viewData->action = 'copy_write';
        $this->sendResponse('contact_template_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactName = $this->getParam('contactName');
        $contactInfo = $this->fetchContactTemplateInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentContactTemplate($deployment, $contactName, $modrevision) === true) {
            $viewData->error = 'Contact template information exists for '.$contactName.' into '.$deployment.' Deployment';
            $viewData->action = 'copy_write';
            $viewData->contacttemplate = $contactName;
            $viewData->deployment = $deployment;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contactInfo = $contactInfo;
            $this->sendResponse('contact_template_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentContactTemplate($deployment, $contactName, $contactInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_template_error');
            $viewData->error = 'Unable to write contact tempalte information for '.$contactName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $contactName;
        $this->sendResponse('contact_template_write', $viewData);
    }
}
