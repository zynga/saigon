<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ModgearmanCfgController extends Controller
{

    /**
     * _fetchConfigInfo - fetch necessary information to populate the modgearman config 
     * 
     * @param mixed $deployment deployment we are being asked to process 
     *
     * @access private
     * @return void
     */
    private function _fetchConfigInfo($deployment)
    {
        $cfg = array();

        // Core
        $cfg['debug'] = $this->getParam('debug');
        $cfg['eventhandler'] = $this->getParam('eventhandler');
        $cfg['services'] = $this->getParam('services');
        $cfg['hosts'] = $this->getParam('hosts');
        $cfg['do_hostchecks'] = $this->getParam('do_hostchecks');
        $cfg['encryption'] = $this->getParam('encryption');
        $cfg['server'] = $this->getParam('server');
        $cfg['dupeserver'] = $this->getParam('dupeserver');
        $cfg['hostgroups'] = $this->getParam('hostgroups');
        $cfg['servicegroups'] = $this->getParam('servicegroups');
        $cfg['logfile'] = $this->getParam('logfile');
        $cfg['key'] = $this->getParam('enckey');

        // NEB
        $cfg['result_workers'] = $this->getParam('result_workers');
        $cfg['use_uniq_jobs'] = $this->getParam('use_uniq_jobs');
        $cfg['localhostgroups'] = $this->getParam('localhostgroups');
        $cfg['localservicegroups'] = $this->getParam('localservicegroups');
        $cfg['queue_custom_variable'] = $this->getParam('queue_custom_variable');
        $cfg['perfdata'] = $this->getParam('perfdata');
        $cfg['perfdata_mode'] = $this->getParam('perfdata_mode');
        $cfg['orphan_host_checks'] = $this->getParam('orphan_host_checks');
        $cfg['orphan_service_checks'] = $this->getParam('orphan_service_checks');
        $cfg['accept_clear_results'] = $this->getParam('accept_clear_results');

        // Worker
        $cfg['job-timeout'] = $this->getParam('job-timeout');
        $cfg['min-worker'] = $this->getParam('min-worker');
        $cfg['max-worker'] = $this->getParam('max-worker');
        $cfg['idle-timeout'] = $this->getParam('idle-timeout');
        $cfg['max-jobs'] = $this->getParam('max-jobs');
        $cfg['max-age'] = $this->getParam('max-age');
        $cfg['spawn-rate'] = $this->getParam('spawn-rate');
        $cfg['fork_on_exec'] = $this->getParam('fork_on_exec');
        $cfg['show_error_output'] = $this->getParam('show_error_output');
        $cfg['workaround_rc_25'] = $this->getParam('workaround_rc_25');
        $cfg['load_limit1'] = $this->getParam('load_limit1');
        $cfg['load_limit5'] = $this->getParam('load_limit5');
        $cfg['load_limit15'] = $this->getParam('load_limit15');
        $cfg['dup_results_are_passive'] = $this->getParam('dup_results_are_passive');
        $cfg['enable_embedded_perl'] = $this->getParam('enable_embedded_perl');
        $cfg['use_embedded_perl_implicitly'] = $this->getParam('use_embedded_perl_implicitly');
        $cfg['use_perl_cache'] = $this->getParam('use_perl_cache');
        $cfg['p1_file'] = $this->getParam('p1_file');

        foreach ($cfg as $key => $value) {
            if ($value === false) {
                if ( preg_match('/debug|max-age|load_limit/', $key ) && ( intval($value) == 0 ) ) {
                    $cfg[$key] = "0";
                } elseif (
                    ( $key == 'servicegroups' ) || ( $key == 'hostgroups' ) || ( $key == 'localservicegroups' ) ||
                    ( $key == 'localhostgroups') || ( $key == 'queue_custom_variable' ) || ( $key == 'p1_file' )
                ) {
                    unset($cfg[$key]);
                } else {
                    if ( ( $key != 'logfile' ) && ( isset( $cfg['logfile'] ) ) && ( !empty( $cfg['logfile'] ) ) ) {
                        $cfg['logfile'] = base64_encode($cfg['logfile']);
                    }
                    if ( ( $key != 'p1_file' ) && ( isset( $cfg['p1_file'] ) ) && ( !empty( $cfg['p1_file'] ) ) ) {
                        $cfg['p1_file'] = base64_encode($cfg['p1_file']);
                    }
                    $viewData = new ViewData();
                    $viewData->error = "Unable to detected param specified for $key";
                    $viewData->mgcfg = $cfg;
                    $viewData->deployment = $deployment;
                    $this->sendResponse('modgearman_cfg_stage', $viewData);
                }
            }
        }
        $cfg['logfile'] = base64_encode($cfg['logfile']);
        if ((isset($cfg['p1_file'])) && (!empty($cfg['p1_file']))) {
            $cfg['p1_file'] = base64_encode($cfg['p1_file']);
        }
        return $cfg;
    }

    /**
     * stage - load up stage view for modifying / creating modgearman config
     * 
     * @access public
     * @return void
     */
    public function stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('modgearman_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentModgearmanCfg($deployment, $modrevision) === true) {
            $viewData->mgcfg = RevDeploy::getDeploymentModgearmanCfg($deployment, $modrevision);
        } else {
            $cfg = array();
            // Core
            $cfg['debug'] = "0";
            $cfg['eventhandler'] = "yes";
            $cfg['services'] = "yes";
            $cfg['hosts'] = "yes";
            $cfg['do_hostchecks'] = "yes";
            $cfg['encryption'] = "yes";
            $cfg['server'] = "10.0.0.1:4370";
            $cfg['dupeserver'] = "";
            $cfg['hostgroups'] = "";
            $cfg['servicegroups'] = "";
            $cfg['logfile'] = base64_encode("/var/log/mod_gearman/mod_gearman.log");
            $cfg['key'] = hash("md5", file_get_contents("/dev/urandom", 0, null, -1, 16));

            // NEB
            $cfg['result_workers'] = "1";
            $cfg['use_uniq_jobs'] = "on";
            $cfg['localhostgroups'] = "";
            $cfg['localservicegroups'] = "";
            $cfg['queue_custom_variable'] = "";
            $cfg['perfdata'] = "no";
            $cfg['perfdata_mode'] = 1;
            $cfg['orphan_host_checks'] ="yes";
            $cfg['orphan_service_checks'] = "yes";
            $cfg['accept_clear_results'] = "no";

            // Worker
            $cfg['job-timeout'] = "60";
            $cfg['min-worker'] = "10";
            $cfg['max-worker'] = "20";
            $cfg['idle-timeout'] = "30";
            $cfg['max-jobs'] = "500";
            $cfg['max-age'] = "0";
            $cfg['spawn-rate'] = "1";
            $cfg['fork_on_exec'] = "yes";
            $cfg['show_error_output'] = "yes";
            $cfg['workaround_rc_25'] = "off";
            $cfg['load_limit1'] = "0";
            $cfg['load_limit5'] = "0";
            $cfg['load_limit15'] = "0";
            $cfg['dup_results_are_passive'] = "yes";
            $cfg['enable_embedded_perl'] = "on";
            $cfg['use_embedded_perl_implicitly'] = "off";
            $cfg['use_perl_cache'] = "on";
            $cfg['p1_file'] = base64_encode("/usr/share/mod_gearman/mod_gearman_p1.pl");

            $viewData->mgcfg = $cfg;
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('modgearman_cfg_stage', $viewData);
    }

    /**
     * write - write modgearman configuration to datastore
     * 
     * @access public
     * @return void
     */
    public function write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('modgearman_cfg_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cfgDelete = $this->getParam('delete');
        if ($cfgDelete == 1) {
            RevDeploy::deleteDeploymentModgearmanCfg($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $this->sendResponse('modgearman_cfg_delete', $viewData);
        }
        $cfgInfo = $this->_fetchConfigInfo($deployment);
        ksort($cfgInfo);
        RevDeploy::writeDeploymentModgearmanCfg($deployment, $cfgInfo, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('modgearman_cfg_write', $viewData);
    }

}

