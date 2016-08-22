<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//
 
class ContactGrpController extends Controller {

    private function fetchContactInfo($deployment, $action, $modrevision) {
        $contactInfo = array();
        $contactInfo['contactgroup_name'] = $this->getParam('cgName');
        $contactInfo['alias'] = $this->getParam('cgAlias');
        $contactInfo['members'] = $this->getParam('cgMembers');
        if ($contactInfo['members'] === false) {
            unset($contactInfo['members']);
        }
        $contactInfo['contactgroup_members'] = $this->getParam('cgGroupMembers');
        if ($contactInfo['contactgroup_members'] !== false) {
            unset($contactInfo['contactgroup_members']);
        }
        if (($contactInfo['contactgroup_name'] === false) || ($contactInfo['alias'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect incoming contact parameters, make sure all of your input fields are filled in';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('contact_group_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $contactInfo['contactgroup_name'], $forbidden))
                || (preg_match_all('/[^a-zA-Z0-9_-\s]/s', $contactInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to use contact group name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('contact_group_action_stage', $viewData);
        } else if (($contactInfo['members'] === false) && ($contactInfo['contactgroup_members'] === false)) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to detect members or other contact groups specified for contact';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            $this->sendResponse('contact_group_action_stage', $viewData);
        }
        return $contactInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('contact_group_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('contact_group_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $cgInfo = $this->fetchContactInfo($deployment,'add_write', $modrevision);
        $cgName = $this->getParam('cgName');
        if (RevDeploy::existsDeploymentContactGroup($deployment, $cgName, $modrevision) === true) {
            $viewData->error = 'Contact information exists for '.$cgName.' in '.$deployment.' Deployment';
            $viewData->contactInfo = $cgInfo;
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->action = 'add_write';
            $this->sendResponse('contact_group_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentContactGroup($deployment, $cgName, $cgInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to write contact information for '.$cgName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $cgName;
        $this->sendResponse('contact_group_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cgName = $this->getParam('cgName');
        $cgInfo = RevDeploy::getDeploymentContactGroup($deployment, $cgName, $modrevision);
        $viewData->contactInfo = $cgInfo;
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $this->sendResponse('contact_group_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $cgName = $this->getParam('cgName');
        $cgInfo = $this->fetchContactInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentContactGroup($deployment, $cgName, $cgInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to write contact group information for '.$cgName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->contact = $cgName;
        $this->sendResponse('contact_group_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $cgName = $this->getParam('cgName');
        if ($cgName === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cgInfo = RevDeploy::getDeploymentContactGroup($deployment, $cgName, $modrevision);
        $viewData->contactInfo = $cgInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('contact_group_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $cgName = $this->getParam('cgName');
        if ($cgName === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $cgName;
        $this->checkGroupAuthByDeployment($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if ($deployment != 'common') {
            $cGrpsInfo = RevDeploy::getDeploymentContactGroupswInfo($deployment, $modrevision);
            foreach ($cGrpsInfo as $cGrp => $cGrpInfo) {
                if (empty($cGrpInfo['contactgroup_members'])) continue;
                $members = $cGrpInfo['contactgroup_members'];
                if (($key = array_search($cgName, $members)) !== false) {
                    unset($members[$key]);
                    $members = array_values($members);
                    $cGrpInfo['contactgroup_members'] = $members;
                    RevDeploy::modifyDeploymentContactGroup($deployment, $cGrp, $cGrpInfo, $modrevision);
                }
            }
        } else {
            $deployments = RevDeploy::getDeployments();
            foreach ($deployments as $tmpDeployment) {
                $this->checkDeploymentRevStatus($tmpDeployment);
                $tmpRevision = RevDeploy::getDeploymentNextRev($tmpDeployment);
                $cGrpsInfo = RevDeploy::getDeploymentContactGroupswInfo($tmpDeployment, $tmpRevision);
                foreach ($cGrpsInfo as $cGrp => $cGrpInfo) {
                    if (empty($cGrpInfo['contactgroup_members'])) continue;
                    $members = $cGrpInfo['contactgroup_members'];
                    if (($key = array_search($cgName, $members)) !== false) {
                        unset($members[$key]);
                        $members = array_values($members);
                        $cGrpInfo['contactgroup_members'] = $members;
                        RevDeploy::modifyDeploymentContactGroup($tmpDeployment, $cGrp, $cGrpInfo, $tmpRevision);
                    }
                }
            }
        }
        RevDeploy::deleteDeploymentContactGroup($deployment, $cgName, $modrevision);
        $this->sendResponse('contact_group_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $cgName = $this->getParam('cgName');
        if ($cgName === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contactInfo = RevDeploy::getDeploymentContactGroup($deployment, $cgName, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('contact_group_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $cgName = $this->getParam('cgName');
        if ($cgName === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contactInfo = RevDeploy::getCommonMergedDeploymentContactGroup($deployment, $cgName, $modrevision);
        $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('contact_group_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_group_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cgName = $this->getParam('cgName');
        $cgInfo = $this->fetchContactInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentContactGroup($deployment, $cgName, $modrevision) === true) {
            $viewData->error = 'Contact information exists for '.$cgName.' in '.$deployment.' Deployment';
            $viewData->contactInfo = $cgInfo;
            $viewData->contactgroups = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $modrevision);
            $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
            $viewData->action = 'copy_write';
            $this->sendResponse('contact_group_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentContactGroup($deployment, $cgName, $cgInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_group_error');
            $viewData->error = 'Unable to write contact information for '.$cgName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $cgName;
        $this->sendResponse('contact_group_write', $viewData);
    }
}
