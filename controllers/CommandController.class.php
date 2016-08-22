<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CommandController extends Controller
{

    /**
     * _fetchCommandInfo - used for fetching necessary information for command processing 
     * 
     * @param mixed $deployment deployment we are processing 
     * @param mixed $action     type of action that is being issued
     *
     * @access private
     * @return void
     */
    private function _fetchCommandInfo($deployment, $action)
    {
        $commandInfo = array();
        $commandInfo['command_name'] = $this->getParam('cmdName');
        $commandInfo['command_desc'] = $this->getParam('cmdDesc');
        $commandInfo['command_line'] = $this->getParam('cmdLine');
        if ($commandInfo['command_line'] !== false) {
            $commandInfo['command_line'] = base64_encode($commandInfo['command_line']);
        }
        if (($commandInfo['command_name'] === false) || ($commandInfo['command_desc'] === false) || ($commandInfo['command_line'] === false)) {
            $viewData = new ViewData();
            if ($action == 'copyToWrite') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $viewData->error = 'Unable to detect incoming command parameters, make sure all of your input fields are filled in';
            $viewData->commandInfo = $commandInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copyToWrite') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('command_action_stage', $viewData);
        } else if (preg_match_all('/[^a-zA-Z0-9_-]/s', $commandInfo['command_name'], $forbidden)) {
            $viewData = new ViewData();
            if ($action == 'copyToWrite') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $viewData->error = 'Unable to use command name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->commandInfo = $commandInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copyToWrite') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('command_action_stage', $viewData);
        } else if (preg_match('/;/', $commandInfo['command_line'])) {
            $viewData = new ViewData();
            if ($action == 'copyToWrite') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $viewData->error = 'Command Line isn\'t valid, make sure you don\'t have any semicolons in your command';
            $viewData->commandInfo = $commandInfo;
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copyToWrite') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('command_action_stage', $viewData);
        }
        return $commandInfo;
    }

    /**
     * stage 
     * 
     * @access public
     * @return void
     */
    public function stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->commands = RevDeploy::getCommonMergedDeploymentCommands($deployment, $modrevision, false);
        $viewData->deployment = $deployment;
        $this->sendResponse('command_stage', $viewData);
    }

    /**
     * addStage 
     * 
     * @access public
     * @return void
     */
    public function addStage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $viewData->deployment = $deployment;
        $viewData->action = 'addWrite';
        $this->sendResponse('command_action_stage', $viewData);
    }

    /**
     * addWrite 
     * 
     * @access public
     * @return void
     */
    public function addWrite()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $cmdName = $this->getParam('cmdName');
        $cmdInfo = $this->_fetchCommandInfo($deployment, 'addWrite');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentCommand($deployment, $cmdName, $modrevision) === true) {
            $viewData->error = 'Command information for '.$cmdName.' already exists for '.$deployment.' Deployment';
            $viewData->commandInfo = $cmdInfo;
            $viewData->action = 'addWrite';
            $this->sendResponse('command_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentCommand($deployment, $cmdName, $cmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to write command information for '.$cmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $cmdName;
        $this->sendResponse('command_write', $viewData);
    }

    /**
     * modifyStage 
     * 
     * @access public
     * @return void
     */
    public function modifyStage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $command = $this->getParam('cmdName');
        if ($command === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cmdInfo = RevDeploy::getDeploymentCommand($deployment, $command, $modrevision);
        if (empty($cmdInfo)) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to fetch command '.$command.' for deployment '.$deployment.' from datastore';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->action = 'modifyWrite';
        $viewData->commandInfo = $cmdInfo;
        $this->sendResponse('command_action_stage', $viewData);
    }

    /**
     * modifyWrite 
     * 
     * @access public
     * @return void
     */
    public function modifyWrite()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $cmdName = $this->getParam('cmdName');
        $cmdInfo = $this->_fetchCommandInfo($deployment, 'modifyWrite');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::modifyDeploymentCommand($deployment, $cmdName, $cmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to write command information for '.$cmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $cmdName;
        $this->sendResponse('command_write', $viewData);
    }

    /**
     * delStage 
     * 
     * @access public
     * @return void
     */
    public function delStage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $viewData->deployment = $deployment;
        $command = $this->getParam('cmdName');
        if ($command === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $command;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $commandInfo = RevDeploy::getDeploymentCommand($deployment, $command, $modrevision);
        if (empty($commandInfo)) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to fetch command information for '.$command.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->commandInfo = $commandInfo;
        $this->sendResponse('command_delete_stage', $viewData);
    }

    /**
     * delWrite 
     * 
     * @access public
     * @return void
     */
    public function delWrite()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $command = $this->getParam('command');
        if ($command === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $command;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentCommand($deployment, $command, $modrevision);
        $this->sendResponse('command_delete', $viewData);
    }

    /**
     * copyStage 
     * 
     * @access public
     * @return void
     */
    public function copyStage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $viewData->deployment = $deployment;
        $command = $this->getParam('cmdName');
        if ($command === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $commandInfo = RevDeploy::getDeploymentCommand($deployment, $command, $modrevision);
        if (empty($commandInfo)) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to fetch command information for '.$command.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->commandInfo = $commandInfo;
        $viewData->action = 'copyWrite';
        $this->sendResponse('command_action_stage', $viewData);
    }

    /**
     * copyCommonStage 
     * 
     * @access public
     * @return void
     */
    public function copyCommonStage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $viewData->deployment = $deployment;
        $command = $this->getParam('cmdName');
        if ($command === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $command;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $commandInfo = RevDeploy::getCommonMergedDeploymentCommand($deployment, $command, $modrevision);
        if (empty($commandInfo)) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to fetch command information for '.$command.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->commandInfo = $commandInfo;
        $viewData->action = 'copyWrite';
        $this->sendResponse('command_action_stage', $viewData);
    }

    /**
     * copyWrite 
     * 
     * @access public
     * @return void
     */
    public function copyWrite()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $cmdName = $this->getParam('cmdName');
        $cmdInfo = $this->_fetchCommandInfo($deployment, 'copyWrite');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentCommand($deployment, $cmdName, $modrevision) === true) {
            $viewData->error = 'Command information for '.$cmdName.' already exists for '.$deployment.' Deployment';
            $viewData->commandInfo = $cmdInfo;
            $viewData->action = 'copyWrite';
            $this->sendResponse('command_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentCommand($deployment, $cmdName, $cmdInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to write command information for '.$cmdName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->command = $cmdName;
        $this->sendResponse('command_write', $viewData);
    }

    /**
     * getCmdline 
     * 
     * @access public
     * @return void
     */
    public function getCmdline()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $cmd = $this->getParam('cmdName');
        if ($cmd === false) {
            die();
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentCommand($deployment, $cmd, $modrevision) === true) {
            $viewData->cmdline = RevDeploy::getDeploymentCommandExec($deployment, $cmd, $modrevision);
        } else {
            $commonRepos = RevDeploy::getCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $cRev = RevDeploy::getDeploymentRev($commonRepo);
                if (RevDeploy::existsDeploymentCommand($commonRepo, $cmd, $cRev) === true) {
                    $viewData->cmdline = RevDeploy::getDeploymentCommandExec($commonRepo, $cmd, $cRev);
                    break;
                }
            }
        }
        $this->sendResponse('command_show_cmdline', $viewData);
    }

    /**
     * copyToStage 
     * 
     * @access public
     * @return void
     */
    public function copyToStage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $command = $this->getParam('cmdName');
        if ($command === false) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to detect command specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $revision = RevDeploy::getDeploymentNextRev($deployment);
        $commandInfo = RevDeploy::getDeploymentCommand($deployment, $command, $revision);
        if (empty($commandInfo)) {
            $viewData->header = $this->getErrorHeader('command_error');
            $viewData->error = 'Unable to fetch command information for '.$command.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
        $viewData->deployment = $deployment;
        $viewData->command = $command;
        $viewData->commandInfo = $commandInfo;
        $viewData->action = 'copyToWrite';
        $this->sendResponse('command_action_stage', $viewData);
    }

    /**
     * copyToWrite 
     * 
     * @access public
     * @return void
     */
    public function copyToWrite()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('command_error');
        $cmdName = $this->getParam('cmdName');
        $cmdInfo = $this->_fetchCommandInfo($deployment, 'copyToWrite');
        $todeployment = $this->getParam('todeployment');
        if ($todeployment === false) {
            $viewData->error = 'Unable to detect deployment to copy command to';
            $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            $viewData->deployment = $deployment;
            $viewData->command = $cmdName;
            $viewData->commandInfo = $cmdInfo;
            $viewData->action = 'copyToWrite';
            $this->sendResponse('command_action_stage', $viewData);
        }
        $this->checkGroupAuthByDeployment($todeployment);
        $this->checkDeploymentRevStatus($todeployment);
        $tdRev = RevDeploy::getDeploymentNextRev($todeployment);
        if (RevDeploy::existsDeploymentCommand($todeployment, $cmdName, $tdRev) === true) {
            RevDeploy::modifyDeploymentCommand($todeployment, $cmdName, $cmdInfo, $tdRev);
        } else {
            RevDeploy::createDeploymentCommand($todeployment, $cmdName, $cmdInfo, $tdRev);
        }
        $viewData->deployment = $deployment;
        $viewData->todeployment = $todeployment;
        $viewData->command = $cmdName;
        $this->sendResponse('command_write', $viewData);
    }

}

