<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class DeploymentController extends Controller
{

    /**
     * _fetchDeploymentInfo - fetch the deployment info that was passed into the system
     * 
     * @param mixed $action action to inform the view to take upon loading
     * @access private
     * @return void
     */
    private function _fetchDeploymentInfo($action)
    {
        $deployInfo = array();
        $deployInfo['name'] = $this->getParam('deployment');
        $deployInfo['desc'] = $this->getParam('deploydesc');
        $deployInfo['authgroups'] = $this->getParam('deployauthgroups');
        $deployInfo['nagioshead'] = $this->getParam('deployhead');
        $deployInfo['type'] = $this->getParam('deploytype');
        $deployInfo['aliastemplate'] = $this->getParam('aliastemplate');
        $deployInfo['ensharding'] = $this->getParam('ensharding');
        $deployInfo['deploystyle'] = $this->getParam('deploystyle');
        $deployInfo['deploynegate'] = $this->getParam('deploynegate');
        $deployInfo['commonrepo'] = $this->getParam('commonrepo');
        if (($deployInfo['name'] === false) || ($deployInfo['desc'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect deployment name or description in post params';
            $viewData->deployInfo = $deployInfo;
            $viewData->action = $action;
            $authmodule = AUTH_MODULE;
            $amodule = new $authmodule();
            $viewData->authtitle = $amodule->getTitle();
            $this->sendResponse('deployment_action_stage', $viewData);
        } elseif (preg_match_all('/[^a-zA-Z0-9_-]/s', $deployInfo['name'], $forbidden)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use deployment name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->deployInfo = $deployInfo;
            $viewData->action = $action;
            $authmodule = AUTH_MODULE;
            $amodule = new $authmodule();
            $viewData->authtitle = $amodule->getTitle();
            $this->sendResponse('deployment_action_stage', $viewData);
        } elseif ($deployInfo['authgroups'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect auth group(s) used for access restriction in post params';
            $viewData->deployInfo = $deployInfo;
            $viewData->action = $action;
            $authmodule = AUTH_MODULE;
            $amodule = new $authmodule();
            $viewData->authtitle = $amodule->getTitle();
            $this->sendResponse('deployment_action_stage', $viewData);
        }
        if ((($deployInfo['aliastemplate'] != 'host-dc') && ($deployInfo['aliastemplate'] != 'host'))
            || ($deployInfo['aliastemplate'] === false)) {
            unset($deployInfo['aliastemplate']);
        }
        if ($deployInfo['type'] === false) unset($deployInfo['type']);
        if ($deployInfo['ensharding'] == 'on') {
            $deployInfo['shardkey'] = $this->getParam('shardkey');
            $deployInfo['shardcount'] = $this->getParam('shardcount');
            if ($deployInfo['shardkey'] === false) {
                $viewData = new ViewData();
                $viewData->error = 'Unable to detect cluster sharding salt key';
                $viewData->deployInfo = $deployInfo;
                $viewData->action = $action;
                $authmodule = AUTH_MODULE;
                $amodule = new $authmodule();
                $viewData->authtitle = $amodule->getTitle();
                $this->sendResponse('deployment_action_stage', $viewData);
            } elseif (($deployInfo['shardcount'] === false) || (!preg_match('/\d+/', $deployInfo['shardcount']))) {
                $viewData = new ViewData();
                $viewData->error = 'Unable to detect cluster sharding count or count supplied was not numeric';
                $viewData->deployInfo = $deployInfo;
                $viewData->action = $action;
                $authmodule = AUTH_MODULE;
                $amodule = new $authmodule();
                $viewData->authtitle = $amodule->getTitle();
                $this->sendResponse('deployment_action_stage', $viewData);
            }
        }
        return $deployInfo;
    }

    /**
     * stage - manage deployments overview 
     * 
     * @access public
     * @return void
     */
    public function stage()
    {
        $viewData = new ViewData();
        $this->checkGroupAuth(SUPERMEN);
        $deployments = RevDeploy::getDeployments();
        asort($deployments);
        $viewData->deployments = $deployments;
        if (!empty($deployments)) {
            $deployInfo = array();
            foreach ($deployments as $deployment) {
                $deployInfo[$deployment] = RevDeploy::getDeploymentGroupInfo($deployment);
                if ($deployment == 'common') $deployInfo[$deployment]['authgroups'] = SUPERMEN;
            }
            $viewData->deployinfo = $deployInfo;
        }
        $this->sendResponse('deployment_stage', $viewData);
    }

    /**
     * add_stage - add deployment stage view / routine 
     * 
     * @access public
     * @return void
     */
    public function add_stage()
    {
        $viewData = new ViewData();
        $viewData->action = 'add_write';
        $viewData->locations = $this->_fetchLocations();
        $viewData->inputs = $this->_fetchInputs();
        $viewData->crepos = RevDeploy::getCommonRepos();
        $authmodule = AUTH_MODULE;
        $amodule = new $authmodule();
        $viewData->authtitle = $amodule->getTitle();
        $builddeployment = substr(md5(microtime()), 0, 9);
        $_SESSION[$builddeployment]['deployments'] = array();
        $_SESSION[$builddeployment]['static-deployments'] = array();
        $_SESSION['add_deployment'] = true;
        $_SESSION['deployment'] = $builddeployment;
        $this->sendResponse('deployment_action_stage', $viewData);
    }

    /**
     * add_write - add deployment write information view / routine 
     * 
     * @access public
     * @return void
     */
    public function add_write()
    {
        $viewData = new ViewData();
        $this->checkGroupAuth(SUPERMEN);
        $deployment = $this->getDeployment('deployment_error');
        $deployInfo = $this->_fetchDeploymentInfo('add_write');
        $builddeployment = $_SESSION['deployment'];
        $deployHostSearch = isset($_SESSION[$builddeployment]['deployments'])?$_SESSION[$builddeployment]['deployments']:array();
        $deployStaticHostSearch = isset($_SESSION[$builddeployment]['static-deployments'])?$_SESSION[$builddeployment]['static-deployments']:array();
        if (RevDeploy::existsDeployment($deployment) === true) {
            $viewData->error = 'Unable to process request; A deployment with the same name has been detected '.$deployment;
            $viewData->locations = $this->_fetchLocations();
            $viewData->inputs = $this->_fetchInputs();
            $viewData->crepos = RevDeploy::getCommonRepos();
            $viewData->deployInfo = $deployInfo;
            $viewData->action = 'add_write';
            $authmodule = AUTH_MODULE;
            $amodule = new $authmodule();
            $viewData->authtitle = $amodule->getTitle();
            $this->sendResponse('deployment_action_stage', $viewData);
        }
        if (($return = RevDeploy::createDeployment($deployment, $deployInfo, $deployHostSearch, $deployStaticHostSearch)) === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to write deployment information to data store';
            $this->sendError('generic_error', $viewData);
        }
        if (BUILD_NRPERPM === true) {
            if ($deployInfo['deploystyle'] == 'both' || $deployInfo['deploystyle'] == 'nrpe') {
                $filecontents = NRPERpm::createSpec($deployment);
                NagPhean::init(BEANSTALKD_SERVER, 'nrperpm', true);
                NagPhean::addJob('nrperpm',
                    json_encode(array('deployment' => $deployment, 'data' => $filecontents)),
                    1024, 0, 60);
            }
        }
        unset($_SESSION[$builddeployment]);
        unset($_SESSION['add_deployment']);
        unset($_SESSION['deployment']);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_write', $viewData);
    }

    /**
     * modify_stage - modify deployment stage view / routine 
     * 
     * @access public
     * @return void
     */
    public function modify_stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $deployInfo = RevDeploy::getDeploymentInfo($deployment);
        $deployHostSearches = RevDeploy::getDeploymentHostSearches($deployment);
        $deployStaticHosts = RevDeploy::getDeploymentStaticHosts($deployment);
        if (empty($deployInfo)) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to _fetch deployment information from data store for '.$deployment;
            $this->sendError('generic_error', $viewData);
        }
        $_SESSION[$deployment]['deployments'] = $deployHostSearches;
        $_SESSION[$deployment]['static-deployments'] = $deployStaticHosts;
        if (($return = $this->checkGroupAuth(SUPERMEN)) === false) {
            $viewData->notsupermen = true;
        }
        $viewData->deployInfo = $deployInfo;
        $viewData->action = 'modify_write';
        $viewData->locations = $this->_fetchLocations();
        $viewData->inputs = $this->_fetchInputs();
        $viewData->deployment = $deployment;
        $viewData->crepos = RevDeploy::getCommonRepos();
        $authmodule = AUTH_MODULE;
        $amodule = new $authmodule();
        $viewData->authtitle = $amodule->getTitle();
        $this->sendResponse('deployment_action_stage', $viewData);
    }

    /**
     * modify_write - modify deployment write information view / routine
     * 
     * @access public
     * @return void
     */
    public function modify_write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $deployInfo = $this->_fetchDeploymentInfo('modify_write');
        $deployHostSearch = isset($_SESSION[$deployment]['deployments'])?$_SESSION[$deployment]['deployments']:array();
        $deployStaticHosts = isset($_SESSION[$deployment]['static-deployments'])?$_SESSION[$deployment]['static-deployments']:array();
        if (($return = RevDeploy::modifyDeployment($deployment, $deployInfo, $deployHostSearch, $deployStaticHosts)) === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to write deployment information to data store';
            $this->sendError('generic_error', $viewData);
        }
        unset($_SESSION[$deployment]['deployments']);
        unset($_SESSION[$deployment]['static-deployments']);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_write', $viewData);
    }

    /**
     * add_hostSearch - add dynamic host location / parameter 
     * 
     * @access public
     * @return void
     */
    public function add_hostSearch()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $location = $this->getParam('loc');
        $param = $this->getParam('param');
        $subdeploy = $this->getParam('subdeploy');
        $note = $this->getParam('note');
        if ((empty($param)) || ($location == 'empty') || ($param == 'Select a Parameter')) {
            $this->sendResponse('deployment_search_window', $viewData);
        }
        if ((!isset($_SESSION[$deployment]['deployments'])) || (!is_array($_SESSION[$deployment]['deployments']))) {
            $_SESSION[$deployment]['deployments'] = array();
        }
        if (preg_match("/,/", $param)) {
            $tmpParams = preg_split("/\s?,\s?/", $param);
            foreach ($tmpParams as $tmpParam) {
                $md5_locparam = md5($location.':'.$tmpParam.':'.$subdeploy);
                $_SESSION[$deployment]['deployments'][$md5_locparam]['location'] = $location;
                $_SESSION[$deployment]['deployments'][$md5_locparam]['srchparam'] = $tmpParam;
                $_SESSION[$deployment]['deployments'][$md5_locparam]['subdeployment'] = $subdeploy;
                $_SESSION[$deployment]['deployments'][$md5_locparam]['note'] = $note;
            }
        } else {
            $md5_locparam = md5($location.':'.$param.':'.$subdeploy);
            $_SESSION[$deployment]['deployments'][$md5_locparam]['location'] = $location;
            $_SESSION[$deployment]['deployments'][$md5_locparam]['srchparam'] = $param;
            $_SESSION[$deployment]['deployments'][$md5_locparam]['subdeployment'] = $subdeploy;
            $_SESSION[$deployment]['deployments'][$md5_locparam]['note'] = $note;
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window', $viewData);
    }

    /**
     * del_hostSearch - delete dynamic host location / parameter 
     * 
     * @access public
     * @return void
     */
    public function del_hostSearch()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $md5_locparam = $this->getParam('lp');
        unset($_SESSION[$deployment]['deployments'][$md5_locparam]);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window', $viewData);
    }

    /**
     * view_hostSearch - view dynamic host information 
     * 
     * @access public
     * @return void
     */
    public function view_hostSearch()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window', $viewData);
    }

    /**
     * view_static_hostSearch - view static hosts 
     * 
     * @access public
     * @return void
     */
    public function view_static_hostSearch()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window_static', $viewData);
    }

    /**
     * del_static_hostSearch - delete static host information for host / location provided 
     * 
     * @access public
     * @return void
     */
    public function del_static_hostSearch()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $locparam = $this->getParam('lp');
        unset($_SESSION[$deployment]['static-deployments'][$locparam]);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window_static', $viewData);
    }

    /**
     * add_static_host - add static host to deployment information 
     * 
     * @access public
     * @return void
     */
    public function add_static_host()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $host = $this->getParam('host');
        $ip = $this->getParam('ip');
        $subdeploy = $this->getParam('subdeploy');
        if (($host === false) || ($ip === false)) {
            $this->sendResponse('deployment_search_window_static', $viewData);
        }
        if (!preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
            $this->sendResponse('deployment_search_window_static', $viewData);
        }
        $encIP = NagMisc::encodeIP($ip);
        if ((!isset($_SESSION[$deployment]['static-deployments'])) || (!is_array($_SESSION[$deployment]['static-deployments']))) {
            $_SESSION[$deployment]['static-deployments'] = array();
        }
        $_SESSION[$deployment]['static-deployments'][$encIP]['host'] = $host;
        $_SESSION[$deployment]['static-deployments'][$encIP]['ip'] = $ip;
        $_SESSION[$deployment]['static-deployments'][$encIP]['subdeployment'] = $subdeploy;
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window_static', $viewData);
    }

    /**
     * add_static_host_csv - add static hosts via csv inject 
     * 
     * @access public
     * @return void
     */
    public function add_static_host_csv() 
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $contents = $this->fetchUploadedFile('staticcsvfile');
        if ($contents === false) {
            $this->sendResponse('deployment_search_window_static', $viewData);
        }
        $lines = explode("\n", $contents);
        if ((!isset($_SESSION[$deployment]['static-deployments'])) || (!is_array($_SESSION[$deployment]['static-deployments']))) {
            $_SESSION[$deployment]['static-deployments'] = array();
        }
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $tmpArray = preg_split('/,\s?/', $line);
            $encIP = NagMisc::encodeIP($tmpArray[1]);
            $_SESSION[$deployment]['static-deployments'][$encIP]['host'] = $tmpArray[0];
            $_SESSION[$deployment]['static-deployments'][$encIP]['ip'] = $tmpArray[1];
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_search_window_static', $viewData);
    }

    /**
     * _fetchLocations - fetch predetermined location information 
     * 
     * @access private
     * @return void
     */
    private function _fetchLocations()
    {
        $locations = array();
        if (!defined('DEPLOYMENT_MODULES')) return $locations;
        $modules = explode(',', DEPLOYMENT_MODULES);
        if (empty($modules[0])) return $locations;
        foreach ($modules as $module) {
            $tmpObj = new $module;
            $return = $tmpObj->getList();
            if ($return == null) continue;
            if (preg_match("/^AWSEC2/", $module)) {
                foreach ($return as $key => $value) {
                    $locations[$key] = $value;
                }
            }
            else {
                $locations[$module] = $return;
            }
        }
        return $locations;
    }

    /**
     * _fetchInputs - fetch deployment glob / param input locations 
     * 
     * @access private
     * @return void
     */
    private function _fetchInputs()
    {
        $inputs = array();
        if (!defined('INPUT_MODULES')) return $inputs;
        $modules = explode(',', INPUT_MODULES);
        if (empty($modules[0])) return $inputs;
        foreach ($modules as $module) {
            $tmpObj = new $module;
            $return = $tmpObj->getInput();
            if ($return == null) continue;
            if (!is_array($return)) {
                $inputs[$module] = $return;
            } else {
                foreach ($return as $key => $value) {
                    if ((!isset($inputs[$key])) || (empty($inputs[$key]))) {
                        $inputs[$key] = $value;
                    }
                }
            }
        }
        return $inputs;
    }

    /**
     * show_configs_stage - show configuration files stage view / routine 
     * 
     * @access public
     * @return void
     */
    public function show_configs_stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $viewData->revs = RevDeploy::getDeploymentRevs($deployment);
        $viewData->allrevs = RevDeploy::getDeploymentAllRevs($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_show_configs_stage', $viewData);
    }

    /**
     * show_configs - show configuration files view / routine 
     * 
     * @access public
     * @return void
     */
    public function show_configs()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $revision = $this->getParam('revision');
        if ($revision === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect revision parameter';
            $this->sendError('generic_error', $viewData);
        } elseif (RevDeploy::existsDeploymentRev($deployment, $revision) !== true) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect deployment revision in datastore';
            $this->sendError('generic_error', $viewData);
        }
        $subdeployment = $this->getParam('subdeployment');
        $islocked = NagTester::getDeploymentBuildLock($deployment, $subdeployment, $revision);
        if ($islocked === false) $viewData->running = false;
        else $viewData->running = true;
        $deploymentResults = NagTester::getDeploymentBuildInfo($deployment, $subdeployment, $revision);
        if ((empty($deploymentResults)) && ($islocked !== false)) {
            $viewData->jobadded = false;
            $deploymentResults['timestamp'] = '0000000000';
            $deploymentResults['output'] = base64_encode('Job is currently processing, this page will reload automatically...');
        } elseif ((empty($deploymentResults)) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(array('deployment' => $deployment, 'revision' => $revision, 'type' => 'build', 'subdeployment' => $subdeployment)),
                1024, 0, 900);
            $viewData->jobadded = true;
            $deploymentResults['timestamp'] = '0000000000';
            $deploymentResults['output'] = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['timestamp'])) && 
            ($deploymentResults['timestamp'] < time() - 60) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(array('deployment' => $deployment, 'revision' => $revision, 'type' => 'build', 'subdeployment' => $subdeployment)),
                1024, 0, 900);
            $viewData->jobadded = true;
            $deploymentResults['output'] = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['subdeployment'])) &&
            ($deploymentResults['subdeployment'] != $subdeployment) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(array('deployment' => $deployment, 'revision' => $revision, 'type' => 'build', 'subdeployment' => $subdeployment)),
                1024, 0, 900);
            $viewData->jobadded = true;
            $deploymentResults['output'] = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((!empty($deploymentResults)) && ($islocked !== false)) {
            $deploymentResults['output'] = base64_encode('Job is currently processing, this page will reload automatically...');
            $viewData->jobadded = false;
        } else {
            $viewData->jobadded = false;
        }
        if ((isset($deploymentResults['starttime'])) && ($deploymentResults['timestamp'] != '0000000000')) {
            $deploymentResults['totaltime'] = $deploymentResults['timestamp'] - $deploymentResults['starttime'];
        }
        if (($viewData->jobadded === true) || ($viewData->running === true)) {
            $viewData->refresh = 15;
        }
        $viewData->results = $deploymentResults;
        $viewData->deployment = $deployment;
        $viewData->subdeployment = $subdeployment;
        $viewData->revision = $revision;
        $viewData->action = 'show_configs';
        $viewData->controller = 'deployment';
        $this->sendResponse('deployment_view_configs', $viewData);
    }

    /**
     * test_configs_stage 
     * 
     * @access public
     * @return void
     */
    public function test_configs_stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $viewData->revs = RevDeploy::getDeploymentRevs($deployment);
        $viewData->allrevs = RevDeploy::getDeploymentAllRevs($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_test_configs_stage', $viewData);
    }

    /**
     * test_configs - test configuration view / routine 
     * 
     * @access public
     * @return void
     */
    public function test_configs()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $revision = $this->getParam('revision');
        if ($revision === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect revision parameter';
            $this->sendError('generic_error', $viewData);
        } elseif (RevDeploy::existsDeploymentRev($deployment, $revision) !== true) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect deployment revision in datastore';
            $this->sendError('generic_error', $viewData);
        }
        $subdeployment = $this->getParam('subdeployment');
        $islocked = NagTester::getDeploymentTestLock($deployment, $subdeployment, $revision);
        if ($islocked === false) $viewData->running = false;
        else $viewData->running = true;
        $deploymentResults = NagTester::getDeploymentTestInfo($deployment, $subdeployment, $revision);
        if ((empty($deploymentResults)) && ($islocked !== false)) {
            $viewData->jobadded = false;
            $deploymentResults['timestamp'] = '0000000000';
            $deploymentResults['output'] = base64_encode('Job is currently processing, this page will reload automatically...');
        } elseif ((empty($deploymentResults)) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'revision' => $revision, 'type' => 'test', 'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $deploymentResults['timestamp'] = '0000000000';
            $deploymentResults['output'] = base64_encode('Test Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['timestamp'])) && 
            ($deploymentResults['timestamp'] < time() - 60) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'revision' => $revision, 'type' => 'test', 'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $deploymentResults['output'] = base64_encode('Test Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['subdeployment'])) &&
            ($deploymentResults['subdeployment'] != $subdeployment) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'revision' => $revision, 'type' => 'test', 'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $deploymentResults['output'] = base64_encode('Test Process has been initiated, this page will reload automatically...');
        } elseif ((!empty($deploymentResults)) && ($islocked !== false)) {
            $deploymentResults['output'] = base64_encode('Job is currently processing, this page will reload automatically...');
            $viewData->jobadded = false;
        } else {
            $viewData->jobadded = false;
        }
        if ((isset($deploymentResults['starttime'])) && ($deploymentResults['timestamp'] != '0000000000')) {
            $deploymentResults['totaltime'] = $deploymentResults['timestamp'] - $deploymentResults['starttime'];
            if ($deploymentResults['totaltime'] > 180) {
                $viewData->refresh = 60;
                $viewData->harsh = 5;
            } elseif ($deploymentResults['totaltime'] > 90) {
                $viewData->refresh = 45;
                $viewData->harsh = 4;
            } elseif ($deploymentResults['totaltime'] > 45) {
                $viewData->refresh = 30;
                $viewData->harsh = 3;
            } elseif ($deploymentResults['totaltime'] > 30) {
                $viewData->refresh = 15;
                $viewData->harsh = 2;
            } else {
                $viewData->refresh = 10;
                $viewData->harsh = 1;
            }
        } else {
            /* Unknown at this point... */
            $viewData->refresh = 10;
            $viewData->harsh = 1;
        }
        $viewData->test_output = $deploymentResults;
        $viewData->deployment = $deployment;
        $viewData->revision = $revision;
        $viewData->subdeployment = $subdeployment;
        $viewData->action = 'test_configs';
        $viewData->controller = 'deployment';
        $this->sendResponse('deployment_test_configs', $viewData);
    }

    /**
     * diff_configs_stage 
     * 
     * @access public
     * @return void
     */
    public function diff_configs_stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $viewData->revs = RevDeploy::getDeploymentRevs($deployment);
        $viewData->allrevs = RevDeploy::getDeploymentAllRevs($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_diff_configs_stage', $viewData);
    }


    /**
     * diff_configs - diff configurations view / routine 
     * 
     * @access public
     * @return void
     */
    public function diff_configs()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $fromrev = $this->getParam('fromrev');
        $torev = $this->getParam('torev');
        if ($fromrev === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect revision to diff from...';
            $this->sendError('generic_error', $viewData);
        } elseif ($torev === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect revision to diff too...';
            $this->sendError('generic_error', $viewData);
        } elseif ($fromrev == $torev) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to diff the revisions when they are the same revision...';
            $this->sendError('generic_error', $viewData);
        }
        $subdeployment = $this->getParam('subdeployment');
        $islocked = NagTester::getDeploymentBuildLock($deployment, $subdeployment, $fromrev);
        if ($islocked === false) {
            $islocked = NagTester::getDeploymentBuildLock($deployment, $subdeployment, $torev);
            if ($islocked === false) $viewData->running = false;
            else $viewData->running = true;
        } else {
            $viewData->running = true;
        }
        $deploymentResults = NagTester::getDeploymentDiffInfo($deployment, $subdeployment);
        if ((empty($deploymentResults)) && ($islocked !== false)) {
            $viewData->jobadded = false;
            $deploymentResults['timestamp'] = '0000000000';
            $viewData->output = base64_encode('Job is currently processing, this page will reload automatically...');
        } elseif ((empty($deploymentResults)) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'type' => 'diff',
                        'fromrev' => $fromrev, 'torev' => $torev,
                        'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $deploymentResults['timestamp'] = '0000000000';
            $viewData->output = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['timestamp'])) && 
            ($deploymentResults['timestamp'] < time() - 60) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'type' => 'diff',
                        'fromrev' => $fromrev, 'torev' => $torev,
                        'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $viewData->output = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['subdeployment'])) &&
            ($deploymentResults['subdeployment'] != $subdeployment) && ($islocked === false)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'type' => 'diff',
                        'fromrev' => $fromrev, 'torev' => $torev,
                        'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $viewData->output = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['fromrev'])) &&
            ($deploymentResults['fromrev'] != $fromrev) && ($islocked === false)) { 
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'type' => 'diff',
                        'fromrev' => $fromrev, 'torev' => $torev,
                        'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $viewData->output = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((isset($deploymentResults['torev'])) &&
            ($deploymentResults['torev'] != $torev) && ($islocked === false)) { 
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(
                BEANSTALKD_TUBE,
                json_encode(
                    array('deployment' => $deployment, 'type' => 'diff',
                        'fromrev' => $fromrev, 'torev' => $torev,
                        'subdeployment' => $subdeployment)
                ),
                1024, 0, 900
            );
            $viewData->jobadded = true;
            $viewData->output = base64_encode('Build Process has been initiated, this page will reload automatically...');
        } elseif ((!empty($deploymentResults)) && ($islocked !== false)) {
            $viewData->output = base64_encode('Job is currently processing, this page will reload automatically...');
            $viewData->jobadded = false;
        } else {
            $viewData->jobadded = false;
            $configdata = json_decode($deploymentResults['configs'], true);
            $deploymentData = $deploymentResults;
            unset($deploymentData['configs']);
            unset($deploymentData['output']);
            $viewData->meta = $deploymentData;
            $viewData->diff = NagDiff::diff($configdata['nagiosconfs']['from'], $configdata['nagiosconfs']['to']);
            $viewData->nagplugins = NagDiff::diff($configdata['plugins']['nagios']['from'], $configdata['plugins']['nagios']['to']);
            $viewData->cplugins = NagDiff::diff($configdata['plugins']['nrpe']['core']['from'], $configdata['plugins']['nrpe']['core']['to']);
            $viewData->splugins = NagDiff::diff($configdata['plugins']['nrpe']['sup']['from'], $configdata['plugins']['nrpe']['sup']['to']);
        }
        if ((isset($deploymentResults['starttime'])) && ($deploymentResults['timestamp'] != '0000000000')) {
            $viewData->meta['totaltime'] = $deploymentResults['timestamp'] - $deploymentResults['starttime'];
        }
        if (($viewData->jobadded === true) || ($viewData->running === true)) {
            $viewData->refresh = 15;
        }
        $viewData->deployment = $deployment;
        $viewData->subdeployment = $subdeployment;
        $viewData->action = 'diff_configs';
        $viewData->controller = 'deployment';
        $viewData->fromrev = $fromrev;
        $viewData->torev = $torev;
        $this->sendResponse('deployment_diff_configs', $viewData);
    }

    /**
     * chg_configs - change configuration stage view / routine 
     * 
     * @access public
     * @return void
     */
    public function chg_configs()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $viewData->revs = RevDeploy::getDeploymentRevs($deployment);
        $viewData->allrevs = RevDeploy::getDeploymentAllRevs($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_change_configs', $viewData);
    }

    /**
     * chg_configs_write - change configuration write view / routine 
     * 
     * @access public
     * @return void
     */
    public function chg_configs_write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $fromRev = $this->getParam('currrev');
        if ($fromRev === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect current revision for deployment';
            $this->sendError('generic_error', $viewData);
        }
        $toRev = $this->getParam('revision');
        if (($toRev === false) || ($toRev == '----')) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect revision to move deployment to';
            $this->sendError('generic_error', $viewData);
        }
        $note = $this->getParam('note');
        if ($note === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect update note for deployment revision change';
            $this->sendError('generic_error', $viewData);
        }
        RevDeploy::setDeploymentRevs($deployment, $fromRev, $toRev, $note);
        $viewData->deployment = $deployment;
        $viewData->from = $fromRev;
        $viewData->to = $toRev;
        $this->sendResponse('deployment_change_configs_write', $viewData);
    }

    /**
     * reset_ftr_rev - reset future revision to match current revision information 
     * 
     * @access public
     * @return void
     */
    public function reset_ftr_rev()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $this->checkGroupAuth($deployment);
        $currrev = RevDeploy::getDeploymentRev($deployment);
        $nextrev = RevDeploy::getDeploymentNextRev($deployment);
        if ($currrev == $nextrev) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to process request, no new revision exists to reset';
            $this->sendError('generic_error', $viewData);
        }
        CopyDeploy::resetDeploymentRevision($deployment, $currrev, $nextrev);
        $viewData->deployment = $deployment;
        $viewData->currrev = $currrev;
        $viewData->nextrev = $nextrev;
        $this->sendResponse('deployment_rfr', $viewData);
    }

    /**
     * del_stage - delete deployment stage view / routine 
     * 
     * @access public
     * @return void
     */
    public function del_stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        if (!RevDeploy::existsDeployment($deployment)) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to find deployment specified in data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_del_stage', $viewData);
    }

    /**
     * del_write - delete deployment write view / routine
     * 
     * @access public
     * @return void
     */
    public function del_write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        if (!RevDeploy::existsDeployment($deployment)) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to find deployment specified in data store';
            $this->sendError('generic_error', $viewData);
        }
        $this->checkGroupAuth($deployment);
        RevDeploy::deleteDeployment($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_del_write', $viewData);
    }

    /**
     * del_rev_stage - delete revision stage view / routine
     * 
     * @access public
     * @return void
     */
    public function del_rev_stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        if (!RevDeploy::existsDeployment($deployment)) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to find deployment specified in data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->revs = RevDeploy::getDeploymentRevs($deployment);
        $viewData->allrevs = RevDeploy::getDeploymentAllRevs($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_del_rev_stage', $viewData);
    }

    /**
     * del_rev_write - delete revision write view / routine 
     * 
     * @access public
     * @return void
     */
    public function del_rev_write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        if (!RevDeploy::existsDeployment($deployment)) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to find deployment specified in data store';
            $this->sendError('generic_error', $viewData);
        }
        $this->checkGroupAuth($deployment);
        $revision = $this->getParam('revision');
        if ($revision === false) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to detect revision number to delete';
            $this->sendError('generic_error', $viewData);
        }
        RevDeploy::deleteDeploymentRev($deployment, $revision);
        $viewData->deployment = $deployment;
        $viewData->revision = $revision;
        $this->sendResponse('deployment_del_rev_write', $viewData);
    }

    /**
     * view_revlog - view revision log information 
     * 
     * @access public
     * @return void
     */
    public function view_revlog()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        if (!RevDeploy::existsDeployment($deployment)) {
            $viewData->header = $this->getErrorHeader('deployment_error');
            $viewData->error = 'Unable to find deployment specified in data store';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->revisions = RevDeploy::getAuditLog($deployment);
        $viewData->deployment = $deployment;
        $this->sendResponse('deployment_view_revlog', $viewData);
    }

    /**
     * view_dynamic_matches - view dynamic matches for global negate regex 
     * 
     * @access public
     * @return void
     */
    public function view_dynamic_matches()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('deployment_error');
        $nregex = $this->getParam('nregex');
        if ($nregex === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect negate regex specified in post params';
            $this->sendResponse('generic_error', $viewData);
        }
        $hosts = RevDeploy::getDeploymentHosts($deployment);
        $resulthosts = array();
        foreach ($hosts as $host => $hArray) {
            if (preg_match("/$nregex/", $host)) {
                array_push($resulthosts, $host);
            }
        }
        sort($resulthosts);
        $viewData = $resulthosts;
        $this->sendResponse('deployment_view_dynamic_matches', $viewData);
    }

}

