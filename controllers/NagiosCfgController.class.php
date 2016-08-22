<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagiosCfgController extends Controller {

    private function fetchConfigInfo($deployment) {
        $cfg = array();
        $cfg['accept_passive_host_checks'] = $this->getParam('acceptphostchks');
        $cfg['accept_passive_service_checks'] = $this->getParam('acceptpsvcchks');
        $cfg['cached_host_check_horizon'] = $this->getParam('cachehchkhorizon');
        $cfg['cached_service_check_horizon'] = $this->getParam('cacheschkhorizon');
        $cfg['cfg_dir'] = $this->getParam('cfgdir');
        $cfg['check_external_commands'] = $this->getParam('chkextcmds');
        $cfg['check_for_orphaned_hosts'] = $this->getParam('chkorphhost');
        $cfg['check_for_orphaned_services'] = $this->getParam('chkorphsvc');
        $cfg['check_host_freshness'] = $this->getParam('chkhostfresh');
        $cfg['check_result_path'] = $this->getParam('chkrespath');
        $cfg['check_result_reaper_frequency'] = $this->getParam('chkresrepfreq');
        $cfg['check_service_freshness'] = $this->getParam('chksvcfresh');
        $cfg['command_check_interval'] = $this->getParam('checkextcmdsint');
        $cfg['command_file'] = $this->getParam('extcmdfile');
        $cfg['enable_event_handlers'] = $this->getParam('enevnthand');
        $cfg['enable_notifications'] = $this->getParam('ennotif');
        $cfg['enable_predictive_host_dependency_checks'] = $this->getParam('enpredhostdepchks');
        $cfg['enable_predictive_service_dependency_checks'] = $this->getParam('enpredsvcdepchks');
        $cfg['event_broker_options'] = $this->getParam('useeventbroker');
        $cfg['event_handler_timeout'] = $this->getParam('evthandtimeout');
        $cfg['execute_host_checks'] = $this->getParam('exechostchks');
        $cfg['execute_service_checks'] = $this->getParam('execsvcchks');
        $cfg['external_command_buffer_slots'] = $this->getParam('extcmdbuff');
        $cfg['host_check_timeout'] = $this->getParam('hostchktimeout');
        $cfg['host_freshness_check_interval'] = $this->getParam('hostfreshchkint');
        $cfg['lock_file'] = $this->getParam('lockfile');
        $cfg['log_archive_path'] = $this->getParam('logarcpath');
        $cfg['log_event_handlers'] = $this->getParam('logeventhandlers');
        $cfg['log_external_commands'] = $this->getParam('logextcmds');
        $cfg['log_file'] = $this->getParam('logfile');
        $cfg['log_host_retries'] = $this->getParam('loghostretries');
        $cfg['log_initial_states'] = $this->getParam('loginitstate');
        $cfg['log_notifications'] = $this->getParam('lognotifs');
        $cfg['log_passive_checks'] = $this->getParam('logpsvchks');
        $cfg['log_rotation_method'] = $this->getParam('logrotmethod');
        $cfg['log_service_retries'] = $this->getParam('logsvcretries');
        $cfg['max_check_result_file_age'] = $this->getParam('mchkresfileage');
        $cfg['max_check_result_reaper_time'] = $this->getParam('mchkresreptime');
        $cfg['nagios_group'] = $this->getParam('naguser');
        $cfg['nagios_user'] = $this->getParam('naggrp');
        $cfg['notification_timeout'] = $this->getParam('notiftimeout');
        $cfg['object_cache_file'] = $this->getParam('objcachefile');
        $cfg['precached_object_file'] = $this->getParam('precacheobjfile');
        $cfg['resource_file'] = $this->getParam('resourcefile');
        $cfg['retain_state_information'] = $this->getParam('retainstateinfo');
        $cfg['retention_update_interval'] = $this->getParam('retupdint');
        $cfg['service_check_timeout'] = $this->getParam('svcchktimeout');
        $cfg['service_freshness_check_interval'] = $this->getParam('svcfreshchkint');
        $cfg['soft_state_dependencies'] = $this->getParam('usesoftstatedeps');
        $cfg['state_retention_file'] = $this->getParam('stateretfile');
        $cfg['status_file'] = $this->getParam('statusfile');
        $cfg['status_update_interval'] = $this->getParam('statusupdateint');
        $cfg['temp_file'] = $this->getParam('tmpfile');
        $cfg['temp_path'] = $this->getParam('tmppath');
        $cfg['use_large_installation_tweaks'] = $this->getParam('uselrginst');
        $cfg['use_retained_program_state'] = $this->getParam('useretstate');
        $cfg['use_retained_scheduling_info'] = $this->getParam('useretsched');
        $cfg['use_syslog'] = $this->getParam('usesyslog');
        $cfg['additional_freshness_latency'] = $this->getParam('addfreshlat');
        $cfg['admin_email'] = $this->getParam('nagadmemail');
        $cfg['admin_pager'] = $this->getParam('nagadmpager');
        $cfg['auto_reschedule_checks'] = $this->getParam('autoreschk');
        $cfg['auto_rescheduling_interval'] = $this->getParam('autoresint');
        $cfg['auto_rescheduling_window'] = $this->getParam('autoreswin');
        $cfg['bare_update_check'] = $this->getParam('bareupchk');
        $cfg['check_for_updates'] = $this->getParam('chkforup');
        $cfg['daemon_dumps_core'] = $this->getParam('useddc');
        $cfg['date_format'] = $this->getParam('dateformat');
        $cfg['debug_file'] = $this->getParam('debugfile');
        $cfg['debug_level'] = $this->getParam('dlevel');
        $cfg['debug_verbosity'] = $this->getParam('dverb');
        $cfg['enable_embedded_perl'] = $this->getParam('useep');
        $cfg['enable_environment_macros'] = $this->getParam('enenvmacros');
        $cfg['enable_flap_detection'] = $this->getParam('enflapdet');
        $cfg['high_host_flap_threshold'] = $this->getParam('hhft');
        $cfg['high_service_flap_threshold'] = $this->getParam('hsft');
        $cfg['host_inter_check_delay_method'] = $this->getParam('hosticdm');
        $cfg['illegal_macro_output_chars'] = $this->getParam('illegalmacro');
        $cfg['illegal_object_name_chars'] = $this->getParam('illegalobj');
        $cfg['interval_length'] = $this->getParam('intlength');
        $cfg['low_host_flap_threshold'] = $this->getParam('lhft');
        $cfg['low_service_flap_threshold'] = $this->getParam('lsft');
        $cfg['max_concurrent_checks'] = $this->getParam('maxccc');
        $cfg['max_host_check_spread'] = $this->getParam('maxhcs');
        $cfg['max_service_check_spread'] = $this->getParam('maxscs');
        $cfg['max_debug_file_size'] = $this->getParam('maxdfs');
        $cfg['obsess_over_hosts'] = $this->getParam('useobh');
        $cfg['obsess_over_services'] = $this->getParam('useobs');
        $cfg['ocsp_timeout'] = $this->getParam('ocsptmo');
        $cfg['ochp_timeout'] = $this->getParam('ochptmo');
        $cfg['ocsp_command'] = $this->getParam('ocspcmd');
        $cfg['ochp_command'] = $this->getParam('ochpcmd');
        $cfg['p1_file'] = $this->getParam('p1file');
        $cfg['passive_host_checks_are_soft'] = $this->getParam('usephcas');
        $cfg['retained_contact_host_attribute_mask'] = $this->getParam('usercham');
        $cfg['retained_contact_service_attribute_mask'] = $this->getParam('usercsam');
        $cfg['retained_host_attribute_mask'] = $this->getParam('userham');
        $cfg['retained_process_host_attribute_mask'] = $this->getParam('userpham');
        $cfg['retained_process_service_attribute_mask'] = $this->getParam('userpsam');
        $cfg['retained_service_attribute_mask'] = $this->getParam('usersam');
        $cfg['service_inter_check_delay_method'] = $this->getParam('svcicdm');
        $cfg['service_interleave_factor'] = $this->getParam('svcif');
        $cfg['sleep_time'] = $this->getParam('sleept');
        $cfg['translate_passive_host_checks'] = $this->getParam('usetphc');
        $cfg['use_aggressive_host_checking'] = $this->getParam('useahc');
        $cfg['use_embedded_perl_implicitly'] = $this->getParam('useepi');
        $cfg['use_regexp_matching'] = $this->getParam('usermatch');
        $cfg['use_true_regexp_matching'] = $this->getParam('usetrmatch');
        $cfg['perfdata_timeout'] = $this->getParam('perftmo');
        $cfg['process_performance_data'] = $this->getParam('enppd');
        $cfg['host_perfdata_command'] = $this->getParam('hpcmd');
        $cfg['service_perfdata_command'] = $this->getParam('spcmd');
        $cfg['host_perfdata_file'] = $this->getParam('hpfile');
        $cfg['service_perfdata_file'] = $this->getParam('spfile');
        $cfg['host_perfdata_file_template'] = $this->getParam('hpfilet');
        $cfg['service_perfdata_file_template'] = $this->getParam('spfilet');
        $cfg['host_perfdata_file_mode'] = $this->getParam('hpfilem');
        $cfg['service_perfdata_file_mode'] = $this->getParam('spfilem');
        $cfg['host_perfdata_file_processing_interval'] = $this->getParam('hpfilepi');
        $cfg['service_perfdata_file_processing_interval'] = $this->getParam('spfilepi');
        $cfg['host_perfdata_file_processing_command'] =  $this->getParam('hpfilepc');
        $cfg['service_perfdata_file_processing_command'] = $this->getParam('spfilepc');
        foreach ($cfg as $key => $value) {
            if (($value === false) && (intval($value) == 0)) {
                $cfg[$key] = 0;
            }
        }
        /* Base64 Encode Certain Keys */
        $cfg['cfg_dir'] = base64_encode($cfg['cfg_dir']);
        $cfg['check_result_path'] = base64_encode($cfg['check_result_path']);
        $cfg['command_file'] = base64_encode($cfg['command_file']);
        $cfg['lock_file'] = base64_encode($cfg['lock_file']);
        $cfg['log_archive_path'] = base64_encode($cfg['log_archive_path']);
        $cfg['log_file'] = base64_encode($cfg['log_file']);
        $cfg['object_cache_file'] = base64_encode($cfg['object_cache_file']);
        $cfg['precached_object_file'] = base64_encode($cfg['precached_object_file']);
        $cfg['resource_file'] = base64_encode($cfg['resource_file']);
        $cfg['state_retention_file'] = base64_encode($cfg['state_retention_file']);
        $cfg['status_file'] = base64_encode($cfg['status_file']);
        $cfg['temp_file'] = base64_encode($cfg['temp_file']);
        $cfg['temp_path'] = base64_encode($cfg['temp_path']);
        $cfg['debug_file'] = base64_encode($cfg['debug_file']);
        $cfg['illegal_macro_output_chars'] = base64_encode($cfg['illegal_macro_output_chars']);
        $cfg['illegal_object_name_chars'] = base64_encode($cfg['illegal_object_name_chars']);
        $cfg['p1_file'] = base64_encode($cfg['p1_file']);
        $cfg['host_perfdata_command'] = base64_encode($cfg['host_perfdata_command']);
        $cfg['service_perfdata_command'] = base64_encode($cfg['service_perfdata_command']);
        $cfg['host_perfdata_file'] = base64_encode($cfg['host_perfdata_file']);
        $cfg['service_perfdata_file'] = base64_encode($cfg['service_perfdata_file']);
        $cfg['host_perfdata_file_template'] = base64_encode($cfg['host_perfdata_file_template']);
        $cfg['service_perfdata_file_template'] = base64_encode($cfg['service_perfdata_file_template']);
        $cfg['host_perfdata_file_processing_command'] = base64_encode($cfg['host_perfdata_file_processing_command']);
        $cfg['service_perfdata_file_processing_command'] = base64_encode($cfg['service_perfdata_file_processing_command']);
        if (($cfg['ocsp_command'] !== false) && (!empty($cfg['ocsp_command']))) {
            $cfg['ocsp_command'] = base64_encode($cfg['ocsp_command']);
        }
        else {
            unset($cfg['ocsp_command']);
        }
        if (($cfg['ochp_command'] !== false) && (!empty($cfg['ochp_command']))) {
            $cfg['ochp_command'] = base64_encode($cfg['ochp_command']);
        }
        else {
            unset($cfg['ochp_command']);
        }

        return $cfg;
    }

    public function stage() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if ((!isset($_SESSION[$deployment])) || (!is_array($_SESSION[$deployment]))) {
            $_SESSION[$deployment] = array();
        }
        $_SESSION[$deployment]['brokermods'] = array();
        if (RevDeploy::existsDeploymentNagiosCfg($deployment, $modrevision) === true) {
            $viewData->nagcfg = RevDeploy::getDeploymentNagiosCfg($deployment, $modrevision);
            foreach ($viewData->nagcfg as $key => $value) {
                if (preg_match('/^broker_module_/', $key)) {
                    $_SESSION[$deployment]['brokermods'][md5($value)] = $value;
                }
            }
        } else {
            $viewData->nagcfg = NagDefaults::getNagiosConfigData();
        }
        $viewData->contacts = RevDeploy::getCommonMergedDeploymentContacts($deployment, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('nagios_cfg_stage', $viewData);
    }

    public function write() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_cfg_error');
        $this->checkGroupAuthByGroup(SUPERMEN);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cfgDelete = $this->getParam('delete');
        if ($cfgDelete == 1) {
            RevDeploy::deleteDeploymentNagiosCfg($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $this->sendResponse('nagios_cfg_delete', $viewData);
        }
        $cfgInfo = $this->fetchConfigInfo($deployment);
        if ((isset($_SESSION[$deployment]['brokermods'])) && (is_array($_SESSION[$deployment]['brokermods']))) {
            $i = 0;
            foreach ($_SESSION[$deployment]['brokermods'] as $md5 => $b64) {
                $cfgInfo['broker_module_' . $i] = $b64;
                $i++;
            }
        }
        RevDeploy::writeDeploymentNagiosCfg($deployment, $cfgInfo, $modrevision);
        unset($_SESSION[$deployment]['brokermods']);
        $viewData->deployment = $deployment;
        $this->sendResponse('nagios_cfg_write', $viewData);
    }

    public function view_brokermods() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_cfg_error');
        $viewData->deployment = $deployment;
        $this->sendResponse('nagios_cfg_view_brokermods_window', $viewData);
    }

    public function add_brokermod() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_cfg_error');
        $bmod = $this->getParam('bmod');
        if ($bmod === false) {
            $this->sendResponse('nagios_cfg_view_brokermods_window', $viewData);
        }
        $b64enc = base64_encode($bmod);
        $md5 = md5($b64enc);
        if ((!isset($_SESSION[$deployment]['brokermods'])) || (!is_array($_SESSION[$deployment]['brokermods']))) {
            $_SESSION[$deployment]['brokermods'] = array();
        }
        $_SESSION[$deployment]['brokermods'][$md5] = $b64enc;
        $viewData->deployment = $deployment;
        $this->sendResponse('nagios_cfg_view_brokermods_window', $viewData);
    }

    public function del_brokermod() {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('nagios_cfg_error');
        $md5 = $this->getParam('bmod');
        unset($_SESSION[$deployment]['brokermods'][$md5]);
        $viewData->deployment = $deployment;
        $this->sendResponse('nagios_cfg_view_brokermods_window', $viewData);
    }

}
