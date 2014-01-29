<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NGNTController extends Controller {

    private function fetchNGNTInfo($deployment, $action, $modrevision) {
        $ngntInfo = array();
        $ngntInfo['name'] = $this->getParam('nodeTemp');
        $ngntInfo['type'] = $this->getParam('ngnttype');
        if ($ngntInfo['type'] === false) {
            $viewData = new ViewData();
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect Node Templatizer Type';
            $this->sendError('generic_error', $viewData);
        } elseif ($ngntInfo['type'] == 'dynamic') {
            $ngntInfo['regex'] = $this->getParam('nodeRegex');
            $ngntInfo['nregex'] = $this->getParam('nodeNegateRegex');
            $ngntInfo['hostgroup'] = $this->getParam('hostgroup');
            $ngntInfo['stdtemplate'] = $this->getParam('stdtemplate');
            $ngntInfo['nservices'] = $this->getParam('nservices');
            $ngntInfo['subdeployment'] = $this->getParam('subdeployment');
        } elseif ($ngntInfo['type'] == 'static') {
            $ngntInfo['selhosts'] = $this->getParam('selhosts');
            $ngntInfo['hostgroup'] = $this->getParam('hostgroup');
            $ngntInfo['stdtemplate'] = $this->getParam('stdtemplate');
            $ngntInfo['nservices'] = $this->getParam('nservices');
            $ngntInfo['subdeployment'] = $this->getParam('subdeployment');
        } elseif ($ngntInfo['type'] == 'standard') {
            $ngntInfo['hostgroup'] = null;
        }
        $ngntInfo['services'] = $this->getParam('services');
        $ngntInfo['hosttemplate'] = $this->getParam('hosttemplate');
        if ($ngntInfo['name'] === false) {
            $viewData = new ViewData();
            $viewData->nodeInfo = $ngntInfo;
            $viewData->error = 'Unable to detect Template Name';
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            if ($ngntInfo['type'] == 'dynamic') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
                $viewData->ngnttype = 'dynamic';
                $this->sendResponse('ngnt_action_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'static') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $viewData->ngnttype = 'static';
                $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
                $this->sendResponse('ngnt_action_static_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'standard') {
                $viewData->ngnttype = 'standard';
                $this->sendResponse('ngnt_action_standard_stage', $viewData);
            }
        } elseif (preg_match_all('/[^a-zA-Z0-9_-]/s', $ngntInfo['name'], $forbidden)) {
            $viewData = new ViewData();
            $viewData->nodeInfo = $ngntInfo;
            $viewData->error = 'Unable to use template name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            if ($ngntInfo['type'] == 'dynamic') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
                $viewData->ngnttype = 'dynamic';
                $this->sendResponse('ngnt_action_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'static') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $viewData->ngnttype = 'static';
                $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
                $this->sendResponse('ngnt_action_static_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'standard') {
                $viewData->ngnttype = 'standard';
                $this->sendResponse('ngnt_action_standard_stage', $viewData);
            }
        } elseif (($ngntInfo['type'] == 'dynamic') && ($ngntInfo['regex'] === false)) {
            $viewData = new ViewData();
            $viewData->nodeInfo = $ngntInfo;
            $viewData->error = 'Unable to detect Template Regex Search Param';
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
            $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
            $viewData->ngnttype = 'dynamic';
            $this->sendResponse('ngnt_action_stage', $viewData);
        } elseif (($ngntInfo['type'] == 'static') && ($ngntInfo['selhosts'] === false)) {
            $viewData = new ViewData();
            $viewData->nodeInfo = $ngntInfo;
            $viewData->error = 'Unable to detect Select Hosts to use for Static Template';
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
            $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
            $viewData->ngnttype = 'static';
            $this->sendResponse('ngnt_action_static_stage', $viewData);
        } elseif ((empty($ngntInfo['services'])) &&
                (($ngntInfo['hostgroup'] == null) || ($ngntInfo['hostgroup'] == 'null')) &&
                (($ngntInfo['hosttemplate'] == null) || ($ngntInfo['hosttemplate'] == 'null')) &&
                (($ngntInfo['stdtemplate'] == null) || ($ngntInfo['stdtemplate'] == 'null'))) {
            $viewData = new ViewData();
            $viewData->nodeInfo = $ngntInfo;
            $viewData->error = 'Unable to detect any service, hostgroup or hosttemplate or standardtemplate to apply to Node Template';
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            if ($ngntInfo['type'] == 'dynamic') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
                $viewData->ngnttype = 'dynamic';
                $this->sendResponse('ngnt_action_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'static') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $viewData->ngnttype = 'static';
                $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
                $this->sendResponse('ngnt_action_static_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'standard') {
                $viewData->error = 'Unable to detect any service or host template to apply to Standard Node Template';
                $viewData->ngnttype = 'standard';
                $this->sendResponse('ngnt_action_standard_stage', $viewData);
            }
        }
        if (($ngntInfo['hostgroup'] == null) || ($ngntInfo['hostgroup'] == 'null')) {
            unset($ngntInfo['hostgroup']);
        }
        if (($ngntInfo['hosttemplate'] == null) || ($ngntInfo['hosttemplate'] == 'null')) {
            unset($ngntInfo['hosttemplate']);
        }
        if ((isset($ngntInfo['nregex'])) && ($ngntInfo['nregex'] === false)) {
            unset($ngntInfo['nregex']);
        }
        if (!empty($ngntInfo['services'])) {
            $ngntInfo['services'] = implode(',', $ngntInfo['services']);
        } else {
            unset($ngntInfo['services']);
        }
        if (!empty($ngntInfo['nservices'])) {
            $ngntInfo['nservices'] = implode(',', $ngntInfo['nservices']);
        } else {
            unset($ngntInfo['nservices']);
        }
        return $ngntInfo;
    }

    public function add_dynamic_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
        $viewData->deployment = $deployment;
        $viewData->ngnttype = 'dynamic';
        $viewData->action = 'add_write';
        $this->sendResponse('ngnt_action_stage', $viewData);
    }

    public function add_static_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
        $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
        $viewData->ngnttype = 'static';
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('ngnt_action_static_stage', $viewData);
    }

    public function add_standard_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->ngnttype = 'standard';
        $viewData->action = 'add_write';
        $this->sendResponse('ngnt_action_standard_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $ngntInfo = $this->fetchNGNTInfo($deployment, 'add_write', $modrevision);
        if (RevDeploy::existsDeploymentNodeTemplate($deployment, $ngntInfo['name'], $modrevision) === true) {
            $viewData->error = 'Unable to process request, a node template with the same name already exists';
            $viewData->nodeInfo = $ngntInfo;
            $this->sendResponse('ngnt_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentNodeTemplate($deployment, $ngntInfo['name'], $ngntInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to write node template information for '.$ngntInfo['name'].' to '.$deployment;
            $this->sendError('generic_error', $viewData);
        }
        $viewData->nodeTemp = $ngntInfo['name'];
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_write', $viewData);
    }

    public function modify_dynamic_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        if (isset($viewData->nodeInfo['services'])) {
            $viewData->nodeInfo['services'] = explode(',', $viewData->nodeInfo['services']);
        }
        if (isset($viewData->nodeInfo['nservices'])) {
            $viewData->nodeInfo['nservices'] = explode(',', $viewData->nodeInfo['nservices']);
        }
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
        $viewData->action = 'modify_write';
        $viewData->ngnttype = 'dynamic';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_action_stage', $viewData);
    }

    public function modify_static_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        if (isset($viewData->nodeInfo['services'])) {
            $viewData->nodeInfo['services'] = explode(',', $viewData->nodeInfo['services']);
        }
        if (isset($viewData->nodeInfo['nservices'])) {
            $viewData->nodeInfo['nservices'] = explode(',', $viewData->nodeInfo['nservices']);
        }
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
        $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
        $viewData->action = 'modify_write';
        $viewData->ngnttype = 'static';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_action_static_stage', $viewData);
    }

    public function modify_standard_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        if (isset($viewData->nodeInfo['services'])) {
            $viewData->nodeInfo['services'] = explode(',', $viewData->nodeInfo['services']);
        }
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->action = 'modify_write';
        $viewData->ngnttype = 'standard';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_action_standard_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $ngntInfo = $this->fetchNGNTInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentNodeTemplate($deployment, $ngntInfo['name'], $ngntInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to write node template information for '.$ngntInfo['name'].' to '.$deployment;
            $this->sendError('generic_error', $viewData);
        }
        $viewData->nodeTemp = $ngntInfo['name'];
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_write', $viewData);
    }

    public function copy_dynamic_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        if (isset($viewData->nodeInfo['services'])) {
            $viewData->nodeInfo['services'] = explode(',', $viewData->nodeInfo['services']);
        }
        if (isset($viewData->nodeInfo['nservices'])) {
            $viewData->nodeInfo['nservices'] = explode(',', $viewData->nodeInfo['nservices']);
        }
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
        $viewData->action = 'copy_write';
        $viewData->ngnttype = 'dynamic';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_action_stage', $viewData);
    }

    public function copy_static_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        if (isset($viewData->nodeInfo['services'])) {
            $viewData->nodeInfo['services'] = explode(',', $viewData->nodeInfo['services']);
        }
        if (isset($viewData->nodeInfo['nservices'])) {
            $viewData->nodeInfo['nservices'] = explode(',', $viewData->nodeInfo['nservices']);
        }
        $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
        $viewData->stdtemplates = RevDeploy::getDeploymentStandardTemplates($deployment, $modrevision, true);
        $viewData->action = 'copy_write';
        $viewData->ngnttype = 'static';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_action_static_stage', $viewData);
    }

    public function copy_standard_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        if (isset($viewData->nodeInfo['services'])) {
            $viewData->nodeInfo['services'] = explode(',', $viewData->nodeInfo['services']);
        }
        $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
        $viewData->action = 'copy_write';
        $viewData->ngnttype = 'standard';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_action_standard_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $ngntInfo = $this->fetchNGNTInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentNodeTemplate($deployment, $ngntInfo['name'], $modrevision) === true) {
            $viewData->error = 'Unable to process request, a node template with the same name already exists';
            $viewData->nodeInfo = $ngntInfo;
            $viewData->services = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->hosttemplates = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $modrevision);
            if ($ngntInfo['type'] == 'dynamic') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $this->sendResponse('ngnt_action_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'static') {
                $viewData->hostgroups = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $modrevision);
                $this->sendResponse('ngnt_action_static_stage', $viewData);
            } elseif ($ngntInfo['type'] == 'standard') {
                $this->sendResponse('ngnt_action_standard_stage', $viewData);
            }
        }
        if (RevDeploy::createDeploymentNodeTemplate($deployment, $ngntInfo['name'], $ngntInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to write node template information for '.$ngntInfo['name'].' to '.$deployment;
            $this->sendError('generic_error', $viewData);
        }
        $viewData->nodeTemp = $ngntInfo['name'];
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_write', $viewData);
    }

    public function del_dynamic_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        $viewData->action = 'del_write';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_view_stage', $viewData);
    }

    public function del_static_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        $viewData->hosts = RevDeploy::getDeploymentHosts($deployment);
        $viewData->action = 'del_write';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_view_static_stage', $viewData);
    }

    public function del_standard_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        $viewData->action = 'del_write';
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $nodeTemp = $this->getParam('nodeTemp');
        if ($nodeTemp === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect node template specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentNodeTemplate($deployment, $nodeTemp, $modrevision);
        $viewData->nodetemp = $nodeTemp;
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_delete', $viewData);
    }

    public function manage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->nodeInfo = RevDeploy::getDeploymentNodeTemplateswInfo($deployment, $modrevision);
        if ($deployment != 'common') {
            $crepo = RevDeploy::getDeploymentCommonRepo($deployment);
            $crev = RevDeploy::getDeploymentRev($crepo);
            $viewData->cstdTemplates = RevDeploy::getDeploymentNodeTemplateswInfo($crepo, $crev);
            $viewData->cdeployment = RevDeploy::getDeploymentCommonRepo($deployment);
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('ngnt_manage', $viewData);
    }

    public function view_dynamic_matches() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('ngnt_error');
        $regex = $this->getParam('regex');
        if ($regex === false) {
            $viewData->header = $this->getErrorHeader('ngnt_error');
            $viewData->error = 'Unable to detect regex specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $nregex = $this->getParam('nregex');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $hosts = RevDeploy::getDeploymentHosts($deployment);
        $globalnegate = RevDeploy::getDeploymentGlobalNegate($deployment);
        $resulthosts = array();
        foreach ($hosts as $host => $hArray) {
            if (($globalnegate !== false) && (preg_match("/$globalnegate/", $host))) continue;
            if (preg_match("/$regex/", $host)) {
                if ($nregex !== false) {
                    if (!preg_match("/$nregex/", $host)) {
                        array_push($resulthosts, $host);
                    }
                } else {
                    array_push($resulthosts, $host);
                }
            }
        }
        sort($resulthosts);
        $viewData = $resulthosts;
        $this->sendResponse('ngnt_view_dynamic_matches', $viewData);
    }

}

