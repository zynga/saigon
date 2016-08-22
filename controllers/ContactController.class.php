<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ContactController extends Controller {

    private function fetchContactInfo($deployment, $action, $modrevision) {
        $keys = array('hostnotifenabled','hostnotifperiod','hostnotifopts','hostnotifcmd','svcnotifenabled','svcnotifperiod','svcnotifopts','svcnotifcmd');
        $contactInfo = array();
        $contactInfo['contact_name'] = $this->getParam('contactName');
        $contactInfo['alias'] = $this->getParam('contactAlias');
        $contactInfo['email'] = $this->getParam('contactEmail');
        $contactInfo['use'] = $this->getParam('usetemplate');
        foreach ($keys as $key) {
            $value = $this->getParam($key);
            if (($value === false) || (empty($value))) continue;
            if ($value == 'on') $value = 1;
            if ($value == 'off') $value = 0;
            switch ($key) {
                case 'hostnotifenabled':
                    $contactInfo['host_notifications_enabled'] = $value; break;
                case 'hostnotifperiod':
                    $contactInfo['host_notification_period'] = $value; break;
                case 'hostnotifopts':
                    $contactInfo['host_notification_options'] = $value; break;
                case 'hostnotifcmd':
                    $contactInfo['host_notification_commands'] = $value; break;
                case 'svcnotifenabled':
                    $contactInfo['service_notifications_enabled'] = $value; break;
                case 'svcnotifperiod':
                    $contactInfo['service_notification_period'] = $value; break;
                case 'svcnotifopts':
                    $contactInfo['service_notification_options'] = $value; break;
                case 'svcnotifcmd':
                    $contactInfo['service_notification_commands'] = $value; break;
                default:
                    break;
            }
        }
        if (($contactInfo['contact_name'] === false) || ($contactInfo['alias'] === false)) {
            $viewData = new ViewData();
            if ($action == 'copy_to_write') $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            $viewData->error = 'Unable to detect incoming contact parameters, make sure all of your input fields are filled in';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copy_to_write') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('contact_action_stage', $viewData);
        } else if ((preg_match_all('/[^a-zA-Z0-9_-]/s', $contactInfo['contact_name'], $forbidden))
                || (preg_match_all('/[^a-zA-Z0-9_-\s]/s', $contactInfo['alias'], $forbidden))) {
            $viewData = new ViewData();
            if ($action == 'copy_to_write') $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            $viewData->error = 'Unable to use contact name specified, detected forbidden characters "'.implode('', array_unique($forbidden[0])).'"';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copy_to_write') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('contact_action_stage', $viewData);
        } else if ($contactInfo['email'] === false) {
            $viewData = new ViewData();
            if ($action == 'copy_to_write') $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            $viewData->error = 'Unable to detect email for contact';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->action = $action;
            $viewData->deployment = $deployment;
            if ($action == 'copy_to_write') {
                $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            }
            $this->sendResponse('contact_action_stage', $viewData);
        } else if ($contactInfo['use'] === false) {
            /* We need to verify all incoming params because there is no template specified */
            foreach ($contactInfo as $key => $value) {
                if (($key == 'alias') || ($key == 'contact_name') || ($key == 'use')) continue;
                if ($value === false) {
                    $viewData = new ViewData();
                    if ($action == 'copy_to_write') $viewData->availdeployments = $this->getDeploymentsAvailToUser();
                    $viewData->error = 'If not using a template, please ensure all fields are filled in';
                    $viewData->contactInfo = $contactInfo;
                    $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
                    $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
                    $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
                    $viewData->action = $action;
                    $viewData->deployment = $deployment;
                    if ($action == 'copy_to_write') {
                        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
                    }
                    $this->sendResponse('contact_action_stage', $viewData);
                }
            }
        }
        return $contactInfo;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('contact_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'add_write';
        $this->sendResponse('contact_action_stage', $viewData);
    }

    public function add_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = $this->fetchContactInfo($deployment,'add_write', $modrevision);
        $contactName = $this->getParam('contactName');
        if (RevDeploy::existsDeploymentContact($deployment, $contactName, $modrevision) === true) {
            $viewData->error = 'Contact information exists for '.$contactName.' in '.$deployment.' Deployment';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = 'add_write';
            $this->sendResponse('contact_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentContact($deployment, $contactName, $contactInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to write contact information for '.$contactName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $contactName;
        $this->sendResponse('contact_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $contactName = $this->getParam('contactName');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = RevDeploy::getDeploymentContact($deployment, $contactName, $modrevision);
        $viewData->contactInfo = $contactInfo;
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'modify_write';
        $this->sendResponse('contact_action_stage', $viewData);
    }

    public function modify_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactName = $this->getParam('contactName');
        $contactInfo = $this->fetchContactInfo($deployment, 'modify_write', $modrevision);
        if (RevDeploy::modifyDeploymentContact($deployment, $contactName, $contactInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to write contact information for '.$contactName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->contact = $contactName;
        $this->sendResponse('contact_write', $viewData);
    }

    public function del_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $contactName = $this->getParam('contactName');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = RevDeploy::getDeploymentContact($deployment, $contactName, $modrevision);
        $viewData->contactInfo = $contactInfo;
        $viewData->deployment = $deployment;
        $viewData->action = 'del_write';
        $viewData->delFlag = true;
        $this->sendResponse('contact_view_stage', $viewData);
    }

    public function del_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $contactName = $this->getParam('contactName');
        if ($contactName === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $contactName;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if ($deployment != 'common') {
            $cGrpsInfo = RevDeploy::getDeploymentContactGroupswInfo($deployment, $modrevision);
            foreach ($cGrpsInfo as $cGrp => $cGrpInfo) {
                if (empty($cGrpInfo['members'])) continue;
                $members = $cGrpInfo['members'];
                if (($key = array_search($contactName, $members)) !== false) {
                    unset($members[$key]);
                    $members = array_values($members);
                    $cGrpInfo['members'] = $members;
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
                    if (empty($cGrpInfo['members'])) continue;
                    $members = $cGrpInfo['members'];
                    if (($key = array_search($contactName, $members)) !== false) {
                        unset($members[$key]);
                        $members = array_values($members);
                        $cGrpInfo['members'] = $members;
                        RevDeploy::modifyDeploymentContactGroup($tmpDeployment, $cGrp, $cGrpInfo, $tmpRevision);
                    }
                }
            }
        }
        RevDeploy::deleteDeploymentContact($deployment, $contactName, $modrevision);
        $this->sendResponse('contact_delete', $viewData);
    }

    public function copy_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $contactName = $this->getParam('contactName');
        if ($contactName === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = RevDeploy::getDeploymentContact($deployment, $contactName, $modrevision);
        $viewData->contactInfo = $contactInfo;
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('contact_action_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $contactName = $this->getParam('contactName');
        if ($contactName === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = RevDeploy::getCommonMergedDeploymentContact($deployment, $contactName, $modrevision);
        $viewData->contactInfo = $contactInfo;
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_write';
        $this->sendResponse('contact_action_stage', $viewData);
    }

    public function copy_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactName = $this->getParam('contactName');
        $contactInfo = $this->fetchContactInfo($deployment, 'copy_write', $modrevision);
        if (RevDeploy::existsDeploymentContact($deployment, $contactName, $modrevision) === true) {
            $viewData->error = 'Contact information exists for '.$contactName.' in '.$deployment.' Deployment';
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = 'copy_write';
            $this->sendResponse('contact_action_stage', $viewData);
        }
        if (RevDeploy::createDeploymentContact($deployment, $contactName, $contactInfo, $modrevision) === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to write contact information for '.$contactName.' into '.$deployment.' Deployment';
            $this->sendError('generic_error', $viewData);
        }
        $viewData->deployment = $deployment;
        $viewData->contact = $contactName;
        $this->sendResponse('contact_write', $viewData);
    }

    public function copy_to_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $contactName = $this->getParam('contactName');
        if ($contactName === false) {
            $viewData->header = $this->getErrorHeader('contact_error');
            $viewData->error = 'Unable to detect contact specified in post params';
            $this->sendError('generic_error', $viewData);
        }
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = RevDeploy::getDeploymentContact($deployment, $contactName, $modrevision);
        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
        $viewData->contactInfo = $contactInfo;
        $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
        $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
        $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'copy_to_write';
        $this->sendResponse('contact_action_stage', $viewData);
    }

    public function copy_to_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('contact_error');
        $contactName = $this->getParam('contactName');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $contactInfo = $this->fetchContactInfo($deployment, 'copy_to_write', $modrevision);
        $todeployment = $this->getParam('todeployment');
        if ($todeployment === false) {
            $viewData->error = 'Unable to detect contact to copy contact to';
            $viewData->availdeployments = $this->getDeploymentsAvailToUser();
            $viewData->contactInfo = $contactInfo;
            $viewData->contacttemplates = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $modrevision);
            $viewData->notifycmds = RevDeploy::getCommonMergedDeploymentNotifyCommands($deployment, $modrevision);
            $viewData->timeperiods = RevDeploy::getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $viewData->action = 'copy_to_write';
            $this->sendResponse('contact_action_stage', $viewData);
        }
        $this->checkGroupAuthByDeployment($todeployment);
        $this->checkDeploymentRevStatus($todeployment);
        $tdRev = RevDeploy::getDeploymentNextRev($todeployment);
        if (RevDeploy::existsDeploymentContact($todeployment, $contactName, $tdRev) === true) {
            RevDeploy::modifyDeploymentContact($todeployment, $contactName, $contactInfo, $tdRev);
        } else {
            RevDeploy::createDeploymentContact($todeployment, $contactName, $contactInfo, $tdRev);
        }
        $viewData->deployment = $deployment;
        $viewData->todeployment = $todeployment;
        $viewData->contact = $contactName;
        $this->sendResponse('contact_write', $viewData);
    }

}
