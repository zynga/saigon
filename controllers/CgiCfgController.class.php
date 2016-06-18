<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CgiCfgController extends Controller
{

    /**
     * _fetchConfigInfo 
     * 
     * @param mixed $deployment  deployment being processed 
     * @param mixed $modrevision revision we should load data up from 
     *
     * @access private
     * @return void
     */
    private function _fetchConfigInfo($deployment, $modrevision)
    {
        $cfg = array();
        $cfg['main_config_file'] = $this->getParam('maincfg');
        $cfg['physical_html_path'] = $this->getParam('htmlloc');
        $cfg['url_html_path'] = $this->getParam('htmlurl');
        $cfg['show_context_help'] = $this->getParam('showctxhelp');
        $cfg['use_pending_states'] = $this->getParam('usependstate');
        $cfg['use_authentication'] = $this->getParam('useauth');
        $cfg['authorized_for_system_information'] = $this->getParam('authsysinfo');
        $cfg['authorized_for_configuration_information'] = $this->getParam('authconfinfo');
        $cfg['authorized_for_system_commands'] = $this->getParam('authsyscmds');
        $cfg['authorized_for_all_services'] = $this->getParam('authallsvcs');
        $cfg['authorized_for_all_hosts'] = $this->getParam('authallhosts');
        $cfg['authorized_for_all_service_commands'] = $this->getParam('authallsvccmds');
        $cfg['authorized_for_all_host_commands'] = $this->getParam('authallhostcmds');
        $cfg['authorized_for_read_only'] = $this->getParam('authreadonly');
        $cfg['default_statusmap_layout'] = $this->getParam('statusmaplayout');
        $cfg['default_statuswrl_layout'] = $this->getParam('statuswrllayout');
        $cfg['ping_syntax'] = $this->getParam('pingsyntax');
        $cfg['refresh_rate'] = $this->getParam('refreshrate');
        $cfg['escape_html_tags'] = $this->getParam('eschtmltags');
        $cfg['action_url_target'] = $this->getParam('actiontarget');
        $cfg['notes_url_target'] = $this->getParam('notestarget');
        $cfg['enable_splunk_integration'] = $this->getParam('ensplunk');
        $cfg['splunk_url'] = $this->getParam('splunkurl');
        foreach ($cfg as $key => $value) {
            if ($value === false) {
                if (($key == "show_context_help") || ($key == "use_pending_states") || ($key == "use_authentication") ||
                    ($key == "escape_html_tags") || ($key == "enable_splunk_integration")) {
                    if (intval($value) == 0) {
                        $cfg[$key] = "0";
                    }
                } else if (($key == 'splunk_url') && ($cfg['enable_splunk_integration'] == 0)) {
                    continue;
                } else if ($key == 'authorized_for_read_only') {
                    continue;
                } else {
                    if (($key != 'main_config_file') && (isset($cfg['main_config_file'])) &&
                        (!empty($cfg['main_config_file']))) $cfg['main_config_file'] = base64_encode($cfg['main_config_file']);
                    if (($key != 'physical_html_path') && (isset($cfg['physical_html_path'])) &&
                        (!empty($cfg['physical_html_path']))) $cfg['physical_html_path'] = base64_encode($cfg['physical_html_path']);
                    if (($key != 'url_html_path') && (isset($cfg['url_html_path'])) &&
                        (!empty($cfg['url_html_path']))) $cfg['url_html_path'] = base64_encode($cfg['url_html_path']);
                    if (($key != 'ping_syntax') && (isset($cfg['ping_syntax'])) &&
                        (!empty($cfg['ping_syntax']))) $cfg['ping_syntax'] = base64_encode($cfg['ping_syntax']);
                    $viewData = new ViewData();
                    $viewData->error = "Unable to detect param specified for $key";
                    $viewData->cgicfg = $cfg;
                    $viewData->deployment = $deployment;
                    $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
                    $this->sendResponse('cgi_cfg_stage', $viewData);
                }
            }
        }
        $cfg['main_config_file'] = base64_encode($cfg['main_config_file']);
        $cfg['physical_html_path'] = base64_encode($cfg['physical_html_path']);
        $cfg['url_html_path'] = base64_encode($cfg['url_html_path']);
        $cfg['ping_syntax'] = base64_encode($cfg['ping_syntax']);
        $cfg['lock_author_names'] = "1";
        $cfg['use_ssl_authentication'] = "0";
        return $cfg;
    }

    /**
     * stage - load up staging web view
     * 
     * @access public
     * @return void
     */
    public function stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cgi_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (($return = RevDeploy::existsDeploymentCgiCfg($deployment, $modrevision)) === true) {
            $viewData->cgicfg = RevDeploy::getDeploymentCgiCfg($deployment, $modrevision);
        } else {
            $viewData->cgicfg = NagDefaults::getNagiosCGIConfigData();
        }
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('cgi_cfg_stage', $viewData);
    }

    /**
     * write - issue cgi config write to datastore 
     * 
     * @access public
     * @return void
     */
    public function write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('cgi_cfg_error');
        $this->checkGroupAuthByDeployment($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cfgDelete = $this->getParam('delete');
        if ($cfgDelete == 1) {
            RevDeploy::deleteDeploymentCgiCfg($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $this->sendResponse('cgi_cfg_delete', $viewData);
        }
        $cfgInfo = $this->_fetchConfigInfo($deployment, $modrevision);
        ksort($cfgInfo);
        RevDeploy::writeDeploymentCgiCfg($deployment, $cfgInfo, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('cgi_cfg_write', $viewData);
    }

}
 
