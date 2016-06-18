<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NRPEPluginController extends Controller {

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
                        if (preg_match('/^sup_/', $redirect)) {
                            $pluginInfo['file'] = base64_decode(RevDeploy::getDeploymentSupNRPEPluginFileContents($deployment, $pluginInfo['name'], $modrevision));
                        } else {
                            $pluginInfo['file'] = base64_decode(RevDeploy::getDeploymentNRPEPluginFileContents($deployment, $pluginInfo['name'], $modrevision));
                        }
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
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugins = RevDeploy::getCommonMergedDeploymentNRPEPlugins($deployment, $modrevision);
        $this->sendResponse('nrpe_plugin_stage', $viewData);
    }

    public function add_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->action = 'add_plugin';
        $viewData->deployment = $deployment;
        $this->sendResponse('nrpe_plugin_action_stage', $viewData);
    }

    public function add_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = $this->fetchPluginInfo($deployment, 'add_plugin', 'nrpe_plugin_action_stage', $modrevision);
        RevDeploy::createDeploymentNRPEPlugin($deployment, $pluginInfo['name'], $pluginInfo, $modrevision);
        $viewData->plugin = $pluginInfo['name'];
        $this->sendResponse('nrpe_plugin_write', $viewData);
    }

    public function modify_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'modify_plugin';
        $this->sendResponse('nrpe_plugin_action_stage', $viewData);
    }

    public function modify_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = $this->fetchPluginInfo($deployment, 'modify_plugin', 'nrpe_plugin_action_stage', $modrevision);
        RevDeploy::modifyDeploymentNRPEPlugin($deployment, $pluginInfo['name'], $pluginInfo, $modrevision);
        $viewData->plugin = $pluginInfo['name'];
        $this->sendResponse('nrpe_plugin_write', $viewData);
    }

    public function delete_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'delete';
        $this->sendResponse('nrpe_plugin_view_stage', $viewData);
    }

    public function delete_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->plugin = $plugin;
        $this->sendResponse('nrpe_plugin_delete', $viewData);
    }

    public function show_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'view';
        $this->sendResponse('nrpe_plugin_view_stage', $viewData);
    }

    public function show_plugin_common() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getCommonMergedDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'view';
        $this->sendResponse('nrpe_plugin_view_stage', $viewData);
    }

    public function copy_to_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
        $viewData->action = 'copy_to_write';
        $this->sendResponse('nrpe_plugin_view_stage', $viewData);
    }

    public function copy_common_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $deployments = array();
        $viewData->deployment = $deployment;
        array_push($deployments, $deployment);
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getCommonMergedDeploymentNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->availdeployments = $deployments;
        $viewData->ccs = true;
        $viewData->action = 'copy_to_write';
        $this->sendResponse('nrpe_plugin_view_stage', $viewData);
    }

    public function copy_to_write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nrpe_plugin_error');
        $plugin = $this->getPlugin('nrpe_plugin_error');
        $todeployment = $this->getParam('todeployment');
        if ($todeployment === false) {
            $viewData->header = $this->getErrorHeader('sup_nrpe_plugin_error');
            $viewData->error = 'Unable to detect deployment to copy plugin to';
            $this->sendResponse('generic_error', $viewData);
        }
        $this->checkGroupAuthByDeployment($todeployment);
        $this->checkDeploymentRevStatus($todeployment);
        $tdRev = RevDeploy::getDeploymentNextRev($todeployment);
        $deployRev = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = RevDeploy::getDeploymentNRPEPlugin($deployment, $plugin, $deployRev);
        if (RevDeploy::existsDeploymentNRPEPlugin($todeployment, $plugin, $tdRev) === true) {
            RevDeploy::modifyDeploymentNRPEPlugin($todeployment, $plugin, $pluginInfo, $tdRev);
        } else {
            RevDeploy::createDeploymentNRPEPlugin($todeployment, $plugin, $pluginInfo, $tdRev);
        }
        $viewData->todeployment = $todeployment;
        $ccs = $this->getParam('ccs');
        if ($ccs == 1) {
            $viewData->deployment = $todeployment;
        } else {
            $viewData->deployment = $deployment;
        }
        $viewData->plugin = $plugin;
        $this->sendResponse('nrpe_plugin_write', $viewData);
    }

    public function sup_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugins = RevDeploy::getCommonMergedDeploymentSupNRPEPlugins($deployment, $modrevision);
        $this->sendResponse('sup_nrpe_plugin_stage', $viewData);
    }

    public function add_sup_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $viewData->action = 'add_sup_plugin';
        $viewData->deployment = $deployment;
        $this->sendResponse('sup_nrpe_plugin_action_stage', $viewData);
    }

    public function add_sup_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $viewData->deployment = $deployment;
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = $this->fetchPluginInfo($deployment, 'add_sup_plugin', 'sup_nrpe_plugin_action_stage', $modrevision);
        RevDeploy::createDeploymentSupNRPEPlugin($deployment, $pluginInfo['name'], $pluginInfo, $modrevision);
        $viewData->plugin = $pluginInfo['name'];
        $this->sendResponse('sup_nrpe_plugin_write', $viewData);
    }

    public function modify_sup_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'modify_sup_plugin';
        $this->sendResponse('sup_nrpe_plugin_action_stage', $viewData);
    }

    public function modify_sup_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = $this->fetchPluginInfo($deployment, 'modify_sup_plugin', 'sup_nrpe_plugin_action_stage', $modrevision);
        RevDeploy::modifyDeploymentSupNRPEPlugin($deployment, $pluginInfo['name'], $pluginInfo, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->plugin = $pluginInfo['name'];
        $this->sendResponse('sup_nrpe_plugin_write', $viewData);
    }

    public function delete_sup_stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->action = 'delete_sup_plugin';
        $this->sendResponse('sup_nrpe_plugin_view_stage', $viewData);
    }

    public function delete_sup_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        RevDeploy::deleteDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->plugin = $plugin;
        $this->sendResponse('sup_nrpe_plugin_delete', $viewData);
    }

    public function show_sup_plugin() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'view';
        $this->sendResponse('sup_nrpe_plugin_view_stage', $viewData);
    }

    public function show_sup_plugin_common() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getCommonMergedDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->deployment = $deployment;
        $viewData->action = 'view';
        $this->sendResponse('sup_nrpe_plugin_view_stage', $viewData);
    }

    public function copy_to_stage_sup() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $viewData->deployment = $deployment;
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->availdeployments = $this->getDeploymentsAvailToUser();
        $viewData->action = 'copy_to_write_sup';
        $this->sendResponse('sup_nrpe_plugin_view_stage', $viewData);
    }

    public function copy_common_stage_sup() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $deployments = array();
        $viewData->deployment = $deployment;
        array_push($deployments, $deployment);
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $viewData->plugin = RevDeploy::getCommonMergedDeploymentSupNRPEPlugin($deployment, $plugin, $modrevision);
        $viewData->availdeployments = $deployments;
        $viewData->ccs = true;
        $viewData->action = 'copy_to_write_sup';
        $this->sendResponse('sup_nrpe_plugin_view_stage', $viewData);
    }

    public function copy_to_write_sup() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('sup_nrpe_plugin_error');
        $plugin = $this->getPlugin('sup_nrpe_plugin_error');
        $todeployment = $this->getParam('todeployment');
        if ($todeployment === false) {
            $viewData->header = $this->getErrorHeader('sup_nrpe_plugin_error');
            $viewData->error = 'Unable to detect deployment to copy plugin to';
            $this->sendResponse('generic_error', $viewData);
        }
        $this->checkGroupAuthByDeployment($todeployment);
        $this->checkDeploymentRevStatus($todeployment);
        $tdRev = RevDeploy::getDeploymentNextRev($todeployment);
        $deployRev = RevDeploy::getDeploymentNextRev($deployment);
        $pluginInfo = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $plugin, $deployRev);
        if (RevDeploy::existsDeploymentSupNRPEPlugin($todeployment, $plugin, $tdRev) === true) {
            RevDeploy::modifyDeploymentSupNRPEPlugin($todeployment, $plugin, $pluginInfo, $tdRev);
        } else {
            RevDeploy::createDeploymentSupNRPEPlugin($todeployment, $plugin, $pluginInfo, $tdRev);
        }
        $viewData->todeployment = $todeployment;
        $ccs = $this->getParam('ccs');
        if ($ccs == 1) {
            $viewData->deployment = $todeployment;
        } else {
            $viewData->deployment = $deployment;
        }
        $viewData->plugin = $plugin;
        $this->sendResponse('sup_nrpe_plugin_write', $viewData);
    }

}
