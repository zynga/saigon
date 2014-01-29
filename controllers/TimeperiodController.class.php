<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class TimeperiodController extends Controller {

    private function fetchTimeperiodInfo($deployment, $action, $modrevision) {
        $timeInfo = array();
        $timeInfo['timeperiod_name'] = $this->getParam('tpName');
        $timeInfo['alias'] = $this->getParam('tpAlias');
        $timeInfo['use'] = $this->getParam('tpUse');
        if (($timeInfo['timeperiod_name'] === false) || ($timeInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming timeperiod parameters, make sure all of your input fields are filled in';
            $viewData->timeInfo = $timeInfo;
            $viewData->action = $action;
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $this->sendResponse('timeperiod_action_stage', $viewData);
        } else if (preg_match_all('/[^a-zA-Z0-9_-]/s', $timeInfo['timeperiod_name'], $forbidden)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use timeperiod name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->timeInfo = $timeInfo;
            $viewData->action = $action;
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $this->sendResponse('timeperiod_action_stage', $viewData);
        }
        return $timeInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if ((!isset($_SESSION[$deployment])) || (!is_array($_SESSION[$deployment]))) {
            $_SESSION[$deployment] = array();
        }
        $_SESSION[$deployment]['timeperiods'] = array();
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('timeperiod_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('timeperiod_action_stage', $viewData);
    }

    public function add_timeperiod() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $directive = $this->getParam('dir');
        $range = $this->getParam('range');
        $md5_directive = md5($directive);
        $_SESSION[$deployment]['timeperiods'][$md5_directive]['directive'] = $directive;
        $_SESSION[$deployment]['timeperiods'][$md5_directive]['range'] = $range;
        $viewData->deployment = $deployment;
        $this->sendResponse('timeperiod_times_window', $viewData);
    }

    public function del_timeperiod() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $directive = $this->getParam('dir');
        unset($_SESSION[$deployment]['timeperiods'][$directive]);
        $viewData->deployment = $deployment;
        $this->sendResponse('timeperiod_times_window', $viewData);
    }

    public function view_timeperiod() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $viewData->deployment = $deployment;
        $this->sendResponse('timeperiod_times_window', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $timeName = $this->getParam('tpName');
        $timeInfo = $this->fetchTimeperiodInfo($deployment, 'add_write', $modrevision);
        $tpArray = $_SESSION[$deployment]['timeperiods'];
        if (RevDeploy::existsDeploymentTimeperiod($deployment, $timeName, $modrevision) === true) {
            $viewData->error = 'Timeperiod information exists for '.$timeName.' in '.$deployment.' Deployment';
            $viewData->timeInfo = $timeInfo;
            $viewData->action = 'add_write';
            $this->sendResponse('timeperiod_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentTimeperiod($deployment, $timeName, $timeInfo, $tpArray, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to write timeperiod information for '.$timeName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        unset($_SESSION[$deployment]['timeperiods']);
        $viewData->timeperiod = $timeName;
        $this->sendResponse('timeperiod_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $timeName = $this->getParam('timeperiod');
        if ($timeName === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to detect timeperiod specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $timeInfo = RevDeploy::getDeploymentTimeperiodInfo($deployment, $timeName, $modrevision);
        if (empty($timeInfo)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod info '.$timeName.' for deployment '.$deployment.' from datastore';
            $this->sendError('generic_error', $viewData);
        }
        $timeData = RevDeploy::getDeploymentTimeperiodData($deployment, $timeName, $modrevision);
        if (empty($timeData)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod data '.$timeName.' for deployment '.$deployment.' from datastore';
            $this->sendError('generic_error', $viewData);
        }
        unset($_SESSION[$deployment]['timeperiods']);
        $_SESSION[$deployment]['timeperiods'] = array();
        foreach ($timeData as $md5Key => $tpArray) {
            $_SESSION[$deployment]['timeperiods'][$md5Key] = $tpArray;
        }
        $viewData->deployment = $deployment;
        $viewData->timeInfo = $timeInfo;
        $viewData->action = 'modify_write';
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $this->sendResponse('timeperiod_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $timeName = $this->getParam('tpName');
        $tpArray = $_SESSION[$deployment]['timeperiods'];
        $timeInfo = $this->fetchTimeperiodInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentTimeperiod($deployment, $timeName, $timeInfo, $tpArray, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to write timeperiod information for '.$timeName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        unset($_SESSION[$deployment]['timeperiods']);
        $viewData->timeperiod = $timeName;
        $this->sendResponse('timeperiod_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $timeName = $this->getParam('timeperiod');
        if ($timeName === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to detect timeperiod specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $timeInfo = RevDeploy::getDeploymentTimeperiodInfo($deployment, $timeName, $modrevision);
        if (empty($timeInfo)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod information for '.$timeName.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $timeData = RevDeploy::getDeploymentTimeperiodData($deployment, $timeName, $modrevision);
        if (empty($timeData)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod data for '.$timeName.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->timeName = $timeName;
        $viewData->timeInfo = $timeInfo;
        $viewData->timeData = $timeData;
        $this->sendResponse('timeperiod_delete_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $timeName = $this->getParam('timeperiod');
        if ($timeName === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to detect timeperiod specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentTimeperiod($deployment, $timeName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->timeName = $timeName;
        $this->sendResponse('timeperiod_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $timeName = $this->getParam('timeperiod');
        if ($timeName === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to detect timeperiod specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $timeInfo = RevDeploy::getDeploymentTimeperiodInfo($deployment, $timeName, $modrevision);
        if (empty($timeInfo)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod information for '.$timeName.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $timeData = RevDeploy::getDeploymentTimeperiodData($deployment, $timeName, $modrevision);
        if (empty($timeData)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod data for '.$timeName.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->timeInfo = $timeInfo;
        unset($_SESSION[$deployment]['timeperiods']);
        $_SESSION[$deployment]['timeperiods'] = array();
        foreach ($timeData as $md5Key => $tpArray) {
            $_SESSION[$deployment]['timeperiods'][$md5Key] = $tpArray;
        }
        $viewData->action = 'copy_write';
        $this->sendResponse('timeperiod_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $timeName = $this->getParam('timeperiod');
        if ($timeName === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to detect timeperiod specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $commonRepo = RevDeploy::getDeploymentCommonRepo($deployment);
        $commonrevision = RevDeploy::getDeploymentRev($commonRepo);
        $timeInfo = RevDeploy::getDeploymentTimeperiodInfo($commonRepo, $timeName, $commonrevision);
        if (empty($timeInfo)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod information for '.$timeName.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $timeData = RevDeploy::getDeploymentTimeperiodData($commonRepo, $timeName, $commonrevision);
        if (empty($timeData)) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to fetch timeperiod data for '.$timeName.' from data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->timeInfo = $timeInfo;
        unset($_SESSION[$deployment]['timeperiods']);
        $_SESSION[$deployment]['timeperiods'] = array();
        foreach ($timeData as $md5Key => $tpArray) {
            $_SESSION[$deployment]['timeperiods'][$md5Key] = $tpArray;
        }
        $viewData->action = 'copy_write';
        $this->sendResponse('timeperiod_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('timeperiod_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $timeName = $this->getParam('tpName');
        $tpArray = $_SESSION[$deployment]['timeperiods'];
        $timeInfo = $this->fetchTimeperiodInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentTimeperiod($deployment, $timeName, $modrevision) === true) {
            $viewData->error = 'Timeperiod information exists for '.$timeName.' in '.$deployment.' Deployment';
            $viewData->timeInfo = $timeInfo;
            $viewData->action = 'copy_write';
            $this->sendResponse('timeperiod_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentTimeperiod($deployment, $timeName, $timeInfo, $tpArray, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('timeperiod_error');
            $viewData->error = 'Unable to write timeperiod information for '.$timeName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        unset($_SESSION[$deployment]['timeperiods']);
        $viewData->deployment = $deployment;
        $viewData->timeperiod = $timeName;
        $this->sendResponse('timeperiod_write', $viewData);
    }

}
