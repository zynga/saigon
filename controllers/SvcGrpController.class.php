<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//
 
class SvcGrpController extends Controller {

    private function fetchSvcInfo($deployment, $action) {
        $svcGrpInfo = array();
        $svcGrpInfo['servicegroup_name'] = $this->getParam('svcName');
        $svcGrpInfo['alias'] = $this->getParam('svcAlias');
        if (($svcGrpInfo['servicegroup_name'] === false) || ($svcGrpInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming service parameters, make sure all of your input fields are filled in';
            $viewData->svcGrpInfo = $svcGrpInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('svc_group_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $svcGrpInfo['servicegroup_name'], $forbidden))
                || (preg_match_all('/[^a-zA-Z0-9_-\s\/]/s', $svcGrpInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use service group name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->svcGrpInfo = $svcGrpInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('svc_group_action_stage', $viewData);
        }
        $notesurl = $this->getParam('notesurl');
        if ($notesurl !== false) $svcGrpInfo['notes_url'] = $notesurl;
        $actionurl = $this->getParam('actionurl');
        if ($actionurl !== false) $svcGrpInfo['action_url'] = $actionurl;
        return $svcGrpInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcgroups = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('svc_group_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('svc_group_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $svcGrpInfo = $this->fetchSvcInfo($deployment, 'add_write');
        $svcGrpName = $this->getParam('svcName');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentSvcGroup($deployment, $svcGrpName, $modrevision) === true) {
            $viewData->error = 'Service information exists for '.$svcGrpName.' in '.$deployment.' Deployment';
            $viewData->svcGrpInfo = $svcGrpInfo;
            $viewData->action = 'add_write';
            $this->sendResponse('svc_group_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcGroup($deployment, $svcGrpName, $svcGrpInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to write service information for '.$svcGrpName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svc = $svcGrpName;
        $this->sendResponse('svc_group_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $svcGrpName = $this->getParam('svcName');
        if ($svcGrpName === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcGrpInfo = RevDeploy::getDeploymentSvcGroup($deployment, $svcGrpName, $modrevision);
        $viewData->svcGrpInfo = $svcGrpInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $this->sendResponse('svc_group_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $svcGrpName = $this->getParam('svcName');
        $svcGrpInfo = $this->fetchSvcInfo($deployment, 'modify_write');
        if (RevDeploy::modifyDeploymentSvcGroup($deployment, $svcGrpName, $svcGrpInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to write service group information for '.$svcGrpName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcGrpName;
        $this->sendResponse('svc_group_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $svcGrpName = $this->getParam('svcName');
        if ($svcGrpName === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcGrpInfo = RevDeploy::getDeploymentSvcGroup($deployment, $svcGrpName, $modrevision);
        $viewData->svcGrpInfo = $svcGrpInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('svc_group_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $svcGrpName = $this->getParam('svcName');
        if ($svcGrpName === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentSvcGroup($deployment, $svcGrpName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svc = $svcGrpName;
        $this->sendResponse('svc_group_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $svcGrpName = $this->getParam('svcName');
        if ($svcGrpName === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcGrpInfo = RevDeploy::getDeploymentSvcGroup($deployment, $svcGrpName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_group_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $svcGrpName = $this->getParam('svcName');
        if ($svcGrpName === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcGrpInfo = RevDeploy::getCommonMergedDeploymentSvcGroup($deployment, $svcGrpName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_group_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcGrpName = $this->getParam('svcName');
        $svcGrpInfo = $this->fetchSvcInfo($deployment, 'copy_write');
        if (RevDeploy::existsDeploymentSvcGroup($deployment, $svcGrpName, $modrevision) === true) {
            $viewData->error = 'Service information exists for '.$svcGrpName.' in '.$deployment.' Deployment';
            $viewData->svcGrpInfo = $svcGrpInfo;
            $viewData->action = 'copy_write';
            $this->sendResponse('svc_group_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcGroup($deployment, $svcGrpName, $svcGrpInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_group_error');
            $viewData->error = 'Unable to write service information for '.$svcGrpName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svc = $svcGrpName;
        $this->sendResponse('svc_group_write', $viewData);
    }
}
