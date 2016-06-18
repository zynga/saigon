<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagiosPluginController extends Controller {

    private function fetchPluginInfo($deployment, $action, $redirect, $modrevision) {
        $pluginInfo = array();
        $pluginInfo['name'] = $this->getParam('name');
        $pluginInfo['desc'] = $this->getParam('desc');
        $pluginInfo['location'] = $this->getParam('location');
        $pluginInfo['file'] = $this->fetchUploadedFile('file');
        foreach ($pluginInfo as $key => $value) {
            if ($value === false) {
                if ($key == 'file') {
                    $oldfile = $this->getParam('useoldfile');
                    if ($oldfile == 1) {
                        $pluginInfo['file'] = base64_decode(RevDeploy::getDeploymentNagiosPluginFileContents($deployment, $pluginInfo['name'], $modrevision));
                        continue;
                    }
                }
                if (($key != 'location') && ($pluginInfo['location'] !== false)) $pluginInfo['location'] = base64_encode($pluginInfo['location']);
                $viewData = new ViewData();
                $viewData->error = "Unable to detect $key param";
                $viewData->deployment = $deployment;
                $viewData->action = $action;
                $viewData->plugin = $pluginInfo;
                $this->sendResponse($redirect, $viewData);
            }
        }
        $pluginInfo['md5'] = md5($pluginInfo['file']);
        $pluginInfo['location'] = base64_encode($pluginInfo['location']);
        $pluginInfo['file'] = base64_encode($pluginInfo['file']);
        return $pluginInfo;
    }

    private function getPlugin($redirect) {
        $plugin = $this->getParam('plugin');
        if ($plugin === false) {
            $viewData = new ViewData();
            $viewData->header = $this->getErrorHeader($redirect);
            $viewData->error = 'Unable to detect plugin param';
            $this->sendError('generic_error', $viewData);
        }
        return $plugin;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugins = RevDeploy::getCommonMergedDeploymentNagiosPlugins($deployment, $modrevision);
        $this->sendResponse('nagios_plugin_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $viewData->action = 'add_plugin';
        $viewData->deployment = $deployment;
        $this->sendResponse('nagios_plugin_action_stage', $viewData);
    }

    public function add_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = $this->fetchPluginInfo($deployment, 'add_plugin', 'nagios_plugin_action_stage', $modrevision);
        RevDeploy::createDeploymentNagiosPlugin($deployment, $pluginInfo['name'], $pluginInfo, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->plugin = $pluginInfo['name'];
        $this->sendResponse('nagios_plugin_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $viewData->plugin = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'modify_plugin';
        $this->sendResponse('nagios_plugin_action_stage', $viewData);
    }

    public function modify_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = $this->fetchPluginInfo($deployment, 'modify_plugin', 'nagios_plugin_action_stage', $modrevision);
        RevDeploy::modifyDeploymentNagiosPlugin($deployment, $pluginInfo['name'], $pluginInfo, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->plugin = $pluginInfo['name'];
        $this->sendResponse('nagios_plugin_write', $viewData);
    }

    public function delete_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $viewData->plugin = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'delete';
        $this->sendResponse('nagios_plugin_view_stage', $viewData);
    }

    public function delete_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->plugin = $plugin;
        $this->sendResponse('nagios_plugin_delete', $viewData);
    }

    public function show_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $viewData->plugin = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'view';
        $this->sendResponse('nagios_plugin_view_stage', $viewData);
    }

    public function show_plugin_common() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $viewData->plugin = RevDeploy::getCommonMergedDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'view';
        $this->sendResponse('nagios_plugin_view_stage', $viewData);
    }

    public function copy_to_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->deployment = $deployment;
        $viewData->plugin = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
        $viewData->action = 'copy_to_write';
        $this->sendResponse('nagios_plugin_view_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $deployments = array();
        array_push($deployments, $deployment);
        $plugin = $this->getPlugin('nagios_plugin_error');
        $modrevision = RevDeploy::getDeploymentRev($deployment);
        $viewData->deployment = $deployment;
        $viewData->plugin = RevDeploy::getCommonMergedDeploymentNagiosPlugin($deployment, $plugin, $modrevision);
        $viewData->availdeployments = $deployments;
        $viewData->ccs = true;
        $viewData->action = 'copy_to_write';
        $this->sendResponse('nagios_plugin_view_stage', $viewData);
    }

    public function copy_to_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_plugin_error');
        $plugin = $this->getPlugin('nagios_plugin_error');
        $todeployment = $this->getParam('todeployment');
        if ($todeployment === false) {
            $viewData->header = $this->getErrorHeader('nagios_plugin_error');
            $viewData->error = 'Unable to detect deployment to copy command to';
            $this->sendResponse('generic_error', $viewData);
        }
        $this->checkGroupAuthByDeployment($todeployment);
        $this->checkDeploymentRevStatus($todeployment);
        $tdRev = RevDeploy::getDeploymentNextRev($todeployment);
        $deployRev = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $deployRev);
        if (RevDeploy::existsDeploymentNagiosPlugin($todeployment, $plugin, $tdRev) === true) {
            RevDeploy::modifyDeploymentNagiosPlugin($todeployment, $plugin, $pluginInfo, $tdRev);
        } else {
            RevDeploy::createDeploymentNagiosPlugin($todeployment, $plugin, $pluginInfo, $tdRev);
        }
        $viewData->todeployment = $todeployment;
        $ccs = $this->getParam('ccs');
        if ($ccs == 1) {
            $viewData->deployment = $todeployment;
        } else {
            $viewData->deployment = $deployment;
        }
        $viewData->plugin = $plugin;
        $this->sendResponse('nagios_plugin_write', $viewData);
    }

}
