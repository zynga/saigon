<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class HostTempController extends Controller {

    private function fetchHostTemplateInfo($deployment, $action, $modrevision) {
        $keys = array(
            'checkcmd', 'initstate', 'maxchkatts', 'chkinterval', 'chkretryinterval', 'activechk',
            'passivechk', 'chkperiod', 'ppdata', 'retstatusinfo', 'retnstatusinfo', 'contacts',
            'contactgrps', 'notifenabled', 'notifinterval', 'notifperiod', 'notifopts', 'notesurl',
            'ehcmd', 'ehenabled'
        );
        $hostInfo = array();
        $hostInfo['name'] = $this->getParam('hostName');
        $hostInfo['alias'] = $this->getParam('hostAlias');
        $hostInfo['use'] = $this->getParam('usetemplate');
        foreach ($keys as $key) {
            $value = $this->getParam($key);
            if ($value === false) continue;
            if ($value == 'on') $value = 1;
            if ($value == 'off') $value = 0;
            switch ($key) {
                case 'checkcmd':
                    $hostInfo['check_command'] = $value; break;
                case 'initstate':
                    $hostInfo['initial_state'] = $value; break;
                case 'maxchkatts':
                    $hostInfo['max_check_attempts'] = $value; break;
                case 'chkinterval':
                    $hostInfo['check_interval'] = $value; break;
                case 'chkretryinterval':
                    $hostInfo['retry_interval'] = $value; break;
                case 'activechk':
                    $hostInfo['active_checks_enabled'] = $value; break;
                case 'passivechk':
                    $hostInfo['passive_checks_enabled'] = $value; break;
                case 'chkperiod':
                    $hostInfo['check_period'] = $value; break;
                case 'ppdata':
                    $hostInfo['process_perf_data'] = $value; break;
                case 'retstatusinfo':
                    $hostInfo['retain_status_information'] = $value; break;
                case 'retnstatusinfo':
                    $hostInfo['retain_nonstatus_information'] = $value; break;
                case 'contacts':
                    $hostInfo['contacts'] = $value; break;
                case 'contactgrps':
                    $hostInfo['contact_groups'] = $value; break;
                case 'notifenabled':
                    $hostInfo['notifications_enabled'] = $value; break;
                case 'notifinterval':
                    $hostInfo['notification_interval'] = $value; break;
                case 'notifperiod':
                    $hostInfo['notification_period'] = $value; break;
                case 'notifopts':
                    $hostInfo['notification_options'] = $value; break;
                case 'notesurl':
                    if ($value === false) break;
                    $hostInfo['notes_url'] = $value; break;
                case 'ehcmd':
                    $hostInfo['event_handler'] = $value; break;
                case 'ehenabled':
                    $hostInfo['event_handler_enabled'] = $value; break;
                default:
                    break;
            }
        }
        if (($hostInfo['name'] === false) || ($hostInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming host parameters, make sure all of your input fields are filled in';
            $viewData->hostInfo = $hostInfo;
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('host_template_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $hostInfo['name'], $forbidden)) 
                || (preg_match_all('/[^a-zA-Z0-9_-\s]/s', $hostInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use host template name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->hostInfo = $hostInfo;
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('host_template_action_stage', $viewData);
        }
        $hostInfo['register'] = 0;
        return $hostInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('host_template_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('host_template_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostName = $this->getParam('hostName');
        $hostInfo = $this->fetchHostTemplateInfo($deployment, 'add_write', $modrevision);
        if (RevDeploy::existsDeploymentHostTemplate($deployment, $hostName, $modrevision) === true) {
            $viewData->error = 'Host template information exists for '.$hostName.' in '.$deployment.' Deployment';
            $viewData->action = 'add_write';
            $viewData->hosttemplate = $hostName;
            $viewData->deployment = $deployment;
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->hostInfo = $hostInfo;
            $this->sendResponse('host_template_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentHostTemplate($deployment, $hostName, $hostInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to write host information for '.$hostName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->host = $hostName;
        $this->sendResponse('host_template_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $hostTemplate = $this->getParam('hosttemp');
        if ($hostTemplate === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to detect host template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->hostInfo = RevDeploy::getDeploymentHostTemplate($deployment, $hostTemplate, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->hosttemplate = $hostTemplate;
        $viewData->action = 'modify_write';
        $viewData->modifyFlag = true;
        $this->sendResponse('host_template_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostName = $this->getParam('hostName');
        $hostInfo = $this->fetchHostTemplateInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentHostTemplate($deployment, $hostName, $hostInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to write host template information for '.$hostName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->host = $hostName;
        $this->sendResponse('host_template_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $hostTemplate = $this->getParam('hosttemp');
        if ($hostTemplate === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to detect host template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hostInfo = RevDeploy::getDeploymentHostTemplate($deployment, $hostTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->hosttemplate = $hostTemplate;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('host_template_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $hostTemplate = $this->getParam('hosttemp');
        if ($hostTemplate === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to detect host template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentHostTemplate($deployment, $hostTemplate, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->host = $hostTemplate;
        $this->sendResponse('host_template_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $hostTemplate = $this->getParam('hosttemp');
        if ($hostTemplate === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to detect host template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->hostInfo = RevDeploy::getDeploymentHostTemplate($deployment, $hostTemplate, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->hosttemplate = $hostTemplate;
        $viewData->action = 'copy_write';
        $this->sendResponse('host_template_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $hostTemplate = $this->getParam('hosttemp');
        if ($hostTemplate === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to detect host template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->hostInfo = RevDeploy::getCommonMergedDeploymentHostTemplate($deployment, $hostTemplate, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->hosttemplate = $hostTemplate;
        $viewData->action = 'copy_write';
        $this->sendResponse('host_template_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_template_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostName = $this->getParam('hostName');
        $hostInfo = $this->fetchHostTemplateInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentHostTemplate($deployment, $hostName, $modrevision) === true) {
            $viewData->error = 'Host template information exists for '.$hostName.' into '.$deployment.' Deployment';
            $viewData->action = 'copy_write';
            $viewData->hosttemplate = $hostName;
            $viewData->deployment = $deployment;
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            $viewData->hostchkcmds = RevDeploy::getCommonMergedDeploymentHostCheckCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->cmds = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision);
            $viewData->hostInfo = $hostInfo;
            $this->sendResponse('host_template_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentHostTemplate($deployment, $hostName, $hostInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('host_template_error');
            $viewData->error = 'Unable to write host tempalte information for '.$hostName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->host = $hostName;
        $this->sendResponse('host_template_write', $viewData);
    }
}
