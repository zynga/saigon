<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPECfgController extends Controller {

    private function fetchNRPECfgInfo($deployment, $action, $revision) {
        $nrpecfg = array();
        $nrpecfg['location'] = $this->getParam('location');
        $nrpecfg['pid_file'] = $this->getParam('pidfile');
        $nrpecfg['server_port'] = $this->getParam('port');
        $nrpecfg['nrpe_user'] = $this->getParam('user');
        $nrpecfg['nrpe_group'] = $this->getParam('group');
        $nrpecfg['dont_blame_nrpe'] = $this->getParam('dontblame');
        $nrpecfg['debug'] = $this->getParam('debug');
        $nrpecfg['command_timeout'] = $this->getParam('cmdtimeout');
        $nrpecfg['connection_timeout'] = $this->getParam('conntimeout');
        $nrpecfg['allowed_hosts'] = $this->getParam('allowedhosts');
        $nrpecfg['include_dir'] = $this->getParam('includedir');
        $nrpecfg['cmds'] = $this->getParam('nrpecmds');
        if ($nrpecfg['dont_blame_nrpe'] === false) $nrpecfg['dont_blame_nrpe'] = '0';
        if ($nrpecfg['debug'] === false) $nrpecfg['debug'] = '0';
        if (($nrpecfg['cmds'] === false) || (empty($nrpecfg['cmds']))) {
            return false;
        }
        $nrpecfg['cmds'] = implode(',', $nrpecfg['cmds']);
        if ($nrpecfg['location'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect file location';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['pid_file'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect pid file location';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['server_port'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect port to listen on';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if (($nrpecfg['server_port'] < 1024) || ($nrpecfg['server_port'] > 65536)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use port number specified, either its protected or doesnt exist';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['nrpe_user'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect user to run nrpe daemon as';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['nrpe_group'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect group to run nrpe daemon as';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['command_timeout'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect command timeout specification';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if (($nrpecfg['command_timeout'] < 10) || ($nrpecfg['command_timeout'] > 120)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use command timeout specified, must be larger than 10, and less than 120';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['connection_timeout'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect connection timeout specification';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['connection_timeout'] < 60) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use connection timeout specified, must be larger than 60';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        } else if ($nrpecfg['allowed_hosts'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect allowed hosts that can connect to nrpe daemon';
            $viewData->nrpecfg = $nrpecfg;
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        }
        return $nrpecfg;
    }

    private function fetchSupNRPEInfo($deployment, $action, $revision) {
        $supnrpeInfo = array();
        $supnrpeInfo['cmds'] = $this->getParam('supcmds');
        $supnrpeInfo['location'] = $this->getParam('location');
        if ($supnrpeInfo['location'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect location param needed for saving the nrpe file';
            $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $revision);
            $viewData->supcmds = $supnrpeInfo;
            $viewData->deployment = $deployment;
            $this->sendResponse($action, $viewData);
        }
        if ($supnrpeInfo['cmds'] !== false) {
            $supnrpeInfo['cmds'] = implode(',', $supnrpeInfo['cmds']);
        }
        return $supnrpeInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentNRPECfg($deployment, $modrevision) !== false) {
            $viewData->nrpecfg = RevDeploy::getDeploymentNRPECfg($deployment, $modrevision);
        }
        $viewData->deployment = $deployment;
        $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $modrevision);
        $this->sendResponse('nrpe_cfg_stage', $viewData);
    }

    public function write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cfg_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $nrpecfgInfo = $this->fetchNRPECfgInfo($deployment, 'nrpe_cfg_stage', $modrevision);
        $viewData->deployment = $deployment;
        if ($nrpecfgInfo !== false) {
            RevDeploy::writeDeploymentNRPECfg($deployment, $nrpecfgInfo, $modrevision);
            $this->sendResponse('nrpe_cfg_write', $viewData);
        }
        else {
            RevDeploy::deleteDeploymentNRPECfg($deployment, $modrevision);
            $this->sendResponse('nrpe_cfg_del', $viewData);
        }
    }

    public function show() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentNRPECfg($deployment, $modrevision) !== false) {
            $nrpecfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $modrevision);
            $filecontents = NRPECreate::buildNRPEFile($deployment, $modrevision, $nrpecfgInfo);
            $viewData->deployment = $deployment;
            $viewData->md5 = md5($filecontents);
            $viewData->location = $nrpecfgInfo['location'];
            $viewData->msg = $filecontents;
            $this->sendResponse('nrpe_cfg_output', $viewData);
        }
        $viewData->header = $this->getErrorHeader('nrpe_cfg_error');
        $viewData->error = 'NRPE Config File for Deployment Specified Does not Exist';
        $this->sendError('generic_error', $viewData);
    }

    public function supstage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentSupNRPECfg($deployment, $modrevision) !== false) {
            $viewData->nrpecfg = RevDeploy::getDeploymentSupNRPECfg($deployment, $modrevision);
        }
        $viewData->deployment = $deployment;
        $viewData->nrpecmds = NRPECreate::buildNRPECmdLines($deployment, $modrevision);
        $this->sendResponse('sup_nrpe_cfg_stage', $viewData);
    }

    public function supwrite() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_cfg_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $supnrpeInfo = $this->fetchSupNRPEInfo($deployment, 'sup_nrpe_cfg_stage', $modrevision);
        $viewData->deployment = $deployment;
        if ($supnrpeInfo['cmds'] !== false) {
            RevDeploy::writeDeploymentSupNRPECfg($deployment, $supnrpeInfo, $modrevision);
            $this->sendResponse('sup_nrpe_cfg_write', $viewData);
        } else {
            RevDeploy::deleteDeploymentSupNRPECfg($deployment, $modrevision);
            $this->sendResponse('sup_nrpe_cfg_del', $viewData);
        }
    }

    public function supshow() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentSupNRPECfg($deployment, $modrevision) !== false) {
            $supnrpecfgInfo = RevDeploy::getDeploymentSupNRPECfg($deployment, $modrevision);
            $filecontents = NRPECreate::buildSupNRPEFile($deployment, $modrevision, $supnrpecfgInfo);
            $viewData->md5 = md5($filecontents);
            $viewData->location = $supnrpecfgInfo['location'];
            $viewData->msg = $filecontents;
            $viewData->deployment = $deployment;
            $this->sendResponse('sup_nrpe_cfg_output', $viewData);
        }
        $viewData->header = $this->getErrorHeader('sup_nrpe_cfg_error');
        $viewData->error = 'No Supplemental NRPE Config File Found';
        $this->sendError('generic_error', $viewData);
    }

    public function import_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentNRPECfg($deployment, $modrevision) !== false) {
            $viewData->error = "NRPE Configuration file exists for Deployment: $deployment";
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('nrpe_cfg_import_stage', $viewData);
    }

    public function import_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_cfg_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $filelocation = $this->getParam('location');
        if ($filelocation === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cfg_error');
            $viewData->error = 'Unable to read file location, please ensure file location was specified';
            $this->sendError('generic_error', $viewData);
        }
        $filecontents = $this->fetchUploadedFile('file');
        if ($filecontents === false) {
            $viewData->header = $this->getErrorHeader('nrpe_cfg_error');
            $viewData->error = 'Unable to read imported file, please ensure file was appended';
            $this->sendError('generic_error', $viewData);
        }
        $filemeta = NagImport::processNRPECfg($filecontents);
        if ((empty($filemeta['meta'])) || (empty($filemeta['cmds']))) {
            $viewData->header = $this->getErrorHeader('nrpe_cfg_error');
            $viewData->error = 'Unable to read imported file, problem parsing out meta or command information';
            $this->sendError('generic_error', $viewData);
        }
        RevDeploy::importDeploymentNRPECfg($deployment, $modrevision, $filelocation, $filemeta);
        $viewData->deployment = $deployment;
        $this->sendResponse('nrpe_cfg_import_write', $viewData);
    }

    public function sup_import_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentSupNRPECfg($deployment, $modrevision) !== false) {
            $viewData->error = "Supplemental NRPE Configuration file exists for Deployment: $deployment";
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('sup_nrpe_cfg_import_stage', $viewData);
    }

    public function sup_import_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_cfg_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $filelocation = $this->getParam('location');
        if ($filelocation === false) {
            $viewData->header = $this->getErrorHeader('sup_nrpe_cfg_error');
            $viewData->error = 'Unable to read file location, please ensure file location was specified';
            $this->sendError('generic_error', $viewData);
        }
        $filecontents = $this->fetchUploadedFile('file');
        if ($filecontents === false) {
            $viewData->header = $this->getErrorHeader('sup_nrpe_cfg_error');
            $viewData->error = 'Unable to read imported file, please ensure file was appended';
            $this->sendError('generic_error', $viewData);
        }
        $filemeta = NagImport::processSupNRPECfg($filecontents);
        if (empty($filemeta)) {
            $viewData->header = $this->getErrorHeader('sup_nrpe_cfg_error');
            $viewData->error = 'Unable to read imported file, problem parsing command information';
            $this->sendError('generic_error', $viewData);
        }
        RevDeploy::importDeploymentSupNRPECfg($deployment, $modrevision, $filelocation, $filemeta);
        $viewData->deployment = $deployment;
        $this->sendResponse('sup_nrpe_cfg_import_write', $viewData);
    }

}

