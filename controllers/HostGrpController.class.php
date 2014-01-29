<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class HostGrpController extends Controller {

    private function fetchHostInfo($deployment, $action) {
        $hostGrpInfo = array();
        $hostGrpInfo['hostgroup_name'] = $this->getParam('hostName');
        $hostGrpInfo['alias'] = $this->getParam('hostAlias');
        if (($hostGrpInfo['hostgroup_name'] === false) || ($hostGrpInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming host group parameters, make sure all of your input fields are filled in';
            $viewData->hostGrpInfo = $hostGrpInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('host_group_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $hostGrpInfo['hostgroup_name'], $forbidden))
                || (preg_match_all('/[^a-zA-Z0-9_-\s]/s', $hostGrpInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use host group name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->hostGrpInfo = $hostGrpInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('host_group_action_stage', $viewData);
        }
        return $hostGrpInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('host_group_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('host_group_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostGrpInfo = $this->fetchHostInfo($deployment,'add_write');
        $hostGrpName = $this->getParam('hostName');
        if (RevDeploy::existsDeploymentHostGroup($deployment, $hostGrpName, $modrevision) === true) {
            $viewData->error = 'Host information exists for '.$hostGrpName.' in '.$deployment.' Deployment';
            $viewData->hostGrpInfo = $hostGrpInfo;
            $viewData->action = 'add_write';
            $this->sendResponse('host_group_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentHostGroup($deployment, $hostGrpName, $hostGrpInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to write host information for '.$hostGrpName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->host = $hostGrpName;
        $this->sendResponse('host_group_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $hostGrpName = $this->getParam('hostName');
        if ($hostGrpName === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to detect host specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostGrpInfo = RevDeploy::getDeploymentHostGroup($deployment, $hostGrpName, $modrevision);
        $viewData->hostGrpInfo = $hostGrpInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $this->sendResponse('host_group_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostGrpName = $this->getParam('hostName');
        $hostGrpInfo = $this->fetchHostInfo($deployment, 'modify_write');
        if (RevDeploy::modifyDeploymentHostGroup($deployment, $hostGrpName, $hostGrpInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to write host group information for '.$hostGrpName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->host = $hostGrpName;
        $this->sendResponse('host_group_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $hostGrpName = $this->getParam('hostName');
        if ($hostGrpName === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to detect host specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostGrpInfo = RevDeploy::getDeploymentHostGroup($deployment, $hostGrpName, $modrevision);
        $viewData->hostGrpInfo = $hostGrpInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('host_group_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $hostGrpName = $this->getParam('hostName');
        if ($hostGrpName === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to detect host specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentHostGroup($deployment, $hostGrpName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->host = $hostGrpName;
        $this->sendResponse('host_group_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $hostGrpName = $this->getParam('hostName');
        if ($hostGrpName === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to detect host specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hostGrpInfo = RevDeploy::getDeploymentHostGroup($deployment, $hostGrpName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('host_group_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $hostGrpName = $this->getParam('hostName');
        if ($hostGrpName === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to detect host specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $commonRepo = RevDeploy::getDeploymentCommonRepo($deployment);
        $commonrevision = RevDeploy::getDeploymentRev($commonRepo);
        $viewData->hostGrpInfo = RevDeploy::getDeploymentHostGroup($commonRepo, $hostGrpName, $commonrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('host_group_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('host_group_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hostGrpName = $this->getParam('hostName');
        $hostGrpInfo = $this->fetchHostInfo($deployment, 'copy_write');
        if (RevDeploy::existsDeploymentHostGroup($deployment, $hostGrpName, $modrevision) === true) {
            $viewData->error = 'Host information exists for '.$hostGrpName.' in '.$deployment.' Deployment';
            $viewData->hostGrpInfo = $hostGrpInfo;
            $viewData->action = 'copy_write';
            $this->sendResponse('host_group_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentHostGroup($deployment, $hostGrpName, $hostGrpInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('host_group_error');
            $viewData->error = 'Unable to write host information for '.$hostGrpName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->host = $hostGrpName;
        $this->sendResponse('host_group_write', $viewData);
    }
}
