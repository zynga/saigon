<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$cfg = $viewData->mgcfg;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#debug")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#eventhandler")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#services")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hosts")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#do_hostchecks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#encryption")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#result_workers")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#min-worker")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#max-worker")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#idle-timeout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#max-jobs")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#spawn-rate")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#fork_on_exec")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#show_error_output")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#use_uniq_jobs")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#accept_clear_results")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#perfdata")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#perfdata_mode")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#orphan_service_checks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#orphan_host_checks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#job-timeout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#max-age")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#dup_results_are_passive")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enable_embedded_perl")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#use_embedded_perl_implicitly")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#use_perl_cache")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#workaround_rc_25")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter();
});
</script>
<script type="text/javascript">
$(function() {
    $('.parentClass').click(function() {
        $('.parent-desc-' + $(this).attr("id")).slideToggle("fast");
        if ($(this).find("img").attr("src") == "static/imgs/minusSign.gif") {
            $(this).find("img").attr("src", "static/imgs/plusSign.gif");
        } else {
            $(this).find("img").attr("src", "static/imgs/minusSign.gif");
        }
    });
});
</script>
<body>
<?php
if ((isset($viewData->error)) && (!empty($viewData->error))) {
?>
<div id="error" style="border-width:2px;width:97%;left:5;top:5;position:absolute;text-align:center;background-color:red;" class="admin_box_blue divCacGroup admin_border_black">
<b>
<?php
    print $viewData->error;
?>
</b>
</div>
<div id="modgearman" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="modgearman" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="mg_submit_form">
<input type="hidden" value="modgearmancfg" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="write" id="action" name="action" />
<table class="noderesults">
    <thead>
        <th colspan="4">
            <div class="divCacGroup">
            Modgearman Configuration for <?php echo $deployment?>
            <div class="divCacGroup"></div>
            Please make sure all fields contain information:<br />
            <i>Configuration Files were built and tested against <a href="http://labs.consol.de/lang/en/nagios/mod-gearman/">ModGearman v1.4.2</a></i>
            </div>
        </th>
    </thead>
    <tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="glblcfg" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Global Configuration Settings:
            </div>
            <div class="divHide parent-desc-glblcfg">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Server:<br /><font size="2">Example: 10.0.0.1:4370</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['server']?>" size="64" maxlength="256" id="server" name="server" />
                        </td>
                        <th style="text-align:right;">
                            DupeServer:<br /><font size="2">Example: 10.0.0.2:4370</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['dupeserver']?>" size="64" maxlength="256" id="dupeserver" name="dupeserver" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Distribute EventHandlers:
                        </th>
                        <td style="text-align:left;">
                            <select id="eventhandler" name="eventhandler" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['eventhandler'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['eventhandler'])?>>Yes (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Distribute Service Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="services" name="services" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['services'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['services'])?>>Yes (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Distribute Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="hosts" name="hosts" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['hosts'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['hosts'])?>>Yes (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Execute Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="do_hostchecks" name="do_hostchecks" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['do_hostchecks'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['do_hostchecks'])?>>Yes (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Encryption:
                        </th>
                        <td style="text-align:left;">
                            <select id="encryption" name="encryption" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['encryption'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['encryption'])?>>Yes (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Encryption Key:<br /><font size="2">32 Character String</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="enckey" name="enckey" value="<?php echo $cfg['key']?>" size="48" maxlength="32" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Servicegroups:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['servicegroups']?>" size="64" maxlength="512" id="servicegroups" name="servicegroups" />
                        </td>
                        <th style="text-align:right;">
                            Hostgroups:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['hostgroups']?>" size="64" maxlength="512" id="hostgroups" name="hostgroups" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">Logfile:</th>
                        <td style="text-align:left;">
                            <input type="text" id="logfile" name="logfile" value="<?php echo htmlspecialchars(base64_decode($cfg['logfile']))?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            Debug:
                        </th>
                        <td style="text-align:left;">
                            <select id="debug" name="debug" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['debug'],0)?>>0 (Only Errors / Default)</option>
                                <option value="1" <?php echo is_selected($cfg['debug'],1)?>>1 (Debug Messages)</option>
                                <option value="2" <?php echo is_selected($cfg['debug'],2)?>>2 (Trace Messages)</option>
                                <option value="3" <?php echo is_selected($cfg['debug'],3)?>>3 (Trace and All Logs to STDOUT)</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="nebcfg" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                NEB Module Configuration Settings:
            </div>
            <div class="divHide parent-desc-nebcfg">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Result Workers:
                        </th>
                        <td style="text-align:left;">
                            <select id="result_workers" name="result_workers" multiple="multiple">
                                <option value="1" <?php echo is_selected($cfg['result_workers'], 1)?>>1 (Default)</option>
                                <option value="2" <?php echo is_selected($cfg['result_workers'], 2)?>>2</option>
                                <option value="3" <?php echo is_selected($cfg['result_workers'], 3)?>>3</option>
                                <option value="4" <?php echo is_selected($cfg['result_workers'], 4)?>>4</option>
                                <option value="5" <?php echo is_selected($cfg['result_workers'], 5)?>>5</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Use Uniq Jobs:
                        </th>
                        <td style="text-align:left;">
                            <select id="use_uniq_jobs" name="use_uniq_jobs" multiple="multiple">
                                <option value="off" <?php echo is_selected($cfg['use_uniq_jobs'], "off")?>>Off</option>
                                <option value="on" <?php echo is_selected($cfg['use_uniq_jobs'], "on")?>>On (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Local Servicegroups:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['localservicegroups']?>" size="64" maxlength="512" id="localservicegroups" name="localservicegroups" />
                        </td>
                        <th style="text-align:right;">
                            Local Hostgroups:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['localhostgroups']?>" size="64" maxlength="512" id="localhostgroups" name="localhostgroups" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Queue Custom Variable:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['queue_custom_variable']?>" size="64" maxlength="512" id="queue_custom_variable" name="queue_custom_variable" />
                        </td>
                        <th style="text-align:right;">
                            Accept Clear Results:
                        </th>
                        <td style="text-align:left;">
                            <select id="accept_clear_results" name="accept_clear_results" multiple="multiple">
                                <option value="no" <?php echo is_selected($cfg['accept_clear_results'], "no")?>>No (Default)</option>
                                <option value="yes" <?php echo is_selected($cfg['accept_clear_results'], "yes")?>>Yes</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Distribute Perfdata:
                        </th>
                        <td style="text-align:left;">
                            <select id="perfdata" name="perfdata" multiple="multiple">
                                <option value="no" <?php echo is_selected($cfg['perfdata'], "no")?>>No (Default)</option>
                                <option value="yes" <?php echo is_selected($cfg['perfdata'], "yes")?>>Yes</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Perfdata Mode:
                        </th>
                        <td style="text-align:left;">
                            <select id="perfdata_mode" name="perfdata_mode" multiple="multiple">
                                <option value="1" <?php echo is_selected($cfg['perfdata_mode'], 1)?>>Overwrite (1 / Default)</option>
                                <option value="2" <?php echo is_selected($cfg['perfdata_mode'], 2)?>>Append (2)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Fake Orphan Service Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="orphan_service_checks" name="orphan_service_checks" multiple="multiple">
                                <option value="no" <?php echo is_selected($cfg['orphan_service_checks'], "no")?>>No</option>
                                <option value="yes" <?php echo is_selected($cfg['orphan_service_checks'], "yes")?>>Yes (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Fake Orphan Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="orphan_host_checks" name="orphan_host_checks" multiple="multiple">
                                <option value="no" <?php echo is_selected($cfg['orphan_host_checks'], "no")?>>No</option>
                                <option value="yes" <?php echo is_selected($cfg['orphan_host_checks'], "yes")?>>Yes (Default)</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="workercfg" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Worker Module Configuration Settings:
            </div>
            <div class="divHide parent-desc-workercfg">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">Minimum Worker Count:</th>
                        <td style="text-align:left;">
                            <select id="min-worker" name="min-worker" multiple="multiple">
                                <option value="10" <?php echo is_selected($cfg['min-worker'], 10)?>>10 (Default)</option>
                                <option value="25" <?php echo is_selected($cfg['min-worker'], 25)?>>25</option>
                                <option value="50" <?php echo is_selected($cfg['min-worker'], 50)?>>50</option>
                                <option value="75" <?php echo is_selected($cfg['min-worker'], 75)?>>75</option>
                                <option value="100" <?php echo is_selected($cfg['min-worker'], 100)?>>100</option>
                            </select>
                        </td>
                        <th style="text-align:right;">Maximum Worker Count:</th>
                        <td style="text-align:left;">
                            <select id="max-worker" name="max-worker" multiple="multiple">
                                <option value="20" <?php echo is_selected($cfg['max-worker'], 20)?>>20 (Default)</option>
                                <option value="50" <?php echo is_selected($cfg['max-worker'], 50)?>>50</option>
                                <option value="100" <?php echo is_selected($cfg['max-worker'], 100)?>>100</option>
                                <option value="150" <?php echo is_selected($cfg['max-worker'], 150)?>>150</option>
                                <option value="200" <?php echo is_selected($cfg['max-worker'], 200)?>>200</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Idle Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="idle-timeout" name="idle-timeout" multiple="multiple">
                                <option value="15" <?php echo is_selected($cfg['idle-timeout'], 15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['idle-timeout'], 30)?>>30 Seconds (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['idle-timeout'], 45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['idle-timeout'], 60)?>>60 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['idle-timeout'], 90)?>>90 Seconds</option>
                                <option value="120" <?php echo is_selected($cfg['idle-timeout'], 120)?>>120 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Job Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="job-timeout" name="job-timeout" multiple="multiple">
                                <option value="30" <?php echo is_selected($cfg['job-timeout'], 30)?>>30 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['job-timeout'], 60)?>>60 Seconds (Default)</option>
                                <option value="90" <?php echo is_selected($cfg['job-timeout'], 90)?>>90 Seconds</option>
                                <option value="120" <?php echo is_selected($cfg['job-timeout'], 120)?>>120 Seconds</option>
                                <option value="180" <?php echo is_selected($cfg['job-timeout'], 180)?>>180 Seconds</option>
                                <option value="240" <?php echo is_selected($cfg['job-timeout'], 240)?>>240 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Max Jobs to Execute:<br /><font size="2">Amount of jobs to process before restarting fork</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="max-jobs" name="max-jobs" multiple="multiple">
                                <option value="100" <?php echo is_selected($cfg['max-jobs'], 100)?>>100</option>
                                <option value="250" <?php echo is_selected($cfg['max-jobs'], 250)?>>250</option>
                                <option value="500" <?php echo is_selected($cfg['max-jobs'], 500)?>>500 (Default)</option>
                                <option value="1000" <?php echo is_selected($cfg['max-jobs'], 1000)?>>1000</option>
                                <option value="2000" <?php echo is_selected($cfg['max-jobs'], 2000)?>>2000</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Max Age of Job:<br /><font size="2">Discard a job that is older than specified seconds</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="max-age" name="max-age" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['max-age'], 0)?>>0 (Disabled / Default)</option>
                                <option value="60" <?php echo is_selected($cfg['max-age'], 60)?>>60 Seconds</option>
                                <option value="120" <?php echo is_selected($cfg['max-age'], 120)?>>120 Seconds</option>
                                <option value="180" <?php echo is_selected($cfg['max-age'], 180)?>>180 Seconds</option>
                                <option value="240" <?php echo is_selected($cfg['max-age'], 240)?>>240 Seconds</option>
                                <option value="300" <?php echo is_selected($cfg['max-age'], 300)?>>300 Seconds</option>
                                <option value="600" <?php echo is_selected($cfg['max-age'], 600)?>>600 Seconds</option>
                                <option value="900" <?php echo is_selected($cfg['max-age'], 900)?>>900 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Spawn Rate:<br /><font size="2">Amount of Workers to Fork per Second to deal with job loads</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="spawn-rate" name="spawn-rate" multiple="multiple">
                                <option value="1" <?php echo is_selected($cfg['spawn-rate'], 1)?>>1 (Default)</option>
                                <option value="2" <?php echo is_selected($cfg['spawn-rate'], 2)?>>2</option>
                                <option value="3" <?php echo is_selected($cfg['spawn-rate'], 3)?>>3</option>
                                <option value="4" <?php echo is_selected($cfg['spawn-rate'], 4)?>>4</option>
                                <option value="5" <?php echo is_selected($cfg['spawn-rate'], 5)?>>5</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Fork on Exec:<br /><font size="2">Reduces Fork Requirements, but can mask plugin issues</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="fork_on_exec" name="fork_on_exec" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['fork_on_exec'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['fork_on_exec'])?>>Yes (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Show Error Output:<br /><font size="2">Append stderr to plugin output</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="show_error_output" name="show_error_output" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['show_error_output'])?>>No</option>
                                <option value="yes" <?php echo is_yes($cfg['show_error_output'])?>>Yes (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Workaround RC25:<br /><font size="2">Implements workaround for duplicate result job exit code 25</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="workaround_rc_25" name="workaround_rc_25" multiple="multiple">
                                <option value="off" <?php echo is_off($cfg['workaround_rc_25'])?>>Off (Default)</option>
                                <option value="on" <?php echo is_on($cfg['workaround_rc_25'])?>>On</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Dupe Results are Passive:
                        </th>
                        <td style="text-align:left;">
                            <select id="dup_results_are_passive" name="dup_results_are_passive" multiple="multiple">
                                <option value="no" <?php echo is_no($cfg['dup_results_are_passive'])?>>No (Active)</option>
                                <option value="yes" <?php echo is_yes($cfg['dup_results_are_passive'])?>>Yes (Passive / Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Load Limit over 1 Minute:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['load_limit1']?>" size="4" maxlength="12" id="load_limit1" name="load_limit1" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Load Limit over 5 Minutes:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['load_limit5']?>" size="4" maxlength="12" id="load_limit5" name="load_limit5" />
                        </td>
                        <th style="text-align:right;">
                            Load Limit over 15 Minutes:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $cfg['load_limit15']?>" size="4" maxlength="12" id="load_limit15" name="load_limit15" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Enable Embedded Perl:
                        </th>
                        <td style="text-align:left;">
                            <select id="enable_embedded_perl" name="enable_embedded_perl" multiple="multiple">
                                <option value="off" <?php echo is_off($cfg['enable_embedded_perl'])?>>Off</option>
                                <option value="on" <?php echo is_on($cfg['enable_embedded_perl'])?>>On (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Use Embedded Perl Implicitly:
                        </th>
                        <td style="text-align:left;">
                            <select id="use_embedded_perl_implicitly" name="use_embedded_perl_implicitly" multiple="multiple">
                                <option value="off" <?php echo is_off($cfg['use_embedded_perl_implicitly'])?>>Off (Default)</option>
                                <option value="on" <?php echo is_on($cfg['use_embedded_perl_implicitly'])?>>On</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Perl Cache:
                        </th>
                        <td style="text-align:left;">
                            <select id="use_perl_cache" name="use_perl_cache" multiple="multiple">
                                <option value="off" <?php echo is_off($cfg['use_perl_cache'])?>>Off</option>
                                <option value="on" <?php echo is_on($cfg['use_perl_cache'])?>>On (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            P1 File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo htmlspecialchars(base64_decode($cfg['p1_file']))?>" size="64" maxlength="512" id="p1_file" name="p1_file" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="delrel" style="text-align:left;text-indent:25px;background-color:#FE2E2E;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Delete Modgearman Configuration:
            </div>
            <div class="divHide parent-desc-delrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;width:20%;background-color:#FE2E2E;">
                            Delete Config:
                        </th>
                        <td style="text-align:left;">
                            <input type="checkbox" name="delete" value="1" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>

<?php

function is_yes($var) {
    if ($var == "yes") return "selected";
    return "";
}

function is_no($var) {
    if ($var == "no") return "selected";
    return "";
}

function is_on($var) {
    if ($var == "on") return "selected";
    return "";
}

function is_off($var) {
    if ($var == "off") return "selected";
    return "";
}

function is_selected($var,$check) {
    if ($var == $check) return "selected";
    return "";
}

require HTML_FOOTER;
