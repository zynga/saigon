<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SvcEscController extends Controller {

    private function fetchSvcEscInfo($deployment, $action, $modrevision) {
        $svcEscInfo = array();
        $svcEscInfo['name'] = $this->getParam('svcEscName');
        $svcEscInfo['service_description'] = $this->getParam('service');
        $svcEscInfo['contacts'] = $this->getParam('contacts');
        $svcEscInfo['contact_groups'] = $this->getParam('contactgrps');
        $svcEscInfo['first_notification'] = $this->getParam('firstnotif');
        $svcEscInfo['last_notification'] = $this->getParam('lastnotif');
        $svcEscInfo['notification_interval'] = $this->getParam('notifinterval');
        $svcEscInfo['escalation_period'] = $this->getParam('timeperiod');
        $svcEscInfo['escalation_options'] = $this->getParam('escopts');
        if ($svcEscInfo['name'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect name specified for service escalation';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = $action;
            $this->sendResponse('svc_esc_action_stage', $viewData);
        } else if ($svcEscInfo['service_description'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect service specified for escalation';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = $action;
            $this->sendResponse('svc_esc_action_stage', $viewData);
        } else if (($svcEscInfo['contacts'] === false) && ($svcEscInfo['contact_groups'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect contacts and contact groups, make sure at least a contact or contact group is specified';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = $action;
            $this->sendResponse('svc_esc_action_stage', $viewData);
        } else if ($svcEscInfo['first_notification'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect first notification threshold';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = $action;
            $this->sendResponse('svc_esc_action_stage', $viewData);
        } else if ($svcEscInfo['last_notification'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect last notification threshold';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = $action;
            $this->sendResponse('svc_esc_action_stage', $viewData);
        } else if ($svcEscInfo['notification_interval'] === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect notification interval';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = $action;
            $this->sendResponse('svc_esc_action_stage', $viewData);
        }
        if ($svcEscInfo['contacts'] !== false) $svcEscInfo['contacts'] = implode(',', $svcEscInfo['contacts']);
        if ($svcEscInfo['contact_groups'] !== false) $svcEscInfo['contact_groups'] = implode(',', $svcEscInfo['contact_groups']);
        if ($svcEscInfo['escalation_options'] !== false) $svcEscInfo['escalation_options'] = implode(',', $svcEscInfo['escalation_options']);
        return $svcEscInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcescs = RevDeploy::getDeploymentSvcEscalationswInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('svc_esc_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('svc_esc_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $svcEscInfo = $this->fetchSvcEscInfo($deployment,'add_write', $modrevision);
        $svcEscName = $svcEscInfo['name'];
        if (RevDeploy::existsDeploymentSvcEscalation($deployment, $svcEscName, $modrevision) === true) {
            $viewData->error = 'Service Escalation information exists for '.$svcEscName.' in '.$deployment.' Deployment';
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = 'add_write';
            $this->sendResponse('svc_esc_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcEscalation($deployment, $svcEscName, $svcEscInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to write service esclation information for '.$svcEscName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svcesc = $svcEscName;
        $this->sendResponse('svc_esc_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $svcEscName = $this->getParam('svcEsc');
        if ($svcEscName === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to detect service escalation specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcescinfo = RevDeploy::getDeploymentSvcEscalation($deployment, $svcEscName, $modrevision);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $this->sendResponse('svc_esc_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $svcEscInfo = $this->fetchSvcEscInfo($deployment, 'modify_write', $modrevision);
        $svcEscName = $svcEscInfo['name'];
        if (RevDeploy::modifyDeploymentSvcEscalation($deployment, $svcEscName, $svcEscInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to write service escalation information for '.$svcEscName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->svcesc = $svcEscName;
        $this->sendResponse('svc_esc_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $svcEscName = $this->getParam('svcEsc');
        if ($svcEscName === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to detect service escalation specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcEscInfo = RevDeploy::getDeploymentSvcEscalation($deployment, $svcEscName, $modrevision);
        $viewData->svcescinfo = $svcEscInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('svc_esc_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $svcEscName = $this->getParam('svcEsc');
        if ($svcEscName === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to detect service escalation specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentSvcEscalation($deployment, $svcEscName, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->svcesc = $svcEscName;
        $this->sendResponse('svc_esc_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $svcEscName = $this->getParam('svcEsc');
        if ($svcEscName === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->svcescinfo = RevDeploy::getDeploymentSvcEscalation($deployment, $svcEscName, $modrevision);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_esc_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $svcEscName = $this->getParam('svcEsc');
        if ($svcEscName === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to detect service specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $commonRepo = RevDeploy::getDeploymentCommonRepo($deployment);
        $commonrevision = RevDeploy::getDeploymentRev($commonRepo);
        $viewData->svcescinfo = RevDeploy::getDeploymentSvcEscalation($commonRepo, $svcEscName, $commonrevision);
        $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('svc_esc_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('svc_esc_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $svcEscInfo = $this->fetchSvcEscInfo($deployment, 'copy_write', $modrevision);
        $svcEscName = $svcEscInfo['name'];
        if (RevDeploy::existsDeploymentSvcEscalation($deployment, $svcEscName, $modrevision) === true) {
            $viewData->error = 'Service Escalation information exists for '.$svcEscName.' in '.$deployment.' Deployment';
            $viewData->svcs = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgrps = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->svcescinfo = $svcEscInfo;
            $viewData->deployment = $deployment;
            $viewData->action = 'copy_write';
            $this->sendResponse('svc_esc_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentSvcEscalation($deployment, $svcEscName, $svcEscInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('svc_esc_error');
            $viewData->error = 'Unable to write service escalation information for '.$svcEscName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->svcesc = $svcEscName;
        $this->sendResponse('svc_esc_write', $viewData);
    }

}
