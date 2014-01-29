<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPECmdController extends Controller {

    private function fetchNRPECmdInfo($deployment, $action) {
        $nrpecmdInfo = array();
        $nrpecmdInfo['cmd_name'] = $this->getParam('nrpecmdname');
        $nrpecmdInfo['cmd_desc'] = $this->getParam('nrpecmddesc');
        $nrpecmdInfo['cmd_line'] = $this->getParam('nrpecmdline');
        if ($nrpecmdInfo['cmd_line'] !== false) {
            $nrpecmdInfo['cmd_line'] = base64_encode($nrpecmdInfo['cmd_line']);
        }
        if (($nrpecmdInfo['cmd_name'] === false) || ($nrpecmdInfo['cmd_desc'] === false) || ($nrpecmdInfo['cmd_line'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming command parameters, make sure all of your input fields are filled in';
            $viewData->nrpecmdInfo = $nrpecmdInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copy_to_write') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('nrpe_cmd_action_stage', $viewData);
        } else if (preg_match_all('/[^a-zA-Z0-9_-]/s', $nrpecmdInfo['cmd_name'], $forbidden)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use command name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->nrpecmdInfo = $nrpecmdInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copy_to_write') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('nrpe_cmd_action_stage', $viewData);
        } else if (preg_match('/;/', $nrpecmdInfo['cmd_line'])) {
            $viewData = new ViewData();
            $viewData->error = 'Command Line isn\'t valid, make sure you don\'t have any semicolons in your command';
            $viewData->nrpecmdInfo = $nrpecmdInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copy_to_write') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('nrpe_cmd_action_stage', $viewData);
        }
        return $nrpecmdInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nrpecmds = RevDeploy::getCommonMergedDeploymentNRPECmds($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('nrpe_cmd_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('nrpe_cmd_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = $this->fetchNRPECmdInfo($deployment, 'add_write');
        $nrpecmdName = $nrpecmdInfo['cmd_name'];
        if (RevDeploy::existsDeploymentNRPECmd($deployment, $nrpecmdName, $modrevision) === true) {
            $viewData->error = 'Command information for '.$nrpecmdName.' already exists for '.$deployment.' Deployment';
            $viewData->nrpecmdInfo = $nrpecmdInfo;
            $viewData->action = 'add_write';
            $this->sendResponse('nrpe_cmd_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentNRPECmd($deployment, $nrpecmdName, $nrpecmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to write command information for '.$nrpecmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $nrpecmdName;
        $this->sendResponse('nrpe_cmd_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $nrpecmd = $this->getParam('cmdname');
        if ($nrpecmd === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = RevDeploy::getDeploymentNRPECmd($deployment, $nrpecmd, $modrevision);
        if (empty($nrpecmdInfo)) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to fetch command '.$nrpecmd.' for deployment '.$deployment.' from datastore';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $viewData->nrpecmdInfo = $nrpecmdInfo;
        $this->sendResponse('nrpe_cmd_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = $this->fetchNRPECmdInfo($deployment, 'modify_write');
        $nrpecmdName = $nrpecmdInfo['cmd_name'];
        if (RevDeploy::modifyDeploymentNRPECmd($deployment, $nrpecmdName, $nrpecmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to write command information for '.$nrpecmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $nrpecmdName;
        $this->sendResponse('nrpe_cmd_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $viewData->deployment = $deployment;
        $nrpecmd = $this->getParam('cmdname');
        if ($nrpecmd === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $nrpecmd;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = RevDeploy::getDeploymentNRPECmd($deployment, $nrpecmd, $modrevision);
        if (empty($nrpecmdInfo)) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to fetch command information for '.$nrpecmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->nrpecmdInfo = $nrpecmdInfo;
        $viewData->action = 'delete';
        $this->sendResponse('nrpe_cmd_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $nrpecmd = $this->getParam('nrpecmd');
        if ($nrpecmd === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $nrpecmd;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentNRPECmd($deployment, $nrpecmd, $modrevision);
        $this->sendResponse('nrpe_cmd_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $viewData->deployment = $deployment;
        $nrpecmd = $this->getParam('cmdname');
        if ($nrpecmd === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = RevDeploy::getDeploymentNRPECmd($deployment, $nrpecmd, $modrevision);
        if (empty($nrpecmdInfo)) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to fetch command information for '.$nrpecmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->nrpecmdInfo = $nrpecmdInfo;
        $viewData->action = 'copy_write';
        $this->sendResponse('nrpe_cmd_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $viewData->deployment = $deployment;
        $nrpecmd = $this->getParam('cmdname');
        if ($nrpecmd === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $nrpecmd;
        $commonRepo = RevDeploy::getDeploymentCommonRepo($deployment);
        $commonrevision = RevDeploy::getDeploymentRev($commonRepo);
        $nrpecmdInfo = RevDeploy::getDeploymentNRPECmd($commonRepo, $nrpecmd, $commonrevision);
        if (empty($nrpecmdInfo)) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to fetch command information for '.$nrpecmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->nrpecmdInfo = $nrpecmdInfo;
        $viewData->action = 'copy_write';
        $this->sendResponse('nrpe_cmd_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = $this->fetchNRPECmdInfo($deployment, 'copy_write');
        $nrpecmdName = $nrpecmdInfo['cmd_name'];
        if (RevDeploy::existsDeploymentNRPECmd($deployment, $nrpecmdName, $modrevision) === true) {
            $viewData->error = 'Command information for '.$nrpecmdName.' already exists for '.$deployment.' Deployment';
            $viewData->nrpecmdInfo = $nrpecmdInfo;
            $viewData->action = 'copy_write';
            $this->sendResponse('nrpe_cmd_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentNRPECmd($deployment, $nrpecmdName, $nrpecmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to write command information for '.$nrpecmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $nrpecmdName;
        $this->sendResponse('nrpe_cmd_write', $viewData);
    }

    public function copy_to_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $viewData->deployment = $deployment;
        $nrpecmd = $this->getParam('cmdname');
        if ($nrpecmd === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecmdInfo = RevDeploy::getDeploymentNRPECmd($deployment, $nrpecmd, $modrevision);
        if (empty($nrpecmdInfo)) {
            $viewData->header = $this->getErrorHeader('nrpe_cmd_error');
            $viewData->error = 'Unable to fetch command information for '.$nrpecmd.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
        $viewData->nrpecmdInfo = $nrpecmdInfo;
        $viewData->action = 'copy_to_write';
        $this->sendResponse('nrpe_cmd_action_stage', $viewData);
    }

    public function copy_to_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cmd_error');
        $nrpecmdInfo = $this->fetchNRPECmdInfo($deployment, 'copy_to_write');
        $todeployment = $this->getParam('todeployment');
        if ($todeployment === false) {
            $viewData->error = 'Unable to detect deployment to copy command to';
            $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            $viewData->deployment = $deployment;
            $viewData->nrpecmdInfo = $nrpecmdInfo;
            $viewData->action = 'copy_to_write';
            $this->sendResponse('command_action_stage', $viewData);
        }
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($todeployment);
        $deployRev = RevDeploy::getDeploymentNextRev($deployment);
        $tdRev = RevDeploy::getDeploymentNextRev($todeployment);
        $nrpecmdName = $nrpecmdInfo['cmd_name'];
        $nrpecmdInfo = RevDeploy::getDeploymentNRPECmd($deployment, $nrpecmdName, $deployRev);
        if (RevDeploy::existsDeploymentNRPECmd($todeployment, $nrpecmdName, $tdRev) === true) {
            RevDeploy::modifyDeploymentNRPECmd($todeployment, $nrpecmdName, $nrpecmdInfo, $tdRev);
        } else {
            RevDeploy::createDeploymentNRPECmd($todeployment, $nrpecmdName, $nrpecmdInfo, $tdRev);
        }
        $viewData->deployment = $deployment;
        $viewData->todeployment = $todeployment;
        $viewData->command = $nrpecmdName;
        $this->sendResponse('nrpe_cmd_write', $viewData);
    }

}
