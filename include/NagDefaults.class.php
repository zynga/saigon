<?php

class NagDefaults
{

    public static function getNagiosConfigData()
    {
        $cfg = array();
        if (strtolower(DIST_TYPE) == 'debian') {
            $cfg['cfg_dir'] = base64_encode('/etc/nagios3/conf.d');
            $cfg['check_result_path'] = base64_encode('/var/lib/nagios3/spool/checkresults');
            $cfg['command_file'] = base64_encode('/var/lib/nagios3/rw/nagios.cmd');
            $cfg['lock_file'] = base64_encode('/var/run/nagios3/nagios3.pid');
            $cfg['log_archive_path'] = base64_encode('/var/log/nagios3/archives');
            $cfg['log_file'] = base64_encode('/var/log/nagios3/nagios.log');
            $cfg['object_cache_file'] = base64_encode('/var/cache/nagios3/objects.cache');
            $cfg['precached_object_file'] = base64_encode('/var/lib/nagios3/objects.precache');
            $cfg['resource_file'] = base64_encode('/etc/nagios3/resource.cfg');
            $cfg['state_retention_file'] = base64_encode('/var/lib/nagios3/retention.dat');
            $cfg['status_file'] = base64_encode('/var/cache/nagios3/status.dat');
            $cfg['temp_file'] = base64_encode('/var/cache/nagios3/nagios.tmp');
            $cfg['debug_file'] = base64_encode('/var/log/nagios3/nagios.debug');
            $cfg['p1_file'] = base64_encode('/usr/lib/nagios3/p1.pl');

        } else {
            $cfg['cfg_dir'] = base64_encode('/usr/local/nagios/etc/objects');
            $cfg['check_result_path'] = base64_encode('/usr/local/nagios/var/spool/checkresults');
            $cfg['command_file'] = base64_encode('/usr/local/nagios/var/rw/nagios.cmd');
            $cfg['lock_file'] = base64_encode('/usr/local/nagios/var/nagios.lock');
            $cfg['log_archive_path'] = base64_encode('/usr/local/nagios/var/archives');
            $cfg['log_file'] = base64_encode('/usr/local/nagios/var/nagios.log');
            $cfg['object_cache_file'] = base64_encode('/usr/local/nagios/var/objects.cache');
            $cfg['precached_object_file'] = base64_encode('/usr/local/nagios/var/objects.precache');
            $cfg['resource_file'] = base64_encode('/usr/local/nagios/etc/resource.cfg');
            $cfg['state_retention_file'] = base64_encode('/usr/local/nagios/var/retention.dat');
            $cfg['status_file'] = base64_encode('/usr/local/nagios/var/status.dat');
            $cfg['temp_file'] = base64_encode('/usr/local/nagios/var/nagios.tmp');
            $cfg['debug_file'] = base64_encode('/usr/local/nagios/var/nagios.debug');
            $cfg['p1_file'] = base64_encode('/usr/local/nagios/bin/p1.pl');

        }
        $cfg['accept_passive_host_checks'] = 1;
        $cfg['accept_passive_service_checks'] = 1;
        $cfg['cached_host_check_horizon'] = 15;
        $cfg['cached_service_check_horizon'] = 15;
        $cfg['check_external_commands'] = 1;
        $cfg['check_for_orphaned_hosts'] = 1;
        $cfg['check_for_orphaned_services'] = 1;
        $cfg['check_host_freshness'] = 1;
        $cfg['check_result_reaper_frequency'] = 10;
        $cfg['check_service_freshness'] = 1;
        $cfg['command_check_interval'] = -1;
        $cfg['enable_event_handlers'] = 1;
        $cfg['enable_notifications'] = 1;
        $cfg['enable_predictive_host_dependency_checks'] = 1;
        $cfg['enable_predictive_service_dependency_checks'] = 1;
        $cfg['event_broker_options'] = -1;
        $cfg['event_handler_timeout'] = 30;
        $cfg['execute_host_checks'] = 1;
        $cfg['execute_service_checks'] = 1;
        $cfg['external_command_buffer_slots'] = 4096;
        $cfg['host_check_timeout'] = 30;
        $cfg['host_freshness_check_interval'] = 60;
        $cfg['illegal_macro_output_chars'] = base64_encode('`~$&|\'"<>');
        $cfg['illegal_object_name_chars'] = base64_encode('`~!$%^&*|\'"<>?,()=');
        $cfg['log_event_handlers'] = 1;
        $cfg['log_external_commands'] = 1;
        $cfg['log_host_retries'] = 1;
        $cfg['log_initial_states'] = 0;
        $cfg['log_notifications'] = 1;
        $cfg['log_passive_checks'] = 1;
        $cfg['log_rotation_method'] = 'd';
        $cfg['log_service_retries'] = 1;
        $cfg['max_check_result_file_age'] = 3600;
        $cfg['max_check_result_reaper_time'] = 30;
        $cfg['nagios_group'] = 'nagios';
        $cfg['nagios_user'] = 'nagios';
        $cfg['notification_timeout'] = 30;
        $cfg['retain_state_information'] = 1;
        $cfg['retention_update_interval'] = 60;
        $cfg['service_check_timeout'] = 60;
        $cfg['service_freshness_check_interval'] = 60;
        $cfg['soft_state_dependencies'] = 0;
        $cfg['status_update_interval'] = 10;
        $cfg['temp_path'] = base64_encode('/tmp');
        $cfg['use_large_installation_tweaks'] = 0;
        $cfg['use_retained_program_state'] = 1;
        $cfg['use_retained_scheduling_info'] = 1;
        $cfg['use_syslog'] = 1;
        $cfg['additional_freshness_latency'] = 15;
        $cfg['admin_email'] = 'nagios@localhost';
        $cfg['admin_pager'] = 'pagenagios@localhost';
        $cfg['auto_reschedule_checks'] = 0;
        $cfg['auto_rescheduling_interval'] = 30;
        $cfg['auto_rescheduling_window'] = 180;
        $cfg['bare_update_check'] = 0;
        $cfg['check_for_updates'] = 1;
        $cfg['daemon_dumps_core'] = 0;
        $cfg['date_format'] = 'us';
        $cfg['debug_level'] = 0;
        $cfg['debug_verbosity'] = 1;
        $cfg['enable_embedded_perl'] = 1;
        $cfg['enable_environment_macros'] = 0;
        $cfg['enable_flap_detection'] = 0;
        $cfg['high_host_flap_threshold'] = 20.0;
        $cfg['high_service_flap_threshold'] = 20.0;
        $cfg['host_inter_check_delay_method'] = 's';
        $cfg['interval_length'] = 60; 
        $cfg['low_host_flap_threshold'] = 5.0;
        $cfg['low_service_flap_threshold'] = 5.0;
        $cfg['max_concurrent_checks'] = 0;
        $cfg['max_host_check_spread'] = 30;
        $cfg['max_service_check_spread'] = 30;
        $cfg['max_debug_file_size'] = 1000000;
        $cfg['obsess_over_hosts'] = 0;
        $cfg['obsess_over_services'] = 0;
        $cfg['ocsp_timeout'] = 5;
        $cfg['ochp_timeout'] = 5;
        $cfg['passive_host_checks_are_soft'] = 0;
        $cfg['retained_contact_host_attribute_mask'] = 0;
        $cfg['retained_contact_service_attribute_mask'] = 0;
        $cfg['retained_host_attribute_mask'] = 0;
        $cfg['retained_process_host_attribute_mask'] = 0;
        $cfg['retained_process_service_attribute_mask'] = 0;
        $cfg['retained_service_attribute_mask'] = 0;
        $cfg['service_inter_check_delay_method'] = 's';
        $cfg['service_interleave_factor'] = 's';
        $cfg['sleep_time'] = 0.25;
        $cfg['translate_passive_host_checks'] = 0;
        $cfg['use_aggressive_host_checking'] = 0;
        $cfg['use_embedded_perl_implicitly'] = 1;
        $cfg['use_regexp_matching'] = 0;
        $cfg['use_true_regexp_matching'] = 0;
        $cfg['process_performance_data'] = 0;
        $cfg['perfdata_timeout'] = 5;
        $cfg['host_perfdata_command'] = base64_encode('process-host-perfdata');
        $cfg['service_perfdata_command'] = base64_encode('process-service-perfdata');
        $cfg['host_perfdata_file'] = base64_encode('/tmp/host-perfdata');
        $cfg['service_perfdata_file'] = base64_encode('/tmp/service-perfdata');
        $cfg['host_perfdata_file_template'] = base64_encode('[HOSTPERFDATA]\t$TIMET$\t$HOSTNAME$\t$HOSTEXECUTIONTIME$\t$HOSTOUTPUT$\t$HOSTPERFDATA$');
        $cfg['service_perfdata_file_template'] = base64_encode('[SERVICEPERFDATA]\t$TIMET$\t$HOSTNAME$\t$SERVICEDESC$\t$SERVICEEXECUTIONTIME$\t$SERVICELATENCY$\t$SERVICEOUTPUT$\t$SERVICEPERFDATA$');
        $cfg['host_perfdata_file_mode'] = 'a';
        $cfg['service_perfdata_file_mode'] = 'a';
        $cfg['host_perfdata_file_processing_interval'] = 0;
        $cfg['service_perfdata_file_processing_interval'] = 0;
        $cfg['host_perfdata_file_processing_command'] = base64_encode('process-host-perfdata-file');
        $cfg['service_perfdata_file_processing_command'] = base64_encode('process-service-perfdata-file');
        return $cfg;
    }

    public static function getNagiosCGIConfigData()
    {
        $cfg = array();
        if (strtolower(DIST_TYPE) == 'debian') {
            $cfg['main_config_file'] = base64_encode('/etc/nagios3/nagios.cfg');
            $cfg['physical_html_path'] = base64_encode('/usr/share/nagios3/htdocs');
            $cfg['url_html_path'] = base64_encode('/nagios3');
        } else {
            $cfg['main_config_file'] = base64_encode('/usr/local/nagios/etc/nagios.cfg');
            $cfg['physical_html_path'] = base64_encode('/usr/local/nagios/share');
            $cfg['url_html_path'] = base64_encode('/nagios');
        }
        $cfg['show_context_help'] = 0;
        $cfg['use_pending_states'] = 1;
        $cfg['use_authentication'] = 1;
        $cfg['use_ssl_authentication'] = 0;
        $cfg['authorized_for_system_information'] = array('*');
        $cfg['authorized_for_configuration_information'] = array('*');
        $cfg['authorized_for_system_commands'] = array('*');
        $cfg['authorized_for_all_services'] = array('*');
        $cfg['authorized_for_all_hosts'] = array('*');
        $cfg['authorized_for_all_service_commands'] = array('*');
        $cfg['authorized_for_all_host_commands'] = array('*');
        $cfg['authorized_for_read_only'] = array();
        $cfg['default_statusmap_layout'] = 5;
        $cfg['default_statuswrl_layout'] = 4;
        $cfg['ping_syntax'] = base64_encode('/bin/ping -n -U -c 5 $HOSTADDRESS$');
        $cfg['refresh_rate'] = 90;
        $cfg['escape_html_tags'] = 1;
        $cfg['action_url_target'] = '_blank';
        $cfg['notes_url_target'] = '_blank';
        $cfg['lock_author_names'] = 1;
        $cfg['enable_splunk_integration'] = 0;
        $cfg['splunk_url'] = base64_encode('http://127.0.0.1:8000/');
        return $cfg; 
    }

    public static function getNagiosResourceConfigData()
    {
        if (strtolower(DIST_TYPE) == 'debian') {
            return array('USER1' => base64_encode('/usr/lib/nagios/plugins'));
        } else {
            return array('USER1' => base64_encode('/usr/local/nagios/libexec'));
        }
    }

}
