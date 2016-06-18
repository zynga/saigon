<?php
//
// Copyright (c) 2014, Pinterest
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ClusterCmdsController extends Controller {

    private function fetchClusterCmdsInfo($deployment, $action, $modrevision) {
        $cmdInfo = array();
        $cmdInfo['name'] = $this->getParam('cmdname');
        $cmdInfo['server'] = $this->getParam('cmdserver');
        $cmdInfo['service_description'] = $this->getParam('cmddesc');
        $cmdInfo['query'] = base64_encode($this->getParam('cmdquery'));
        $cmdInfo['warnmin'] = $this->getParam('cmdwarnmin');
        $cmdInfo['warnmax'] = $this->getParam('cmdwarnmax');
        $cmdInfo['crit'] = $this->getParam('cmdcrit');
        $cmdInfo['type'] = $this->getParam('cmdtype');
        $cctype = $this->getParam('cctype');
        if ($cctype == false) {
            $viewData = new ViewData();
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect cluster command type specified';
            $this->sendError('generic_error', $viewData);
        }
        elseif ($cctype == 'basic') {
            $cmdInfo['cctype'] = $cctype;
            $cmdInfo['warnmode'] = $this->getParam('warnmode');
            $cmdInfo['critmode'] = $this->getParam('critmode');
        }
        elseif ($cctype == 'quorum') {
            $cmdInfo['cctype'] = $cctype;
            $cmdInfo['quorum'] = $this->getParam('cmdquorum');
        }
        else {
            $viewData = new ViewData();
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to use cluster command type specified [basic/quorum]';
            $this->sendError('generic_error', $viewData);
        }
        if ($cmdInfo['server'] == false) {
            unset($cmdInfo['server']);
        }
        $cmdInfo['use'] = $this->getParam('usetemplate');
        $keys = array(
            'checkcmd', 'initstate', 'maxchkatts', 'chkinterval', 'chkretryinterval',
            'activechk', 'passivechk', 'chkperiod', 'retstatusinfo', 'retnstatusinfo',
            'contacts', 'contactgrps', 'notifenabled', 'notifinterval', 'notifperiod',
            'notifopts', 'svcgrp', 'checkfreshness', 'chkfreshinterval', 'notesurl',
            'actionurl', 
        );
        foreach ($keys as $key) {
            $value = $this->getParam($key);
            if ($value === false) continue;
            if ($value == 'on') $value = 1;
            if ($value == 'off') $value = 0;
            switch ($key) {
                case 'checkcmd':
                    $cmdInfo['check_command'] = $value; break;
                case 'initstate':
                    $cmdInfo['initial_state'] = $value; break;
                case 'maxchkatts':
                    $cmdInfo['max_check_attempts'] = $value; break;
                case 'chkinterval':
                    $cmdInfo['check_interval'] = $value; break;
                case 'chkretryinterval':
                    $cmdInfo['retry_interval'] = $value; break;
                case 'activechk':
                    $cmdInfo['active_checks_enabled'] = $value; break;
                case 'chkperiod':
                    $cmdInfo['check_period'] = $value; break;
                case 'retstatusinfo':
                    $cmdInfo['retain_status_information'] = $value; break;
                case 'retnstatusinfo':
                    $cmdInfo['retain_nonstatus_information'] = $value; break;
                case 'contacts':
                    $cmdInfo['contacts'] = $value; break;
                case 'contactgrps':
                    $cmdInfo['contact_groups'] = $value; break;
                case 'notifenabled':
                    $cmdInfo['notifications_enabled'] = $value; break;
                case 'notifinterval':
                    $cmdInfo['notification_interval'] = $value; break;
                case 'notifperiod':
                    $cmdInfo['notification_period'] = $value; break;
                case 'notifopts':
                    $cmdInfo['notification_options'] = $value; break;
                case 'svcgrp':
                    $cmdInfo['servicegroups'] = $value; break;
                case 'checkfreshness':
                    $cmdInfo['check_freshness'] = $value; break;
                case 'chkfreshinterval':
                    $cmdInfo['freshness_threshold'] = $value; break;
                case 'notesurl':
                    $cmdInfo['notes_url'] = $value; break;
                case 'actionurl':
                    $cmdInfo['action_url'] = $value; break;
            default:
                break;
            }
        }

        if (
            ($cmdInfo['name'] == false) ||
            ($cmdInfo['service_description'] == false) ||
            ($cmdInfo['query'] == false) ||
            ($cmdInfo['warnmin'] == false) ||
            ($cmdInfo['warnmax'] == false) ||
            ($cmdInfo['crit'] == false)
        ) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming command parameters, make sure all of your input fields are filled in';
            $viewData->clustercmdsInfo = $cmdInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = $action;
            $this->sendResponse('cluster_cmds_action_stage', $viewData);
        } else if (preg_match_all('/[^a-zA-Z0-9_-]/s', $cmdInfo['name'], $forbidden)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use command name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->clustercmdsInfo = $cmdInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $this->sendResponse('cluster_cmds_action_stage', $viewData);
        }
        return $cmdInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->clustercmds = RevDeploy::getDeploymentClusterCmdswInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('cluster_cmds_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $viewData->cctype = 'basic';
        $this->sendResponse('cluster_cmds_action_stage', $viewData);
    }

    public function add_quorum_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $viewData->cctype = 'quorum';
        $this->sendResponse('cluster_cmds_action_quorum_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = $this->fetchClusterCmdsInfo($deployment, 'add_write', $modrevision);
        $cmdName = $cmdInfo['name'];
        if (RevDeploy::existsDeploymentClusterCmd($deployment, $cmdName, $modrevision) === true) {
            $viewData->error = 'Command information for '.$cmdName.' already exists for '.$deployment.' Deployment';
            $viewData->clustercmdsInfo = $cmdInfo;
            $viewData->action = 'add_write';
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $this->sendResponse('cluster_cmds_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentClusterCmd($deployment, $cmdName, $cmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to write command information for '.$cmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $cmdName;
        $this->sendResponse('cluster_cmds_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $clustercmd = $this->getParam('cmdname');
        if ($clustercmd === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = RevDeploy::getDeploymentClusterCmd($deployment, $clustercmd, $modrevision);
        if (empty($cmdInfo)) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to fetch command '.$clustercmd.' for deployment '.$deployment.' from datastore';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $viewData->cctype = 'basic';
        $viewData->clustercmdsInfo = $cmdInfo;
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $this->sendResponse('cluster_cmds_action_stage', $viewData);
    }

    public function modify_quorum_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $clustercmd = $this->getParam('cmdname');
        if ($clustercmd === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = RevDeploy::getDeploymentClusterCmd($deployment, $clustercmd, $modrevision);
        if (empty($cmdInfo)) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to fetch command '.$clustercmd.' for deployment '.$deployment.' from datastore';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $viewData->cctype = 'quorum';
        $viewData->clustercmdsInfo = $cmdInfo;
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $this->sendResponse('cluster_cmds_action_quorum_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = $this->fetchClusterCmdsInfo($deployment, 'modify_write', $modrevision);
        $cmdName = $cmdInfo['name'];
        if (RevDeploy::modifyDeploymentClusterCmd($deployment, $cmdName, $cmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to write command information for '.$cmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $cmdName;
        $this->sendResponse('cluster_cmds_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $viewData->deployment = $deployment;
        $clustercmd = $this->getParam('cmdname');
        if ($clustercmd === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $clustercmd;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = RevDeploy::getDeploymentClusterCmd($deployment, $clustercmd, $modrevision);
        if (empty($cmdInfo)) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to fetch command information for '.$clustercmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->clustercmdsInfo = $cmdInfo;
        $viewData->action = 'delete';
        $this->sendResponse('cluster_cmds_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $clustercmd = $this->getParam('cmdname');
        if ($clustercmd === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $clustercmd;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentClusterCmd($deployment, $clustercmd, $modrevision);
        $this->sendResponse('cluster_cmds_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $viewData->deployment = $deployment;
        $clustercmd = $this->getParam('cmdname');
        if ($clustercmd === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = RevDeploy::getDeploymentClusterCmd($deployment, $clustercmd, $modrevision);
        if (empty($cmdInfo)) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to fetch command information for '.$clustercmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->clustercmdsInfo = $cmdInfo;
        $viewData->action = 'copy_write';
        $viewData->cctype = 'basic';
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $this->sendResponse('cluster_cmds_action_stage', $viewData);
    }

    public function copy_quorum_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $viewData->deployment = $deployment;
        $clustercmd = $this->getParam('cmdname');
        if ($clustercmd === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = RevDeploy::getDeploymentClusterCmd($deployment, $clustercmd, $modrevision);
        if (empty($cmdInfo)) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to fetch command information for '.$clustercmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->clustercmdsInfo = $cmdInfo;
        $viewData->action = 'copy_write';
        $viewData->cctype = 'quorum';
        $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $this->sendResponse('cluster_cmds_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cluster_cmds_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = $this->fetchClusterCmdsInfo($deployment, 'copy_write', $modrevision);
        $cmdName = $cmdInfo['name'];
        if (RevDeploy::existsDeploymentClusterCmd($deployment, $cmdName, $modrevision) === true) {
            $viewData->error = 'Command information for '.$cmdName.' already exists for '.$deployment.' Deployment';
            $viewData->clustercmdsInfo = $cmdInfo;
            $viewData->action = 'copy_write';
            $viewData->svctemplates = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $modrevision);
            $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $this->sendResponse('cluster_cmds_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentClusterCmd($deployment, $cmdName, $cmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('cluster_cmds_error');
            $viewData->error = 'Unable to write command information for '.$cmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $cmdName;
        $this->sendResponse('cluster_cmds_write', $viewData);
    }

    public function view_matches() {
        $viewData = new ViewData();
        $query = $this->getParam('query');
        $type = $this->getParam('type');
        $server = $this->getParam('server');
        if (($query === false) || ($type === false)) {
            $viewData->error = "Unable to detect Query or Type Parameter";
            $this->sendResponse('cluster_cmds_error', $viewData);
        }
        $url = CLUSTER_COMMANDS_URL . '?query=' . urlencode($query) . '&ui=' . $type[0];
        if ($server !== false) {
            $url .= "&server=" . $server;
        }
        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);
        if ($errno) {
            $viewData->error = "Unable to process request: $errno : $errstr";
            $this->sendResponse('cluster_cmds_error', $viewData);
        }
        $viewData->response = $response;
        $this->sendResponse('cluster_cmds_matches', $viewData);
    }

}
