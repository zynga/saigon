<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$jsdata = json_encode($viewData->nrpecmds);

$filelocation = isset($viewData->nrpecfg['location'])?$viewData->nrpecfg['location']:'/usr/local/nagios/etc/nrpe.cfg';
$pidfile = isset($viewData->nrpecfg['pid_file'])?$viewData->nrpecfg['pid_file']:'/var/run/nrpe.pid';
$port = isset($viewData->nrpecfg['server_port'])?$viewData->nrpecfg['server_port']:'5666';
$user = isset($viewData->nrpecfg['nrpe_user'])?$viewData->nrpecfg['nrpe_user']:'nagios';
$group = isset($viewData->nrpecfg['nrpe_group'])?$viewData->nrpecfg['nrpe_group']:'nagios';
$dontblame = (isset($viewData->nrpecfg['dont_blame_nrpe']) && ($viewData->nrpecfg['dont_blame_nrpe'] != 0))?'checked':'';
$debug = (isset($viewData->nrpecfg['debug']) && ($viewData->nrpecfg['debug'] != 0))?'checked':'';
$cmdtimeout = isset($viewData->nrpecfg['command_timeout'])?$viewData->nrpecfg['command_timeout']:'60';
$conntimeout = isset($viewData->nrpecfg['connection_timeout'])?$viewData->nrpecfg['connection_timeout']:'300';
$allowedhosts = isset($viewData->nrpecfg['allowed_hosts'])?$viewData->nrpecfg['allowed_hosts']:'127.0.0.1';
$includedir = isset($viewData->nrpecfg['include_dir'])?$viewData->nrpecfg['include_dir']:'/usr/local/nagios/etc/nrpe.d';
$nrpecmds = isset($viewData->nrpecfg['cmds'])?$viewData->nrpecfg['cmds']:array();

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#nrpecmds")
        .multiselect({
            noneSelectedText: "Select Commands",
        }).multiselectfilter();
});
</script>
<script type="text/javascript">
$(function() {
    var target = $("#nrpecmdwindow");
    var data = <?php echo $jsdata?>;
    $("select")
        .multiselect()
        .bind("multiselectclick multiselectcheckall multiselectuncheckall",
            function( event, ui ) {
                var checkedValues = $.map($(this).multiselect("getChecked"),
                    function( input ) {
                        return input.value;
                    });
                if (checkedValues.length > 0) {
                    var output;
                    $.each(checkedValues, function() {
                        if (output == undefined) {
                            output = data[this] + '<br />';
                        } else {
                            output += data[this] + '<br />';
                        }
                    });
                    target.html (
                        output
                    );
                } else {
                    target.html (
                        'Please Select Some Commands'
                    );
                }
            })
        .triggerHandler("multiselectclick");
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
<div id="nrpecfg" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="nrpecfg" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="nrpe_cfg_write">
<input type="hidden" value="nrpecfg" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="write" id="action" name="action" />
<table class="noderesults">
    <thead>
        <th colspan="4">NRPE Configuration File Management for <?php echo $deployment?></th>
    </thead>
    <tr>
        <td colspan="4">
            <div style="height:25px;padding-top:10px;" class="divCacGroup divCacSubResponse">
                Leave the Available Commands Empty, if you want to delete / nullify the supplemental config
            </div>
        </td>
    </tr><tr>
        <th style="width:25%;text-align:right;">File Location:</th>
        <td style="width:25%;text-align:left;" colspan="3">
            <input type="text" value="<?php echo $filelocation?>" size="64" maxlength="128" id="location" name="location" />
        </td>
    </tr><tr>
        <th style="width:25%;text-align:right;">PID File:</th>
        <td style="width:25%;text-align:left;">
            <input type="text" value="<?php echo $pidfile?>" size="32" maxlength="128" id="pidfile" name="pidfile" />
        </td>
        <th style="width:25%;text-align:right;">Listen Port:</th>
        <td style="width:25%;text-align:left;">
            <input type="text" value="<?php echo $port?>" size="5" maxlength="5" id="port" name="port" />
        </td>
    </tr><tr>
        <th style="width:25%;text-align:right;">Run as User:</th>
        <td style="width:25%;text-align:left;">
            <input type="text" value="<?php echo $user?>" size="8" maxlength="32" id="user" name="user" />
        </td>
        <th style="width:25%;text-align:right;">Run as Group:</th>
        <td style="width:25%;text-align:left;">
            <input type="text" value="<?php echo $group?>" size="8" maxlength="32" id="group" name="group" />
        </td>
    </tr><tr>
        <th style="width:25%;text-align:right;">
            Don't Blame NRPE:<br />
            <font size="2">Enable Passing of Command Args</font>
        </th>
        <td style="width:25%;text-align:left;">
            <input type="checkbox" value="1" id="dontblame" name="dontblame" <?php echo $dontblame?> />
        </td>
        <th style="width:25%;text-align:right;">
            Debug:<br />
            <font size="2">Log to Syslog</font>
        </th>
        <td style="width:25%;text-align:left;">
            <input type="checkbox" value="1" id="debug" name="debug" <?php echo $debug?> />
        </td>
    </tr><tr>
        <th style="width:25%;text-align:right;">Command Timeout:</th>
        <td style="width:25%;text-align:left;">
            <input type="text" value="<?php echo $cmdtimeout?>" size="2" maxlength="2" id="cmdtimeout" name="cmdtimeout" />
        </td>
        <th style="width:25%;text-align:right;">Connection Timeout:</th>
        <td style="width:25%;text-align:left;">
            <input type="text" value="<?php echo $conntimeout?>" size="2" maxlength="2" id="conntimeout" name="conntimeout" />
        </td>
    </tr><tr>
        <th colspan="1" style="width:25%;text-align:right;">
            Allowed Hosts:<br />
            <font size="2">Comma Separated Values (no spaces)</font>
        </th>
        <td colspan="3" style="text-align:left;">
            <input type="text" value="<?php echo $allowedhosts?>" size="96" maxlength="512" id="allowedhosts" name="allowedhosts" />
        </td>
    </tr><tr>
        <th colspan="1" style="width:25%;text-align:right;">Include Config Directory:</th>
        <td colspan="3" style="text-align:left;">
            <input type="text" value="<?php echo $includedir?>" size="96" maxlength="256" id="includedir" name="includedir" />
        </td>
    </tr><tr>
        <th colspan="1" style="width:25%;text-align:right;">Available Commands:</th>
        <td colspan="3" style="text-align:left;">
            <select id="nrpecmds" name="nrpecmds[]" multiple="multiple">
<?php
foreach ($viewData->nrpecmds as $nrpecmd => $nrpecmdInfo) {
    if (in_array($nrpecmd, $nrpecmds)) {
?>
                <option value="<?php echo $nrpecmd?>" selected><?php echo $nrpecmd?></option>
<?php
    } else {
?>
                <option value="<?php echo $nrpecmd?>"><?php echo $nrpecmd?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
<div id="nrpecmdsout" class="divCacGroup admin_border_black" style="border-width:2px;">
    NRPE Command Configuration Window
    <div id="nrpecmdwindow" class="divCacSubResponse divCacGroup">
    </div>
</div>
</div>
<?php

require HTML_FOOTER;
