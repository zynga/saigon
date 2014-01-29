<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$cfg = $viewData->nagcfg;

foreach (array('ocsp_command','ochp_command') as $key) {
    if ((isset($cfg[$key])) && (!empty($cfg[$key]))) {
        $cfg[$key] = htmlspecialchars(base64_decode($cfg[$key]));
    }
    else {
        $cfg[$key] = '';
    }
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#statusupdateint")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#logrotmethod")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usesyslog")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#lognotifs")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#logsvcretries")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#loghostretries")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#logeventhandlers")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#loginitstate")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#logpsvchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#logextcmds")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkextcmds")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkextcmdsint")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkresrepfreq")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#mchkresreptime")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#mchkresfileage")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usesoftstatedeps")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#cachehchkhorizon")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#cacheschkhorizon")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enpredhostdepchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enpredsvcdepchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#svcchktimeout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hostchktimeout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#evthandtimeout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#notiftimeout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#retainstateinfo")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#retupdint")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useretstate")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useretsched")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#execsvcchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#exechostchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#acceptpsvcchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#acceptphostchks")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#ennotif")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enevnthand")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkorphsvc")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkorphhost")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chksvcfresh")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#svcfreshchkint")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkhostfresh")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hostfreshchkint")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#uselrginst")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enenvmacros")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usermatch")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usetrmatch")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useep")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useepi")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#chkforup")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#bareupchk")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useddc")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#dverb")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#addfreshlat")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useahc")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#maxhcs")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#maxscs")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usephcas")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usetphc")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#autoreschk")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#autoresint")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#autoreswin")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useobs")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#ocsptmo")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useobh")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#ochptmo")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enflapdet")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#lsft")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#lhft")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hsft")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hhft")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#enppd")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#perftmo")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#spfilem")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hpfilem")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#spfilepi")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#hpfilepi")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useeventbroker")
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
<script type="text/javascript">
function insertBrokerModule() {
    var bmod = $('#brokermodule').val();
    var deployment = '<?php echo $deployment?>';
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=nagioscfg&action=add_brokermod&bmod=' + encodeURIComponent(bmod)
            + '&deployment=' + encodeURIComponent(deployment),
        dataType: 'html',
        success: function( data ) {
            $('#brokerMods').attr('src', $('#brokerMods').attr('src'));
            $('#brokermodule').val('');
        }
    });
}
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
<input type="hidden" value="nagioscfg" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="write" id="action" name="action" />
<table class="noderesults">
    <thead>
        <th colspan="4">
            <div class="divCacGroup">
            Nagios Configuration for <?php echo $deployment?>
            </div>
        </th>
    </thead>
    <tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="filesdirs" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Files and Directories:
            </div>
            <div class="divHide parent-desc-filesdirs">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;width:20%;">
                            Log File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="logfile" name="logfile" value="<?php echo htmlspecialchars(base64_decode($cfg['log_file']))?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            Configuration Directory:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="cfgdir" name="cfgdir" value="<?php echo htmlspecialchars(base64_decode($cfg['cfg_dir']))?>" size="64" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;width:20%;">
                            Object Cache File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="objcachefile" name="objcachefile" size="64" maxlength="256" value="<?php echo htmlspecialchars(base64_decode($cfg['object_cache_file']))?>" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            PreCached Object File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="precacheobjfile" name="precacheobjfile" size="64" maxlength="256" value="<?php echo htmlspecialchars(base64_decode($cfg['precached_object_file']))?>" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;width:20%;">
                            Resource Config File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="resourcefile" name="resourcefile" size="64" maxlength="256" value="<?php echo htmlspecialchars(base64_decode($cfg['resource_file']))?>" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            Status File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="statusfile" name="statusfile" size="64" maxlength="256" value="<?php echo htmlspecialchars(base64_decode($cfg['status_file']))?>" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;width:20%;">
                            Lock File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="lockfile" name="lockfile" value="<?php echo htmlspecialchars(base64_decode($cfg['lock_file']))?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            Temp File:<br /><font size="2">Scratch File Space for Status Updates</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="tmpfile" name="tmpfile" value="<?php echo htmlspecialchars(base64_decode($cfg['temp_file']))?>" size="64" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;width:20%;">
                            Temp Path:<br /><font size="2">Used for creating tmp files if necessary</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="tmppath" name="tmppath" value="<?php echo htmlspecialchars(base64_decode($cfg['temp_path']))?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            Log Archive Path:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="logarcpath" name="logarcpath" value="<?php echo htmlspecialchars(base64_decode($cfg['log_archive_path']))?>" size="64" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;width:20%;">
                            Check Result Path:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="chkrespath" name="chkrespath" value="<?php echo htmlspecialchars(base64_decode($cfg['check_result_path']))?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            State Retention File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="stateretfile" name="stateretfile" value="<?php echo htmlspecialchars(base64_decode($cfg['state_retention_file']))?>" size="64" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;width:20%;">
                            Debug File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="debugfile" name="debugfile" value="<?php echo htmlspecialchars(base64_decode($cfg['debug_file']))?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;width:20%;">
                            P1 File:<br /><font size="2">Used by the embedded Perl interpreter</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="p1file" name="p1file" value="<?php echo htmlspecialchars(base64_decode($cfg['p1_file']))?>" size="64" maxlength="256" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="usrgrp" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                User and Group:
            </div>
            <div class="divHide parent-desc-usrgrp">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Nagios User:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="naguser" name="naguser" size="64" maxlength="72" value="<?php echo $cfg['nagios_user']?>" />
                        </td>
                        <th style="text-align:right;">
                            Nagios Group:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="naggrp" name="naggrp" size="64" maxlength="72" value="<?php echo $cfg['nagios_group']?>" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Admin Email:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="nagadmemail" name="nagadmemail" size="64" maxlength="72" value="<?php echo $cfg['admin_email']?>" />
                        </td>
                        <th style="text-align:right;">
                            Admin Pager:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="nagadmpager" name="nagadmpager" size="64" maxlength="72" value="<?php echo $cfg['admin_pager']?>" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="logsexcmds" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Log and External Commands:
            </div>
            <div class="divHide parent-desc-logsexcmds">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Status Update Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="statusupdateint" name="statusupdateint" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['status_update_interval'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['status_update_interval'],10)?>>10 Seconds</option>
                                <option value="20" <?php echo is_selected($cfg['status_update_interval'],20)?>>20 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['status_update_interval'],30)?>>30 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['status_update_interval'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Log Rotation Method:
                        </th>
                        <td style="text-align:left;">
                            <select id="logrotmethod" name="logrotmethod" multiple="multiple">
                                <option value="n" <?php echo is_selected($cfg['log_rotation_method'],'n')?>>Don't Rotate</option>
                                <option value="h" <?php echo is_selected($cfg['log_rotation_method'],'h')?>>Top of the Hour</option>
                                <option value="d" <?php echo is_selected($cfg['log_rotation_method'],'d')?>>Midnight Daily (Default)</option>
                                <option value="w" <?php echo is_selected($cfg['log_rotation_method'],'w')?>>Weekly at Midnight on Saturday</option>
                                <option value="m" <?php echo is_selected($cfg['log_rotation_method'],'m')?>>Monthly on Last Day at Midnight</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Syslog:<br /><font size="2">Logs info to Syslog as well</font>
                        </th>
                        <td style="text-align:left;">
                            <select id="usesyslog" name="usesyslog" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_syslog'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['use_syslog'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Log Notifications:
                        </th>
                        <td style="text-align:left;">
                            <select id="lognotifs" name="lognotifs" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_notifications'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['log_notifications'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Log Service Retries:
                        </th>
                        <td style="text-align:left;">
                            <select id="logsvcretries" name="logsvcretries" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_service_retries'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['log_service_retries'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Log Host Retries:
                        </th>
                        <td style="text-align:left;">
                            <select id="loghostretries" name="loghostretries" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_host_retries'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['log_host_retries'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Log Event Handlers:
                        </th>
                        <td style="text-align:left;">
                            <select id="logeventhandlers" name="logeventhandlers" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_event_handlers'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['log_event_handlers'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Log Initial States:
                        </th>
                        <td style="text-align:left;">
                            <select id="loginitstate" name="loginitstate" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_initial_states'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['log_initial_states'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Log Passive Checks
                        </th>
                        <td style="text-align:left;">
                            <select id="logpsvchks" name="logpsvchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_passive_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['log_passive_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Log External Commands:
                        </th>
                        <td style="text-align:left;">
                            <select id="logextcmds" name="logextcmds" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['log_external_commands'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['log_external_commands'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Check for External Commands:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkextcmds" name="chkextcmds" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['check_external_commands'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['check_external_commands'],1)?>>Enabled (Default) </option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Check External Command Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkextcmdsint" name="checkextcmdsint" multiple="multiple">
                                <option value="-1" <?php echo is_selected($cfg['command_check_interval'],-1)?>>Often As Possible (Default)</option>
                                <option value="15s" <?php echo is_selected($cfg['command_check_interval'],'15s')?>>Every 15 Seconds</option>
                                <option value="30s" <?php echo is_selected($cfg['command_check_interval'],'15s')?>>Every 30 Seconds</option>
                                <option value="45s" <?php echo is_selected($cfg['command_check_interval'],'15s')?>>Every 45 Seconds</option>
                                <option value="60s" <?php echo is_selected($cfg['command_check_interval'],'15s')?>>Every 60 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            External Command File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="extcmdfile" name="extcmdfile" value="<?php echo htmlspecialchars(base64_decode($cfg['command_file']))?>" size="40" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            External Command Buffer Slots:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="extcmdbuff" name="extcmdbuff" value="<?php echo $cfg['external_command_buffer_slots']?>" size="4" maxlength="4" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="resrel" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Results and State Related:
            </div>
            <div class="divHide parent-desc-resrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Check Result Reaper Frequency:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkresrepfreq" name="chkresrepfreq" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['check_result_reaper_frequency'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['check_result_reaper_frequency'],10)?>>10 Seconds (Default)</option>
                                <option value="15" <?php echo is_selected($cfg['check_result_reaper_frequency'],15)?>>15 Seconds</option>
                                <option value="20" <?php echo is_selected($cfg['check_result_reaper_frequency'],20)?>>20 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['check_result_reaper_frequency'],30)?>>30 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Max Check Result Reaper Time:
                        </th>
                        <td style="text-align:left;">
                            <select id="mchkresreptime" name="mchkresreptime" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['max_check_result_reaper_time'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['max_check_result_reaper_time'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['max_check_result_reaper_time'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['max_check_result_reaper_time'],30)?>>30 Seconds (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['max_check_result_reaper_time'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['max_check_result_reaper_time'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Max Check Result File Age:
                        </th>
                        <td style="text-align:left;">
                            <select id="mchkresfileage" name="mchkresfileage" multiple="multiple">
                                <option value="900" <?php echo is_selected($cfg['max_check_result_file_age'],900)?>>15 Minutes</option>
                                <option value="1800" <?php echo is_selected($cfg['max_check_result_file_age'],1800)?>>30 Minutes</option>
                                <option value="2700" <?php echo is_selected($cfg['max_check_result_file_age'],2700)?>>45 Minutes</option>
                                <option value="3600" <?php echo is_selected($cfg['max_check_result_file_age'],3600)?>>60 Minutes (Default)</option>
                                <option value="4500" <?php echo is_selected($cfg['max_check_result_file_age'],4500)?>>75 Minutes</option>
                                <option value="5400" <?php echo is_selected($cfg['max_check_result_file_age'],5400)?>>90 Minutes</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Use Soft States for Dependencies:
                        </th>
                        <td style="text-align:left;">
                            <select id="usesoftstatedeps" name="usesoftstatedeps" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['soft_state_dependencies'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['soft_state_dependencies'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Cache Host Check Horizon:
                        </th>
                        <td style="text-align:left;">
                            <select id="cachehchkhorizon" name="cachehchkhorizon" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['cached_host_check_horizon'],0)?>>Disabled</option>
                                <option value="5" <?php echo is_selected($cfg['cached_host_check_horizon'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['cached_host_check_horizon'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['cached_host_check_horizon'],15)?>>15 Seconds (Default)</option>
                                <option value="30" <?php echo is_selected($cfg['cached_host_check_horizon'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['cached_host_check_horizon'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['cached_host_check_horizon'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Cache Service Check Horizon:
                        </th>
                        <td style="text-align:left;">
                            <select id="cacheschkhorizon" name="cacheschkhorizon" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['cached_service_check_horizon'],0)?>>Disabled</option>
                                <option value="5" <?php echo is_selected($cfg['cached_service_check_horizon'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['cached_service_check_horizon'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['cached_service_check_horizon'],15)?>>15 Seconds (Default)</option>
                                <option value="30" <?php echo is_selected($cfg['cached_service_check_horizon'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['cached_service_check_horizon'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['cached_service_check_horizon'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Enable Notifications:
                        </th>
                        <td style="text-align:left;">
                            <select id="ennotif" name="ennotif" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_notifications'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['enable_notifications'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Enable Event Handlers:
                        </th>
                        <td style="text-align:left;">
                            <select id="enevnthand" name="enevnthand" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_event_handlers'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['enable_event_handlers'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Notification Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="notiftimeout" name="notiftimeout" multiple="multiple">
                                <option value="15" <?php echo is_selected($cfg['notification_timeout'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['notification_timeout'],30)?>>30 Seconds (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['notification_timeout'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['notification_timeout'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Event Handler Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="evthandtimeout" name="evthandtimeout" multiple="multiple">
                                <option value="15" <?php echo is_selected($cfg['event_handler_timeout'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['event_handler_timeout'],30)?>>30 Seconds (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['event_handler_timeout'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['event_handler_timeout'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Retain State Information:
                        </th>
                        <td style="text-align:left;">
                            <select id="retainstateinfo" name="retainstateinfo" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['retain_state_information'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['retain_state_information'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Retention Update Interval
                        </th>
                        <td style="text-align:left;">
                            <select id="retupdint" name="retupdint" multiple="multiple">
                                <option value="30" <?php echo is_selected($cfg['retention_update_interval'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['retention_update_interval'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['retention_update_interval'],60)?>>60 Seconds (Default)</option>
                                <option value="75" <?php echo is_selected($cfg['retention_update_interval'],75)?>>75 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['retention_update_interval'],90)?>>90 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Retained Program State:
                        </th>
                        <td style="text-align:left;">
                            <select id="useretstate" name="useretstate" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_retained_program_state'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['use_retained_program_state'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Use Retained Scheduling Information:
                        </th>
                        <td style="text-align:left;">
                            <select id="useretsched" name="useretsched" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_retained_scheduling_info'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['use_retained_scheduling_info'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Retained Host Attribute Mask:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="userham" name="userham" value="<?php echo $cfg['retained_host_attribute_mask']?>" size="4" maxlength="8" />
                        </td>
                        <th style="text-align:right;">
                            Use Retained Service Attribute Mask:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="usersam" name="usersam" value="<?php echo $cfg['retained_service_attribute_mask']?>" size="4" maxlength="8" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Retained Process Host Attribute Mask:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="userpham" name="userpham" value="<?php echo $cfg['retained_process_host_attribute_mask']?>" size="4" maxlength="8" />
                        </td>
                        <th style="text-align:right;">
                            Use Retained Process Service Attribute Mask:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="userpsam" name="userpsam" value="<?php echo $cfg['retained_process_service_attribute_mask']?>" size="4" maxlength="8" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Retained Contact Host Attribute Mask:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="usercham" name="usercham" value="<?php echo $cfg['retained_contact_host_attribute_mask']?>" size="4" maxlength="8" />
                        </td>
                        <th style="text-align:right;">
                            Use Retained Contact Service Attribute Mask:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="usercsam" name="usercsam" value="<?php echo $cfg['retained_contact_service_attribute_mask']?>" size="4" maxlength="8" />
                        </td>
                    </tr>
                </table>
            <div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="chkrel" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Check Related:
            </div>
            <div class="divHide parent-desc-chkrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Execute Service Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="execsvcchks" name="execsvcchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['execute_service_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['execute_service_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Execute Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="exechostchks" name="exechostchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['execute_host_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['execute_host_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Inter Check Delay Method:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="svcicdm" name="svcicdm" value="<?php echo $cfg['service_inter_check_delay_method']?>" size="4" maxlength="8" />
                        </td>
                        <th style="text-align:right;">
                            Host Inter Check Delay Method:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="hosticdm" name="hosticdm" value="<?php echo $cfg['host_inter_check_delay_method']?>" size="4" maxlength="8" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Max Service Check Spread:
                        </th>
                        <td style="text-align:left;">
                            <select id="maxscs" name="maxscs" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['max_service_check_spread'],5)?>>5 Minutes</option>
                                <option value="15" <?php echo is_selected($cfg['max_service_check_spread'],15)?>>15 Minutes</option>
                                <option value="30" <?php echo is_selected($cfg['max_service_check_spread'],30)?>>30 Minutes (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['max_service_check_spread'],45)?>>45 Minutes</option>
                                <option value="60" <?php echo is_selected($cfg['max_service_check_spread'],60)?>>60 Minutes</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Max Host Check Spread:
                        </th>
                        <td style="text-align:left;">
                            <select id="maxhcs" name="maxhcs" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['max_host_check_spread'],5)?>>5 Minutes</option>
                                <option value="15" <?php echo is_selected($cfg['max_host_check_spread'],15)?>>15 Minutes</option>
                                <option value="30" <?php echo is_selected($cfg['max_host_check_spread'],30)?>>30 Minutes (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['max_host_check_spread'],45)?>>45 Minutes</option>
                                <option value="60" <?php echo is_selected($cfg['max_host_check_spread'],60)?>>60 Minutes</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Interleave Factor:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="svcif" name="svcif" value="<?php echo $cfg['service_interleave_factor']?>" size="4" maxlength="8" />
                        </td>
                        <th style="text-align:right;">
                            Use Aggressive Host Checking:
                        </th>
                        <td style="text-align:left;">
                            <select id="useahc" name="useahc" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_aggressive_host_checking'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['use_aggressive_host_checking'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Accept Passive Service Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="acceptpsvcchks" name="acceptpsvcchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['accept_passive_service_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['accept_passive_service_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Accept Passive Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="acceptphostchks" name="acceptphostchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['accept_passive_host_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['accept_passive_host_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <td></td>
                        <td></td>
                        <th style="text-align:right;">
                            Passive Host Checks are Soft:
                        </th>
                        <td style="text-align:left;">
                            <select id="usephcas" name="usephcas" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['passive_host_checks_are_soft'],0)?>>Hard (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['passive_host_checks_are_soft'],1)?>>Soft</option>
                            </select>
                        </td>
                    </tr><tr>
                        <td></td>
                        <td></td>
                        <th style="text-align:right;">
                            Translate Passive Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="usetphc" name="usetphc" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['translate_passive_host_checks'],0)?>>Don't Translate (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['translate_passive_host_checks'],1)?>>Translate</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Predictive Service Dependency Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="enpredsvcdepchks" name="enpredsvcdepchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_predictive_service_dependency_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['enable_predictive_service_dependency_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Predictive Host Dependency Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="enpredhostdepchks" name="enpredhostdepchks" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_predictive_host_dependency_checks'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['enable_predictive_host_dependency_checks'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Check Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="svcchktimeout" name="svcchktimeout" multiple="multiple">
                                <option value="15" <?php echo is_selected($cfg['service_check_timeout'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['service_check_timeout'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['service_check_timeout'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['service_check_timeout'],60)?>>60 Seconds (Default)</option>
                                <option value="75" <?php echo is_selected($cfg['service_check_timeout'],75)?>>75 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['service_check_timeout'],90)?>>90 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Host Check Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="hostchktimeout" name="hostchktimeout" multiple="multiple">
                                <option value="15" <?php echo is_selected($cfg['host_check_timeout'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['host_check_timeout'],30)?>>30 Seconds (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['host_check_timeout'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['host_check_timeout'],60)?>>60 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Check for Orphaned Service Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkorphsvc" name="chkorphsvc" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['check_for_orphaned_services'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['check_for_orphaned_services'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Check for Orphaned Host Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkorphhost" name="chkorphhost" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['check_for_orphaned_hosts'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['check_for_orphaned_hosts'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Check Service Freshness:
                        </th>
                        <td style="text-align:left;">
                            <select id="chksvcfresh" name="chksvcfresh" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['check_service_freshness'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['check_service_freshness'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Check Host Freshness:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkhostfresh" name="chkhostfresh" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['check_host_freshness'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['check_host_freshness'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Freshness Check Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="svcfreshchkint" name="svcfreshchkint" multiple="multiple">
                                <option value="30" <?php echo is_selected($cfg['service_freshness_check_interval'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['service_freshness_check_interval'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['service_freshness_check_interval'],60)?>>60 Seconds (Default)</option>
                                <option value="75" <?php echo is_selected($cfg['service_freshness_check_interval'],75)?>>75 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['service_freshness_check_interval'],90)?>>90 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Host Freshness Check Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="hostfreshchkint" name="hostfreshchkint" multiple="multiple">
                                <option value="30" <?php echo is_selected($cfg['host_freshness_check_interval'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['host_freshness_check_interval'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['host_freshness_check_interval'],60)?>>60 Seconds (Default)</option>
                                <option value="75" <?php echo is_selected($cfg['host_freshness_check_interval'],75)?>>75 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['host_freshness_check_interval'],90)?>>90 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Additional Freshness Latency:
                        </th>
                        <td style="text-align:left;">
                            <select id="addfreshlat" name="addfreshlat" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['additional_freshness_latency'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['additional_freshness_latency'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['additional_freshness_latency'],15)?>>15 Seconds (Default)</option>
                                <option value="30" <?php echo is_selected($cfg['additional_freshness_latency'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['additional_freshness_latency'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['additional_freshness_latency'],60)?>>60 Seconds</option>
                                <option value="75" <?php echo is_selected($cfg['additional_freshness_latency'],75)?>>75 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['additional_freshness_latency'],90)?>>90 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Max Concurrent Checks:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="maxccc" name="maxccc" value="<?php echo $cfg['max_concurrent_checks']?>" size="8" maxlength="12" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Auto Reschedule Checks:
                        </th>
                        <td style="text-align:left;">
                            <select id="autoreschk" name="autoreschk" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['auto_reschedule_checks'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['auto_reschedule_checks'],1)?>>Enabled</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Auto Reschedule Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="autoresint" name="autoresint" multiple="multiple">
                                <option value="15" <?php echo is_selected($cfg['auto_rescheduling_interval'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['auto_rescheduling_interval'],30)?>>30 Seconds (Default)</option>
                                <option value="45" <?php echo is_selected($cfg['auto_rescheduling_interval'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['auto_rescheduling_interval'],60)?>>60 Seconds</option>
                                <option value="75" <?php echo is_selected($cfg['auto_rescheduling_interval'],75)?>>75 Seconds</option>
                                <option value="90" <?php echo is_selected($cfg['auto_rescheduling_interval'],90)?>>90 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Auto Rescheduling Window:
                        </th>
                        <td style="text-align:left;">
                            <select id="autoreswin" name="autoreswin" multiple="multiple">
                                <option value="60" <?php echo is_selected($cfg['auto_rescheduling_window'],60)?>>1 Minute</option>
                                <option value="120" <?php echo is_selected($cfg['auto_rescheduling_window'],120)?>>2 Minutes</option>
                                <option value="180" <?php echo is_selected($cfg['auto_rescheduling_window'],180)?>>3 Minutes (Default)</option>
                                <option value="240" <?php echo is_selected($cfg['auto_rescheduling_window'],240)?>>4 Minutes</option>
                                <option value="300" <?php echo is_selected($cfg['auto_rescheduling_window'],300)?>>5 Minutes</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Obsess Over Services:
                        </th>
                        <td style="text-align:left;">
                            <select id="useobs" name="useobs" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['obsess_over_services'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['obsess_over_services'],1)?>>Enabled</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Obsess Over Hosts:
                        </th>
                        <td style="text-align:left;">
                            <select id="useobh" name="useobh" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['obsess_over_hosts'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['obsess_over_hosts'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            OCSP Command:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="ocspcmd" name="ocspcmd" value="<?php echo $cfg['ocsp_command']?>" size="64" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            OCHP Command:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="ochpcmd" name="ochpcmd" value="<?php echo $cfg['ochp_command']?>" size="64" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            OCSP Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="ocsptmo" name="ocsptmo" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['ocsp_timeout'],5)?>>5 Seconds (Default)</option>
                                <option value="10" <?php echo is_selected($cfg['ocsp_timeout'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['ocsp_timeout'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['ocsp_timeout'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['ocsp_timeout'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['ocsp_timeout'],60)?>>60 Seconds</option>
                                <option value="75" <?php echo is_selected($cfg['ocsp_timeout'],75)?>>75 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            OCHP Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="ochptmo" name="ochptmo" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['ochp_timeout'],5)?>>5 Seconds (Default)</option>
                                <option value="10" <?php echo is_selected($cfg['ochp_timeout'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['ochp_timeout'],15)?>>15 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['ochp_timeout'],30)?>>30 Seconds</option>
                                <option value="45" <?php echo is_selected($cfg['ochp_timeout'],45)?>>45 Seconds</option>
                                <option value="60" <?php echo is_selected($cfg['ochp_timeout'],60)?>>60 Seconds</option>
                                <option value="75" <?php echo is_selected($cfg['ochp_timeout'],75)?>>75 Seconds</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="flaprel" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Flap Detection Settings:
            </div>
            <div class="divHide parent-desc-flaprel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Enable Flap Detection:
                        </th>
                        <td style="text-align:left;">
                            <select id="enflapdet" name="enflapdet" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_flap_detection'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['enable_flap_detection'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Low Service Flap Threshold:
                        </th>
                        <td style="text-align:left;">
                            <select id="lsft" name="lsft" multiple="multiple">
                                <option value="5.0" <?php echo is_selected($cfg['low_service_flap_threshold'],5.0)?>>5.0 (Default)</option>
                                <option value="10.0" <?php echo is_selected($cfg['low_service_flap_threshold'],10.0)?>>10.0</option>
                                <option value="15.0" <?php echo is_selected($cfg['low_service_flap_threshold'],15.0)?>>15.0</option>
                                <option value="20.0" <?php echo is_selected($cfg['low_service_flap_threshold'],20.0)?>>20.0</option>
                                <option value="25.0" <?php echo is_selected($cfg['low_service_flap_threshold'],25.0)?>>25.0</option>
                                <option value="30.0" <?php echo is_selected($cfg['low_service_flap_threshold'],30.0)?>>30.0</option>
                                <option value="35.0" <?php echo is_selected($cfg['low_service_flap_threshold'],35.0)?>>35.0</option>
                                <option value="40.0" <?php echo is_selected($cfg['low_service_flap_threshold'],40.0)?>>40.0</option>
                                <option value="45.0" <?php echo is_selected($cfg['low_service_flap_threshold'],45.0)?>>45.0</option>
                                <option value="50.0" <?php echo is_selected($cfg['low_service_flap_threshold'],50.0)?>>50.0</option>
                            </select>
                        <th style="text-align:right;">
                            Low Host Flap Threshold:
                        </th>
                        <td style="text-align:left;">
                            <select id="lhft" name="lhft" multiple="multiple">
                                <option value="5.0" <?php echo is_selected($cfg['low_host_flap_threshold'],5.0)?>>5.0 (Default)</option>
                                <option value="10.0" <?php echo is_selected($cfg['low_host_flap_threshold'],10.0)?>>10.0</option>
                                <option value="15.0" <?php echo is_selected($cfg['low_host_flap_threshold'],15.0)?>>15.0</option>
                                <option value="20.0" <?php echo is_selected($cfg['low_host_flap_threshold'],20.0)?>>20.0</option>
                                <option value="25.0" <?php echo is_selected($cfg['low_host_flap_threshold'],25.0)?>>25.0</option>
                                <option value="30.0" <?php echo is_selected($cfg['low_host_flap_threshold'],30.0)?>>30.0</option>
                                <option value="35.0" <?php echo is_selected($cfg['low_host_flap_threshold'],35.0)?>>35.0</option>
                                <option value="40.0" <?php echo is_selected($cfg['low_host_flap_threshold'],40.0)?>>40.0</option>
                                <option value="45.0" <?php echo is_selected($cfg['low_host_flap_threshold'],45.0)?>>45.0</option>
                                <option value="50.0" <?php echo is_selected($cfg['low_host_flap_threshold'],50.0)?>>50.0</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            High Service Flap Threshold:
                        </th>
                        <td style="text-align:left;">
                            <select id="hsft" name="hsft" multiple="multiple">
                                <option value="10.0" <?php echo is_selected($cfg['high_service_flap_threshold'],10.0)?>>10.0</option>
                                <option value="15.0" <?php echo is_selected($cfg['high_service_flap_threshold'],15.0)?>>15.0</option>
                                <option value="20.0" <?php echo is_selected($cfg['high_service_flap_threshold'],20.0)?>>20.0 (Default)</option>
                                <option value="25.0" <?php echo is_selected($cfg['high_service_flap_threshold'],25.0)?>>25.0</option>
                                <option value="30.0" <?php echo is_selected($cfg['high_service_flap_threshold'],30.0)?>>30.0</option>
                                <option value="35.0" <?php echo is_selected($cfg['high_service_flap_threshold'],35.0)?>>35.0</option>
                                <option value="40.0" <?php echo is_selected($cfg['high_service_flap_threshold'],40.0)?>>40.0</option>
                                <option value="45.0" <?php echo is_selected($cfg['high_service_flap_threshold'],45.0)?>>45.0</option>
                                <option value="50.0" <?php echo is_selected($cfg['high_service_flap_threshold'],50.0)?>>50.0</option>
                            </select>
                        <th style="text-align:right;">
                            High Host Flap Threshold:
                        </th>
                        <td style="text-align:left;">
                            <select id="hhft" name="hhft" multiple="multiple">
                                <option value="10.0" <?php echo is_selected($cfg['high_host_flap_threshold'],10.0)?>>10.0</option>
                                <option value="15.0" <?php echo is_selected($cfg['high_host_flap_threshold'],15.0)?>>15.0</option>
                                <option value="20.0" <?php echo is_selected($cfg['high_host_flap_threshold'],20.0)?>>20.0 (Default)</option>
                                <option value="25.0" <?php echo is_selected($cfg['high_host_flap_threshold'],25.0)?>>25.0</option>
                                <option value="30.0" <?php echo is_selected($cfg['high_host_flap_threshold'],30.0)?>>30.0</option>
                                <option value="35.0" <?php echo is_selected($cfg['high_host_flap_threshold'],35.0)?>>35.0</option>
                                <option value="40.0" <?php echo is_selected($cfg['high_host_flap_threshold'],40.0)?>>40.0</option>
                                <option value="45.0" <?php echo is_selected($cfg['high_host_flap_threshold'],45.0)?>>45.0</option>
                                <option value="50.0" <?php echo is_selected($cfg['high_host_flap_threshold'],50.0)?>>50.0</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="perfrel" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Performance Data Settings:
            </div>
            <div class="divHide parent-desc-perfrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Enable Process Performance Data:
                        </th>
                        <td style="text-align:left;">
                            <select id="enppd" name="enppd" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['process_performance_data'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['process_performance_data'],1)?>>Enabled</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Perfdata Timeout:
                        </th>
                        <td style="text-align:left;">
                            <select id="perftmo" name="perftmo" multiple="multiple">
                                <option value="5" <?php echo is_selected($cfg['perfdata_timeout'],5)?>>5 Seconds (Default)</option>
                                <option value="10" <?php echo is_selected($cfg['perfdata_timeout'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['perfdata_timeout'],15)?>>15 Seconds</option>
                                <option value="20" <?php echo is_selected($cfg['perfdata_timeout'],20)?>>20 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['perfdata_timeout'],30)?>>30 Seconds</option>
                                <option value="40" <?php echo is_selected($cfg['perfdata_timeout'],40)?>>40 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Perfdata Command:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="spcmd" name="spcmd" value="<?php echo htmlspecialchars(base64_decode($cfg['service_perfdata_command']))?>" size="48" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            Host Perfdata Command:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="hpcmd" name="hpcmd" value="<?php echo htmlspecialchars(base64_decode($cfg['host_perfdata_command']))?>" size="48" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Perfdata File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="spfile" name="spfile" value="<?php echo htmlspecialchars(base64_decode($cfg['service_perfdata_file']))?>" size="48" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            Host Perfdata File:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="hpfile" name="hpfile" value="<?php echo htmlspecialchars(base64_decode($cfg['host_perfdata_file']))?>" size="48" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Perfdata File Mode:
                        </th>
                        <td style="text-align:left;">
                            <select id="spfilem" name="spfilem" multiple="multiple">
                                <option value="a" <?php echo is_selected($cfg['service_perfdata_file_mode'],'a')?>>Append (Default)</option>
                                <option value="w" <?php echo is_selected($cfg['service_perfdata_file_mode'],'w')?>>Write</option>
                                <option value="p" <?php echo is_selected($cfg['service_perfdata_file_mode'],'p')?>>Pipes</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Host Perfdata File Mode:
                        </th>
                        <td style="text-align:left;">
                            <select id="hpfilem" name="hpfilem" multiple="multiple">
                                <option value="a" <?php echo is_selected($cfg['host_perfdata_file_mode'],'a')?>>Append (Default)</option>
                                <option value="w" <?php echo is_selected($cfg['host_perfdata_file_mode'],'w')?>>Write</option>
                                <option value="p" <?php echo is_selected($cfg['host_perfdata_file_mode'],'p')?>>Pipes</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Perfdata File Processing Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="spfilepi" name="spfilepi" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],0)?>>0 (Default / Disabled)</option>
                                <option value="5" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],15)?>>15 Seconds</option>
                                <option value="20" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],20)?>>20 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],30)?>>30 Seconds</option>
                                <option value="40" <?php echo is_selected($cfg['service_perfdata_file_processing_interval'],40)?>>40 Seconds</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Host Perfdata File Processing Interval:
                        </th>
                        <td style="text-align:left;">
                            <select id="hpfilepi" name="hpfilepi" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],0)?>>0 (Default / Disabled)</option>
                                <option value="5" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],5)?>>5 Seconds</option>
                                <option value="10" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],10)?>>10 Seconds</option>
                                <option value="15" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],15)?>>15 Seconds</option>
                                <option value="20" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],20)?>>20 Seconds</option>
                                <option value="30" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],30)?>>30 Seconds</option>
                                <option value="40" <?php echo is_selected($cfg['host_perfdata_file_processing_interval'],40)?>>40 Seconds</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Perfdata File Processing Command:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="spfilepc" name="spfilepc" value="<?php echo htmlspecialchars(base64_decode($cfg['service_perfdata_file_processing_command']))?>" size="48" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            Host Perfdata File Processing Command:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="hpfilepc" name="hpfilepc" value="<?php echo htmlspecialchars(base64_decode($cfg['host_perfdata_file_processing_command']))?>" size="48" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Service Perfdata File Template:
                        </th>
                        <td colspan="3" style="text-align:left;">
                            <input type="text" id="spfilet" name="spfilet" value="<?php echo htmlspecialchars(base64_decode($cfg['service_perfdata_file_template']))?>" size="192" maxlength="512" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Host Perfdata File Template:
                        </th>
                        <td colspan="3" style="text-align:left;">
                            <input type="text" id="hpfilet" name="hpfilet" value="<?php echo htmlspecialchars(base64_decode($cfg['host_perfdata_file_template']))?>" size="192" maxlength="512" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="miscrel" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Miscellaneous Settings:
            </div>
            <div class="divHide parent-desc-miscrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Date Format:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="dateformat" name="dateformat" value="<?php echo $cfg['date_format']?>" size="48" maxlength="64" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Interval Length:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="intlength" name="intlength" value="<?php echo $cfg['interval_length']?>" size="4" maxlength="6" />
                        </td>
                        <th style="text-align:right;">
                            Sleep Time:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="sleept" name="sleept" value="<?php echo $cfg['sleep_time']?>" size="4" maxlength="6" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Check For Updates:
                        </th>
                        <td style="text-align:left;">
                            <select id="chkforup" name="chkforup" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['check_for_updates'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['check_for_updates'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Bare Update Check:
                        </th>
                        <td style="text-align:left;">
                            <select id="bareupchk" name="bareupchk" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['bare_update_check'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['bare_update_check'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Illegal Object Name Characters:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="illegalobj" name="illegalobj" value="<?php echo htmlspecialchars(base64_decode($cfg['illegal_object_name_chars']))?>" size="40" maxlength="256" />
                        </td>
                        <th style="text-align:right;">
                            Illegal Macro Output Characters:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="illegalmacro" name="illegalmacro" value="<?php echo htmlspecialchars(base64_decode($cfg['illegal_macro_output_chars']))?>" size="40" maxlength="256" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Large Installation Tweaks:
                        </th>
                        <td style="text-align:left;">
                            <select id="uselrginst" name="uselrginst" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_large_installation_tweaks'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['use_large_installation_tweaks'],1)?>>Enabled</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Enable Environment Macros:
                        </th>
                        <td style="text-align:left;">
                            <select id="enenvmacros" name="enenvmacros" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_environment_macros'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['enable_environment_macros'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Use Regex Matching:
                        </th>
                        <td style="text-align:left;">
                            <select id="usermatch" name="usermatch" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_regexp_matching'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['use_regexp_matching'],1)?>>Enabled</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Use True Regex Matching:
                        </th>
                        <td style="text-align:left;">
                            <select id="usetrmatch" name="usetrmatch" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_true_regexp_matching'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['use_true_regexp_matching'],1)?>>Enabled</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Daemon Dumps Core:
                        </th>
                        <td style="text-align:left;">
                            <select id="useddc" name="useddc" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['daemon_dumps_core'],0)?>>Disabled (Default)</option>
                                <option value="1" <?php echo is_selected($cfg['daemon_dumps_core'],1)?>>Enabled</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Debug Level:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="dlevel" name="dlevel" value="<?php echo $cfg['debug_level']?>" size="4" maxlength="8" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Debug Verbosity:
                        </th>
                        <td style="text-align:left;">
                            <select id="dverb" name="dverb" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['debug_verbosity'],0)?>>Brief Details</option>
                                <option value="1" <?php echo is_selected($cfg['debug_verbosity'],1)?>>More Detailed</option>
                                <option value="2" <?php echo is_selected($cfg['debug_verbosity'],2)?>>Very Detailed</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Max Debug File Size:
                        </th>
                        <td style="text-align:left;">
                            <input type="text" id="maxdfs" name="maxdfs" value="<?php echo $cfg['max_debug_file_size']?>" size="12" maxlength="16" />
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Enable Embedded Perl:
                        </th>
                        <td style="text-align:left;">
                            <select id="useep" name="useep" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['enable_embedded_perl'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['enable_embedded_perl'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                        <th style="text-align:right;">
                            Use Embedded Perl Implicitly:
                        </th>
                        <td style="text-align:left;">
                            <select id="useepi" name="useepi" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['use_embedded_perl_implicitly'],0)?>>Disabled</option>
                                <option value="1" <?php echo is_selected($cfg['use_embedded_perl_implicitly'],1)?>>Enabled (Default)</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="ebrel" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Event Broker Settings:
            </div>
            <div class="divHide parent-desc-ebrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;">
                            Use Event Broker:
                        </th>
                        <td style="text-align:left;">
                            <select id="useeventbroker" name="useeventbroker" multiple="multiple">
                                <option value="0" <?php echo is_selected($cfg['event_broker_options'],0)?>>Broker Nothing</option>
                                <option value="-1" <?php echo is_selected($cfg['event_broker_options'],-1)?>>Broker Everything (Default)</option>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="text-align:right;">
                            Broker Module:
                        </th>
                        <td style="text-align:left;width:60%;" colspan="2">
                            <input type="text" id="brokermodule" name="brokermodule" value="" size="128" maxlength="256" />
                        </td>
                        <td>
                            <input type="submit" value="Insert" style="font-size:14px;" onClick="insertBrokerModule(); return false;">
                        </td>
                    <tr>
                        <td colspan="4">
                            <div id="bmod-examples">
                                <table style="width=99%;">
                                    <tr>
                                        <th colspan="3">
                                            Example Broker Module Lines
                                        </th>
                                    </tr><tr>
                                        <th style="text-align:left;">
                                            ModGearman v1.0.10:
                                        </th>
                                        <td colspan="2" style="background-color:#91C5D4;">
                                            /usr/lib64/mod_gearman/mod_gearman.o config=/usr/local/etc/mod_gearman.conf
                                        </td>
                                    </tr><tr>
                                        <th style="text-align:left;">
                                            ModGearman v1.4.2:
                                        </th>
                                        <td colspan="2" style="background-color:#91C5D4;">
                                            /usr/lib64/mod_gearman/mod_gearman.o config=/etc/mod_gearman/mod_gearman_neb.conf
                                        </td>
                                    </tr><tr>
                                        <th style="text-align:left;">
                                            Livestatus:
                                        </th>
                                        <td colspan="2" style="background-color:#91C5D4;">
                                            /usr/local/lib/mk-livestatus/livestatus.o /usr/local/nagios/var/rw/live max_response_size=314572800 thread_stack_size=524288 query_timeout=20000
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr><tr>
                        <td colspan="4">
                            <iframe
                                height="200px" name="brokerMods" id="brokerMods" style="min-height:100px;width:97%;left:5px;"
                                src="action.php?controller=nagioscfg&action=view_brokermods&deployment=<?php echo $deployment?>">
                            </iframe>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="delrel" style="text-align:left;text-indent:25px;background-color:#FE2E2E;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Delete Nagios Configuration:
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

function is_selected($var,$check) {
    if ($var == $check) return "selected";
    return "";
}

require HTML_FOOTER;
