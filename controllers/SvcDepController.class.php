<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SvcDepController extends Controller {

    private function fetchSvcDepInfo($deployment, $action, $modrevision) {
        $svcDepInfo = array();
        $svcDepInfo['name'] = $this->getParam('svcDepName');
        $svcDepInfo['service_description'] = $this->getParam('parentsvc');
        $svcDepInfo['dependent_service_description'] = $this->getParam('dependentsvc');
        $svcDepInfo['execution_failure_criteria'] = $this->getParam('checkcriteria');
        $svcDepInfo['notification_failure_criteria'] = $this->getParam('notifcriteria');
        $svcDepInfo['inherits_parent'] = $this->getParam('inheritparent');
        if ($svcDepInfo['name'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming service parameters, make sure all of your input fields are filled in';
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $this->sendResponse('svc_dep_action_stage', $viewData);
        } else if (preg_match_all('/[^a-zA-Z0-9_-]/s', $svcDepInfo['name'], $forbidden)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use service group name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $this->sendResponse('svc_dep_action_stage', $viewData);
        } else if ($svcDepInfo['service_description'] == $svcDepInfo['dependent_service_description']) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to complete request, Parent and Dependent Service can not be the same';
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $this->sendResponse('svc_dep_action_stage', $viewData);
        } else if ($svcDepInfo['execution_failure_criteria'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to complete request, Execution Criteria param was empty';
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $this->sendResponse('svc_dep_action_stage', $viewData);
        } else if ($svcDepInfo['notification_failure_criteria'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to complete request, Notification Criteria param was empty';
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $this->sendResponse('svc_dep_action_stage', $viewData);
        }
        if (($svcDepInfo['inherits_parent'] === false) || ( $svcDepInfo['inherits_parent'] == 'off')) {
            unset($svcDepInfo['inherits_parent']);
        }
        elseif ($svcDepInfo['inherits_parent'] == 'on') {
            $svcDepInfo['inherits_parent'] = 1;
        }
        return $svcDepInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcdeps = RevDeploy::getCommonMergedDeploymentSvcDependencies($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('svc_dep_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('svc_dep_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $svcDepInfo = $this->fetchSvcDepInfo($deployment,'add_write', $modrevision);
        $svcDepName = $svcDepInfo['name'];
        if (RevDeploy::existsDeploymentSvcDependency($deployment, $svcDepName, $modrevision) === true) {
            $viewData->error = 'Service information exists for '.$svcDepName.' in '.$deployment.' Deployment';
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->action = 'add_write';
            $this->sendResponse('svc_dep_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcDependency($deployment, $svcDepName, $svcDepInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to write service information for '.$svcDepName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svc = $svcDepName;
        $this->sendResponse('svc_dep_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $svcDepName = $this->getParam('svcDep');
        if ($svcDepName === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcDepInfo = RevDeploy::getDeploymentSvcDependency($deployment, $svcDepName, $modrevision);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $this->sendResponse('svc_dep_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $svcDepInfo = $this->fetchSvcDepInfo($deployment, 'modify_write', $modrevision);
        $svcDepName = $svcDepInfo['name'];
        if (RevDeploy::modifyDeploymentSvcDependency($deployment, $svcDepName, $svcDepInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to write service group information for '.$svcDepName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcDepName;
        $this->sendResponse('svc_dep_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $svcDepName = $this->getParam('svcDep');
        if ($svcDepName === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcDepInfo = RevDeploy::getDeploymentSvcDependency($deployment, $svcDepName, $modrevision);
        $viewData->svcDepInfo = $svcDepInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('svc_dep_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $svcDepName = $this->getParam('svcDep');
        if ($svcDepName === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentSvcDependency($deployment, $svcDepName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svc = $svcDepName;
        $this->sendResponse('svc_dep_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $svcDepName = $this->getParam('svcDep');
        if ($svcDepName === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcDepInfo = RevDeploy::getDeploymentSvcDependency($deployment, $svcDepName, $modrevision);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_dep_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $svcDepName = $this->getParam('svcDep');
        if ($svcDepName === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcDepInfo = RevDeploy::getCommonMergedDeploymentSvcDependency($deployment, $svcDepName, $modrevision);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_dep_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_dep_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcDepInfo = $this->fetchSvcDepInfo($deployment, 'copy_write', $modrevision);
        $svcDepName = $svcDepInfo['name'];
        if (RevDeploy::existsDeploymentSvcDependency($deployment, $svcDepName, $modrevision) === true) {
            $viewData->error = 'Service information exists for '.$svcDepName.' in '.$deployment.' Deployment';
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->svcDepInfo = $svcDepInfo;
            $viewData->action = 'copy_write';
            $this->sendResponse('svc_dep_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcDependency($deployment, $svcDepName, $svcDepInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_dep_error');
            $viewData->error = 'Unable to write service information for '.$svcDepName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svc = $svcDepName;
        $this->sendResponse('svc_dep_write', $viewData);
    }
}
