<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SvcController extends Controller {

    private function fetchSvcInfo($deployment, $action, $modrevision) {
        $keys = array('checkcmd', 'initstate', 'maxchkatts', 'chkinterval', 'chkretryinterval', 'activechk', 'passivechk', 'chkperiod', 'ppdata', 'retstatusinfo', 'retnstatusinfo', 'contacts', 'contactgrps', 'notifenabled', 'notifinterval', 'notifperiod', 'notifopts', 'svcgrp', 'checkfreshness', 'chkfreshinterval', 'notesurl', 'actionurl', 'ehcmd', 'ehenabled');
        $svcInfo = array();
        $svcInfo['name'] = $this->getParam('svcName');
        $svcInfo['service_description'] = $this->getParam('svcDesc');
        $svcInfo['use'] = $this->getParam('usetemplate');
        foreach ($keys as $key) {
            $value = $this->getParam($key);
            if ($value === false) continue;
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
                    $svcInfo['contacts'] = $value; break;
                case 'contactgrps':
                    $svcInfo['contact_groups'] = $value; break;
                case 'notifenabled':
                    $svcInfo['notifications_enabled'] = $value; break;
                case 'notifinterval':
                    $svcInfo['notification_interval'] = $value; break;
                case 'notifperiod':
                    $svcInfo['notification_period'] = $value; break;
                case 'notifopts':
                    $svcInfo['notification_options'] = $value; break;
                case 'svcgrp':
                    $svcInfo['servicegroups'] = $value; break;
                case 'checkfreshness':
                    $svcInfo['check_freshness'] = $value; break;
                case 'chkfreshinterval':
                    $svcInfo['freshness_threshold'] = $value; break;
                case 'notesurl':
                    $svcInfo['notes_url'] = $value; break;
                case 'actionurl':
                    $svcInfo['action_url'] = $value; break;
                case 'ehcmd':
                    $svcInfo['event_handler'] = $value; break;
                case 'ehenabled':
                    $svcInfo['event_handler_enabled'] = $value; break;
                default:
                    break;
            }
        }
        for ($i=1; $i<=8; $i++) {
            $key = 'carg'.$i;
            $value = $this->getParam($key);
            if ($value === false) continue;
            $svcInfo[$key] = $value;
        }
        if (($svcInfo['name'] === false) || ($svcInfo['service_description'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming service parameters, make sure all of your input fields are filled in';
            $viewData->svcInfo = $svcInfo;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('svc_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $svcInfo['name'], $forbidden)) 
                || (preg_match_all('/[^a-zA-Z0-9_-\s\/]/s', $svcInfo['service_description'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use service name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->svcInfo = $svcInfo;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('svc_action_stage', $viewData);
        }
        $svcInfo['parallelize_check'] = 1;
        $svcInfo['is_volatile'] = 0;
        return $svcInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('svc_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('svc_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcName = $this->getParam('svcName');
        $svcInfo = $this->fetchSvcInfo($deployment, 'add_write', $modrevision);
        if (RevDeploy::existsDeploymentSvc($deployment, $svcName, $modrevision) === true) {
            $viewData->error = 'Service template information exists for '.$svcName.' in '.$deployment.' Deployment';
            $viewData->action = 'add_write';
            $viewData->deployment = $deployment;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcInfo = $svcInfo;
            $this->sendResponse('svc_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvc($deployment, $svcName, $svcInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to write service information for '.$svcName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcName;
        $this->sendResponse('svc_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $svc = $this->getParam('svc');
        if ($svc === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->svcInfo = RevDeploy::getDeploymentSvc($deployment, $svc, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $viewData->modifyFlag = true;
        $this->sendResponse('svc_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcName = $this->getParam('svcName');
        $svcInfo = $this->fetchSvcInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentSvc($deployment, $svcName, $svcInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to write service template information for '.$svcName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcName;
        $this->sendResponse('svc_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $svc = $this->getParam('svc');
        if ($svc === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcInfo = RevDeploy::getDeploymentSvc($deployment, $svc, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('svc_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $svc = $this->getParam('svc');
        if ($svc === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentSvc($deployment, $svc, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svc = $svc;
        $this->sendResponse('svc_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $svc = $this->getParam('svc');
        if ($svc === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->svcInfo = RevDeploy::getDeploymentSvc($deployment, $svc, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $svc = $this->getParam('svc');
        if ($svc === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to detect service template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->svcInfo = RevDeploy::getCommonMergedDeploymentSvc($deployment, $svc, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcName = $this->getParam('svcName');
        $svcInfo = $this->fetchSvcInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentSvc($deployment, $svcName, $modrevision) === true) {
            $viewData->error = 'Service template information exists for '.$svcName.' into '.$deployment.' Deployment';
            $viewData->action = 'copy_write';
            $viewData->deployment = $deployment;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->svcchkcmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcInfo = $svcInfo;
            $this->sendResponse('svc_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvc($deployment, $svcName, $svcInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_error');
            $viewData->error = 'Unable to write service tempalte information for '.$svcName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svc = $svcName;
        $this->sendResponse('svc_write', $viewData);
    }
}
