<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$action = $viewData->action;
if ($action == 'modify_write') {
    $modifyFlag = true;
}

$svcName = isset($viewData->svcInfo['name'])?$viewData->svcInfo['name']:'';
$svcDesc = isset($viewData->svcInfo['service_description'])?$viewData->svcInfo['service_description']:'';
$svcUse = isset($viewData->svcInfo['use'])?$viewData->svcInfo['use']:'';

$svcChkCommand = isset($viewData->svcInfo['check_command'])?$viewData->svcInfo['check_command']:'';
$svcServiceGroup = isset($viewData->svcInfo['servicegroups'])?$viewData->svcInfo['servicegroups']:array();
$svcInitState = isset($viewData->svcInfo['initial_state'])?$viewData->svcInfo['initial_state']:'';
$svcMaxChkAtts = isset($viewData->svcInfo['max_check_attempts'])?$viewData->svcInfo['max_check_attempts']:'';
$svcCheckInt = isset($viewData->svcInfo['check_interval'])?$viewData->svcInfo['check_interval']:'';
$svcRetryInt = isset($viewData->svcInfo['retry_interval'])?$viewData->svcInfo['retry_interval']:'';
$svcActChks = isset($viewData->svcInfo['active_checks_enabled'])?$viewData->svcInfo['active_checks_enabled']:-1;
$svcPsvChks = isset($viewData->svcInfo['passive_checks_enabled'])?$viewData->svcInfo['passive_checks_enabled']:-1;
$svcChkPeriod = isset($viewData->svcInfo['check_period'])?$viewData->svcInfo['check_period']:'';
$svcPPData = isset($viewData->svcInfo['process_perf_data'])?$viewData->svcInfo['process_perf_data']:-1;
$svcRetStatusInfo = isset($viewData->svcInfo['retain_status_information'])?$viewData->svcInfo['retain_status_information']:-1;
$svcRetNStatusInfo = isset($viewData->svcInfo['retain_nonstatus_information'])?$viewData->svcInfo['retain_nonstatus_information']:-1;
$svcContacts = isset($viewData->svcInfo['contacts'])?$viewData->svcInfo['contacts']:array();
$svcContactGrps = isset($viewData->svcInfo['contact_groups'])?$viewData->svcInfo['contact_groups']:array();
$svcNotifEn = isset($viewData->svcInfo['notifications_enabled'])?$viewData->svcInfo['notifications_enabled']:-1;
$svcNotifInt = isset($viewData->svcInfo['notification_interval'])?$viewData->svcInfo['notification_interval']:'';
$svcNotifPeriod = isset($viewData->svcInfo['notification_period'])?$viewData->svcInfo['notification_period']:'';
$svcNotifOpts = isset($viewData->svcInfo['notification_options'])?$viewData->svcInfo['notification_options']:array();
$svcChkFreshness = isset($viewData->svcInfo['check_freshness'])?$viewData->svcInfo['check_freshness']:-1;
$svcChkFreshInt = isset($viewData->svcInfo['freshness_threshold'])?$viewData->svcInfo['freshness_threshold']:'';
$svcNotesUrl = isset($viewData->svcInfo['notes_url'])?$viewData->svcInfo['notes_url']:'';
$svcActionUrl = isset($viewData->svcInfo['action_url'])?$viewData->svcInfo['action_url']:'';
$svcEvtHandEn = isset($viewData->svcInfo['event_handler_enabled'])?$viewData->svcInfo['event_handler_enabled']:'-1';
$svcEHCommand = isset($viewData->svcInfo['event_handler'])?$viewData->svcInfo['event_handler']:'';

$svcCArg1 = isset($viewData->svcInfo['carg1'])?$viewData->svcInfo['carg1']:'';
$svcCArg2 = isset($viewData->svcInfo['carg2'])?$viewData->svcInfo['carg2']:'';
$svcCArg3 = isset($viewData->svcInfo['carg3'])?$viewData->svcInfo['carg3']:'';
$svcCArg4 = isset($viewData->svcInfo['carg4'])?$viewData->svcInfo['carg4']:'';
$svcCArg5 = isset($viewData->svcInfo['carg5'])?$viewData->svcInfo['carg5']:'';
$svcCArg6 = isset($viewData->svcInfo['carg6'])?$viewData->svcInfo['carg6']:'';
$svcCArg7 = isset($viewData->svcInfo['carg7'])?$viewData->svcInfo['carg7']:'';
$svcCArg8 = isset($viewData->svcInfo['carg8'])?$viewData->svcInfo['carg8']:'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#activechk")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#passivechk")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#ppdata")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#retstatusinfo")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#retnstatusinfo")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#notifinterval")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Interval",
            multiple: false,
        }).multiselectfilter(),
    $("#chkperiod")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Timeperiod",
            multiple: false,
        }).multiselectfilter(),
    $("#usetemplate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Template",
            multiple: false,
        }).multiselectfilter(),
    $("#initstate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Initial State",
            multiple: false,
        }).multiselectfilter(),
    $("#checkcmd")
            .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Check Command",
            multiple: false,
        }).multiselectfilter(),
    $("#chkretryinterval")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Check Retry Interval",
            multiple: false,
        }).multiselectfilter(),
    $("#chkinterval")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Check Interval",
            multiple: false,
        }).multiselectfilter(),
    $("#maxchkatts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Max Attempt Count",
            multiple: false,
        }).multiselectfilter(),
    $("#notifenabled")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#notifperiod")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Timeperiod",
            multiple: false,
        }).multiselectfilter(),
    $("#notifopts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Options",
        }).multiselectfilter(),
    $("#notifcmd")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Command",
            multiple: false,
        }).multiselectfilter(),
    $("#svcgrp")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Service Group",
        }).multiselectfilter(),
    $("#contacts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Contacts",
        }).multiselectfilter(),
    $("#contactgrps")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Contact Groups",
        }).multiselectfilter(),
    $("#checkfreshness")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#chkfreshinterval")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Interval",
            multiple: false,
        }).multiselectfilter(),
    $("#ehcmd")
            .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Eventhandler Command",
            multiple: false,
        }).multiselectfilter(),
    $("#ehenabled")
            .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter();
});
</script>
<script type="text/javascript">
function getCmdDefinition(mode) {
    if (mode == 'eh') {
        var cmd = $('#ehcmd').val();
    }
    else {
        var cmd = $('#checkcmd').val();
    }
    if (cmd == null) return;
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=command&action=getCmdline&deployment=<?php echo $deployment?>&cmdName=' + encodeURIComponent(cmd),
        dataType: 'html',
        success: function( data ) {
            if (mode == 'eh') {
                $('#ehresults').html( data );
            }
            else {
                $('#cmdresults').html( data );
            }
        }
    });
}
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
$(document).ready(function() {
    getCmdDefinition();
    getCmdDefinition('eh');
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
<div id="svcs" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="svcs" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=svc" name="svc_submit_form">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="4">Add Service to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="4">Modify Service Information for <?php echo $svcName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="4">Copy Service Information for <?php echo $svcName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="text-align:right;width:30%;">Name:<br /><font size="2">Ex: check-disk-root</font></th>
        <td style="text-align:left;" colspan="3"><input type="text" value="<?php echo $svcName?>" size="64" maxlength="128" id="svcName" name="svcName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="text-align:right;width:30%;">Service Description:<br /><font size="2">Ex: Check Free Space on /</font></th>
        <td style="text-align:left;" colspan="3"><input type="text" value="<?php echo $svcDesc?>" size="64" maxlength="128" id="svcDesc" name="svcDesc" /></td>
    </tr><tr>
        <th style="text-align:right;width:30%;">Use Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;" colspan="3">
            <select id="usetemplate" name="usetemplate" multiple="multiple">
                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->svctemplates as $ctemplate => $ctArray) {
    /* Prevent Self-Inclusion as Template */
    if (isset($modifyFlag)) {
        if ($ctemplate == $svcName) continue;
    }
    if ($ctemplate == $svcUse) {
?>
                <option value="<?php echo $ctemplate?>" selected><?php echo $ctemplate?></option>
<?php
    } else {
?>
                <option value="<?php echo $ctemplate?>"><?php echo $ctemplate?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;width:30%;" rowspan="2">Check Command:</th>
        <td style="text-align:left;" colspan="3">
            <select id="checkcmd" name="checkcmd" multiple="multiple" onChange="getCmdDefinition()">
                <option value=""> - Null or incl from Template - </option>
<?php
asort($viewData->svcchkcmds);
foreach ($viewData->svcchkcmds as $svcCmd => $svcArray) {
    if ($svcCmd == $svcChkCommand) {
?>
                <option value="<?php echo $svcCmd?>" selected><?php echo $svcCmd?></option>
<?php
    } else {
?>
                <option value="<?php echo $svcCmd?>"><?php echo $svcCmd?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <td colspan="3">
            <div class="divCacGroup" style="width:98%;min-height:20px;text-align:left;">
                <div id="cmdresults"></div>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;" id="cmdArgs">
                <img src="static/imgs/plusSign.gif">
                Add Possible Command Arguments
            </div>
            <div class="divHide parent-desc-cmdArgs" style="padding-left:100px;">
                <table>
                    <tr>
                        <th>$ARG1$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg1)?>" id="carg1" name="carg1" size="32" maxlength="128" /></td>
                        <th>$ARG2$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg2)?>" id="carg2" name="carg2" size="32" maxlength="128" /></td>
                    </tr><tr>
                        <th>$ARG3$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg3)?>" id="carg3" name="carg3" size="32" maxlength="128" /></td>
                        <th>$ARG4$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg4)?>" id="carg4" name="carg4" size="32" maxlength="128" /></td>
                    </tr><tr>
                        <th>$ARG5$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg5)?>" id="carg5" name="carg5" size="32" maxlength="128" /></td>
                        <th>$ARG6$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg6)?>" id="carg6" name="carg6" size="32" maxlength="128" /></td>
                    </tr><tr>
                        <th>$ARG7$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg7)?>" id="carg7" name="carg7" size="32" maxlength="128" /></td>
                        <th>$ARG8$</th><td><input type="text" value="<?php echo htmlspecialchars($svcCArg8)?>" id="carg8" name="carg8" size="32" maxlength="128" /></td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svcstateinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                State Related Information:
            </div>
            <div class="divHide parent-desc-svcstateinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Initial State:</th>
                        <td style="text-align:left;">
                            <select id="initstate" name="initstate" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$states = array('o' => 'ok', 'w' => 'warning', 'c' => 'critical', 'u' => 'unknown');
foreach ($states as $stateKey => $state) {
    if ($stateKey == $svcInitState) {
?>
                                <option value="<?php echo $stateKey?>" selected><?php echo $state?></option>
<?php
    } else {
?>
                                <option value="<?php echo $stateKey?>"><?php echo $state?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Process Performance Data:</th>
                        <td style="text-align:left;">
                            <select id="ppdata" name="ppdata" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcPPData)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcPPData)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="width:30%;text-align:right;">Retain Status Information:</th>
                        <td style="text-align:left;">
                            <select id="retstatusinfo" name="retstatusinfo" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcRetStatusInfo)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcRetStatusInfo)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Retain Non-Status Information:</th>
                        <td style="text-align:left;">
                            <select id="retnstatusinfo" name="retnstatusinfo" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcRetNStatusInfo)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
} 
else if (preg_match("/^1$/", $svcRetNStatusInfo)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svcchkinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Check Related Information:
            </div>
            <div class="divHide parent-desc-svcchkinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Check Period:</th>
                        <td style="text-align:left;">
                            <select id="chkperiod" name="chkperiod" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->timeperiods as $timePeriod => $tpArray) {
    if ($timePeriod == $svcChkPeriod) {
?>
                                <option value="<?php echo $timePeriod?>" selected><?php echo $timePeriod?></option>
<?php
    } else {
?>
                                <option value="<?php echo $timePeriod?>"><?php echo $timePeriod?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Max Check Attempts:<br /><font size="2">(# of check attempts before sending an alert)</font></th>
                        <td style="text-align:left;">
                            <select id="maxchkatts" name="maxchkatts" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$maxchks = array('1','2','3','4','5','10');
foreach ($maxchks as $chkTimes) {
    if ($chkTimes == $svcMaxChkAtts) {
?>
                                <option value="<?php echo $chkTimes?>" selected><?php echo $chkTimes?></option>
<?php
    } else {
?>
                                <option value="<?php echo $chkTimes?>"><?php echo $chkTimes?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="width:30%;text-align:right;">Check Interval:<br /><font size="2">(amount of time between normal check scheduling)</font></th>
                        <td style="text-align:left;">
                            <select id="chkinterval" name="chkinterval" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$chkInts = array();
for ($i=1;$i<=5;$i++) {
    $chkInts[$i] = $i . " Min(s)";
}
for ($i=10;$i<55;) {
    $chkInts[$i] = $i . " Min(s)";
    $i = $i+5;
}
for ($i=1;$i<=48;$i++) {
    $chkInts[60*$i] = $i . " Hour(s)";
}
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $svcCheckInt) {
?>
                                <option value="<?php echo $chkTime?>" selected><?php echo $chkVal?></option>
<?php
    } else {
?>
                                <option value="<?php echo $chkTime?>"><?php echo $chkVal?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Check Retry Interval:<br /><font size="2">(amount of time between error check scheduling)</font></th>
                        <td style="text-align:left;">
                            <select id="chkretryinterval" name="chkretryinterval" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
// defined at line 528
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $svcRetryInt) {
?>
                                <option value="<?php echo $chkTime?>" selected><?php echo $chkVal?></option>
<?php
    } else {
?>
                                <option value="<?php echo $chkTime?>"><?php echo $chkVal?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="width:30%;text-align:right;">Active Checks Enabled:</th>
                        <td style="text-align:left;">
                            <select id="activechk" name="activechk" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcActChks)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcActChks)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>

<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Passive Checks Enabled:</th>
                        <td style="text-align:left;">
                            <select id="passivechk" name="passivechk" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcPsvChks)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcPsvChks)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svccontactinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Contact Related Information:
            </div>
            <div class="divHide parent-desc-svccontactinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Contacts:<br /><font size="2">(Uncheck all to nullify / include from template)</font></th>
                        <td style="text-align:left;">
                            <select id="contacts" name="contacts[]" multiple="multiple">
<?php
foreach ($viewData->contacts as $contact => $cArray) {
    if (in_array($contact,$svcContacts)) {
?>
                                <option value="<?php echo $contact?>" selected><?php echo $contact?></option>
<?php
    } else {
?>
                                <option value="<?php echo $contact?>"><?php echo $contact?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Contact Groups:<br /><font size="2">(Uncheck all to nullify / include from template)</font></th>
                        <td style="text-align:left;">
                            <select id="contactgrps" name="contactgrps[]" multiple="multiple">
<?php
foreach ($viewData->contactgroups as $contactGroup => $cgArray) {
    if (in_array($contactGroup, $svcContactGrps)) {
?>
                                <option value="<?php echo $contactGroup?>" selected><?php echo $contactGroup?></option>
<?php
    } else {
?>
                                <option value="<?php echo $contactGroup?>"><?php echo $contactGroup?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svcnotifinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Notification Related Information:
            </div>
            <div class="divHide parent-desc-svcnotifinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Notifications Enabled:</th>
                        <td style="text-align:left;">
                            <select id="notifenabled" name="notifenabled" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcNotifEn)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcNotifEn)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Notification Interval:<br /><font size="2">(amount of time between sending alerts)</font></th>
                        <td style="text-align:left;">
                            <select id="notifinterval" name="notifinterval" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
// defined at line 528
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $svcNotifInt) {
?>
                                <option value="<?php echo $chkTime?>" selected><?php echo $chkVal?></option>
<?php
    } else {
?>
                                <option value="<?php echo $chkTime?>"><?php echo $chkVal?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr><tr>
                        <th style="width:30%;text-align:right;">Notification Period:</th>
                        <td style="text-align:left;">
                            <select id="notifperiod" name="notifperiod" multiple="multiple">
                               <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->timeperiods as $timePeriod => $tpArray) {
    if ($timePeriod == $svcNotifPeriod) {
?>
                                <option value="<?php echo $timePeriod?>" selected><?php echo $timePeriod?></option>
<?php
    } else {
?>
                                <option value="<?php echo $timePeriod?>"><?php echo $timePeriod?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Notification Options:</th>
                        <td style="text-align:left;">
                            <select id="notifopts" name="notifopts[]" multiple="multiple">
                                <option value="w" <?php echo in_array('w', $svcNotifOpts)?'selected':''?>>Warning</option>
                                <option value="c" <?php echo in_array('c', $svcNotifOpts)?'selected':''?>>Critical</option>
                                <option value="r" <?php echo in_array('r', $svcNotifOpts)?'selected':''?>>Recovery</option>
                                <option value="u" <?php echo in_array('u', $svcNotifOpts)?'selected':''?>>Unknown</option>
                                <option value="s" <?php echo in_array('s', $svcNotifOpts)?'selected':''?>>Schedule</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svcfreshinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Freshness Related Information:
            </div>
            <div class="divHide parent-desc-svcfreshinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Check Freshness:</th>
                        <td style="text-align:left;">
                            <select id="checkfreshness" name="checkfreshness" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcChkFreshness)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcChkFreshness)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Freshness Check Interval:<br /><font size="2">(amount of time between checking results freshness)</font></th>
                        <td style="text-align:left;">
                            <select id="chkfreshinterval" name="chkfreshinterval" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$chkInts = array(
    '60' => '1 Min(s)', '120' => '2 Min(s)', '180' => '3 Min(s)', '240' => '4 Min(s)',
);
for ($i=5;$i<=55;) {
    $chkInts[60*$i] = $i . " Min(s)";
    $i = $i+5;
}
for ($i=1;$i<=48;$i++) {
    $chkInts[3600*$i] = $i . " Hour(s)";
}
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $svcChkFreshInt) {
?>
                                <option value="<?php echo $chkTime?>" selected><?php echo $chkVal?></option>
<?php
    } else {
?>
                                <option value="<?php echo $chkTime?>"><?php echo $chkVal?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svcgrpsinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                ServiceGroups Related Information:
            </div>
            <div class="divHide parent-desc-svcgrpsinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Service Groups:</th>
                        <td style="text-align:left;">
                            <select id="svcgrp" name="svcgrp[]" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->svcgroups as $svcGroup => $sgArray) {
    if (in_array($svcGroup,$svcServiceGroup)) {
?>
                                <option value="<?php echo $svcGroup?>" selected><?php echo $svcGroup?></option>
<?php
    } else {
?>
                                <option value="<?php echo $svcGroup?>"><?php echo $svcGroup?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;" id="eventhandler">
                <img src="static/imgs/plusSign.gif">
                EventHandler Related Information:
            </div>
            <div class="divHide parent-desc-eventhandler" style="padding-left:100px;">
                <table>
                    <tr>
                        <th style="width:30%;text-align:right;">Eventhandler Enabled:</th>
                        <td style="text-align:left;">
                            <select id="ehenabled" name="ehenabled" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $svcEvtHandEn)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $svcEvtHandEn)) {
?>
                                <option value="on" selected>On</option>
                                <option value="off">Off</option>
<?php
} else {
?>
                                <option value="on">On</option>
                                <option value="off">Off</option>
<?php
}
?>
                            </select>
                        </td>
                        <th style="text-align:right;width:30%;">Eventhandler Command:</th>
                        <td style="text-align:left;">
                            <select id="ehcmd" name="ehcmd" multiple="multiple" onChange="getCmdDefinition('eh')">
                                <option value=""> - Null or incl from Template - </option>
<?php
asort($viewData->svcchkcmds);
foreach ($viewData->svcchkcmds as $svcCmd => $svcArray) {
    if ($svcCmd == $svcEHCommand) {
?>
                                <option value="<?php echo $svcCmd?>" selected><?php echo $svcCmd?></option>
<?php
    } else {
?>
                                <option value="<?php echo $svcCmd?>"><?php echo $svcCmd?></option>
<?php
    }
}
?>
                            </select>
                        </td>
                    </tr><tr>
                        <td colspan="4">
                            <div class="divCacGroup" style="width:98%;min-height:20px;text-align:center;">
                                <div id="ehresults"></div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;" id="urls">
                <img src="static/imgs/plusSign.gif">
                Extended Service Related Information:
            </div>
            <div class="divHide parent-desc-urls" style="padding-left:100px;">
                <table>
                    <tr>
                        <th>Notes URL:</th><td><input type="text" value="<?php echo $svcNotesUrl?>" id="notesurl" name="notesurl" size="128" maxlength="512" /></td>
                    </tr><tr>
                        <th>Action URL:</th><td><input type="text" value="<?php echo $svcActionUrl?>" id="actionurl" name="actionurl" size="128" maxlength="512" /></td>
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

require HTML_FOOTER;
