<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SvcTempController extends Controller {

    private function fetchSvcTemplateInfo($deployment, $action, $modrevision) {
        $keys = array('checkcmd', 'initstate', 'maxchkatts', 'chkinterval', 'chkretryinterval', 'activechk', 'passivechk', 'chkperiod', 'ppdata', 'retstatusinfo', 'retnstatusinfo', 'contacts', 'contactgrps', 'notifenabled', 'notifinterval', 'notifperiod', 'notifopts', 'checkfreshness', 'chkfreshinterval', 'ehcmd', 'ehenabled', 'svcgrp');
        $svcInfo = array();
        $svcInfo['name'] = $this->getParam('svcName');
        $svcInfo['alias'] = $this->getParam('svcAlias');
        $svcInfo['use'] = $this->getParam('usetemplate');
        foreach ($keys as $key) {
            $value = $this->getParam($key);
            if ($value == 'on') $value = 1;
            if ($value == 'off') $value = 0;
            switch ($key) {
                case 'checkcmd':
                    $svcInfo['check_command'] = $value; break;
                case 'initstate':
                    $svcInfo['initial_state'] = $value; break;
                case 'maxchkatts':
                    $svcInfo['max_check_attempts'] = $value; break;
                case 'chkinterval':
                    $svcInfo['check_interval'] = $value; break;
                case 'chkretryinterval':
                    $svcInfo['retry_interval'] = $value; break;
                case 'activechk':
                    $svcInfo['active_checks_enabled'] = $value; break;
                case 'passivechk':
                    $svcInfo['passive_checks_enabled'] = $value; break;
                case 'chkperiod':
                    $svcInfo['check_period'] = $value; break;
                case 'ppdata':
                    $svcInfo['process_perf_data'] = $value; break;
                case 'retstatusinfo':
                    $svcInfo['retain_status_information'] = $value; break;
                case 'retnstatusinfo':
                    $svcInfo['retain_nonstatus_information'] = $value; break;
                case 'contacts':
                    if ($value === false) break;
                    $svcInfo['contacts'] = implode(",", $value); break;
                case 'contactgrps':
                    if ($value === false) break;
                    $svcInfo['contact_groups'] = implode(",", $value); break;
                case 'notifenabled':
                    $svcInfo['notifications_enabled'] = $value; break;
                case 'notifinterval':
                    $svcInfo['notification_interval'] = $value; break;
                case 'notifperiod':
                    $svcInfo['notification_period'] = $value; break;
                case 'notifopts':
                    if ($value === false) break;
                    $svcInfo['notification_options'] = implode(",", $value); break;
                case 'checkfreshness':
                    $svcInfo['check_freshness'] = $value; break;
                case 'chkfreshinterval':
                    $svcInfo['freshness_threshold'] = $value; break;
                case 'ehcmd':
                    if ($value === false) break;
                    $svcInfo['event_handler'] = $value; break;
                case 'ehenabled':
                    if ($value === false) break;
                    $svcInfo['event_handler_enabled'] = $value; break;
                case 'svcgrp':
                    $svcInfo['servicegroups'] = $value; break;
                default:
                    break;
            }
        }
        for ($i=1; $i<=32; $i++) {
            $key = 'carg'.$i;
            $value = $this->getParam($key);
            if ($value === false) continue;
            $svcInfo[$key] = $value;
        }
        if (($svcInfo['name'] === false) || ($svcInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming service parameters, make sure all of your input fields are filled in';
            $viewData->svcInfo = $svcInfo;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('svc_template_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $svcInfo['name'], $forbidden)) 
                || (preg_match_all('/[^a-zA-Z0-9_-\s]/s', $svcInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use service template name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->svcInfo = $svcInfo;
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('svc_template_action_stage', $viewData);
        }
        $svcInfo['parallelize_check'] = 1;
        $svcInfo['is_volatile'] = 0;
        $svcInfo['register'] = 0;
        return $svcInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('svc_template_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('svc_template_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcName = $this->getParam('svcName');
        $svcInfo = $this->fetchSvcTemplateInfo($deployment, 'add_write', $modrevision);
        if (RevDeploy::existsDeploymentSvcTemplate($deployment, $svcName, $modrevision) === true) {
            $viewData->error = 'Service template information exists for '.$svcName.' in '.$deployment.' Deployment';
            $viewData->action = 'add_write';
            $viewData->svctemplate = $svcName;
            $viewData->deployment = $deployment;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svcInfo = $svcInfo;
            $this->sendResponse('svc_template_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcTemplate($deployment, $svcName, $svcInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to write service information for '.$svcName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcName;
        $this->sendResponse('svc_template_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $svcTemplate = $this->getParam('svctemp');
        if ($svcTemplate === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->svcInfo = RevDeploy::getDeploymentSvcTemplate($deployment, $svcTemplate, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svctemplate = $svcTemplate;
        $viewData->action = 'modify_write';
        $viewData->modifyFlag = true;
        $this->sendResponse('svc_template_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcName = $this->getParam('svcName');
        $svcInfo = $this->fetchSvcTemplateInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentSvcTemplate($deployment, $svcName, $svcInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to write service template information for '.$svcName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcName;
        $this->sendResponse('svc_template_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $svcTemplate = $this->getParam('svctemp');
        if ($svcTemplate === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcInfo = RevDeploy::getDeploymentSvcTemplate($deployment, $svcTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svctemplate = $svcTemplate;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('svc_template_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $svcTemplate = $this->getParam('svctemp');
        if ($svcTemplate === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentSvcTemplate($deployment, $svcTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svc = $svcTemplate;
        $this->sendResponse('svc_template_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $svcTemplate = $this->getParam('svctemp');
        if ($svcTemplate === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->svcInfo = RevDeploy::getDeploymentSvcTemplate($deployment, $svcTemplate, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svctemplate = $svcTemplate;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_template_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $svcTemplate = $this->getParam('svctemp');
        if ($svcTemplate === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $commonRepo = RevDeploy::getDeploymentCommonRepo($deployment);
        $commonrevision = RevDeploy::getDeploymentRev($commonRepo);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->svcInfo = RevDeploy::getDeploymentSvcTemplate($commonRepo, $svcTemplate, $commonrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svctemplate = $svcTemplate;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_template_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_template_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcName = $this->getParam('svcName');
        $svcInfo = $this->fetchSvcTemplateInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentSvcTemplate($deployment, $svcName, $modrevision) === true) {
            $viewData->error = 'Service template information exists for '.$svcName.' into '.$deployment.' Deployment';
            $viewData->action = 'copy_write';
            $viewData->svctemplate = $svcName;
            $viewData->deployment = $deployment;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svcInfo = $svcInfo;
            $this->sendResponse('svc_template_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcTemplate($deployment, $svcName, $svcInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_template_error');
            $viewData->error = 'Unable to write service tempalte information for '.$svcName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svc = $svcName;
        $this->sendResponse('svc_template_write', $viewData);
    }
}
