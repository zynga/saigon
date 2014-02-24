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

$hostName = isset($viewData->hostInfo['name'])?$viewData->hostInfo['name']:'';
$hostAlias = isset($viewData->hostInfo['alias'])?$viewData->hostInfo['alias']:'';
$hostUse = isset($viewData->hostInfo['use'])?$viewData->hostInfo['use']:'';

$hostChkCommand = isset($viewData->hostInfo['check_command'])?$viewData->hostInfo['check_command']:'';
$hostInitState = isset($viewData->hostInfo['initial_state'])?$viewData->hostInfo['initial_state']:'';
$hostMaxChkAtts = isset($viewData->hostInfo['max_check_attempts'])?$viewData->hostInfo['max_check_attempts']:'';
$hostCheckInt = isset($viewData->hostInfo['check_interval'])?$viewData->hostInfo['check_interval']:'';
$hostRetryInt = isset($viewData->hostInfo['retry_interval'])?$viewData->hostInfo['retry_interval']:'';
$hostActChks = isset($viewData->hostInfo['active_checks_enabled'])?$viewData->hostInfo['active_checks_enabled']:-1;
$hostPsvChks = isset($viewData->hostInfo['passive_checks_enabled'])?$viewData->hostInfo['passive_checks_enabled']:-1;
$hostChkPeriod = isset($viewData->hostInfo['check_period'])?$viewData->hostInfo['check_period']:'';
$hostPPData = isset($viewData->hostInfo['process_perf_data'])?$viewData->hostInfo['process_perf_data']:-1;
$hostRetStatusInfo = isset($viewData->hostInfo['retain_status_information'])?$viewData->hostInfo['retain_status_information']:-1;
$hostRetNStatusInfo = isset($viewData->hostInfo['retain_nonstatus_information'])?$viewData->hostInfo['retain_nonstatus_information']:-1;
$hostContacts = isset($viewData->hostInfo['contacts'])?$viewData->hostInfo['contacts']:array();
$hostContactGrps = isset($viewData->hostInfo['contact_groups'])?$viewData->hostInfo['contact_groups']:array();
$hostNotifEn = isset($viewData->hostInfo['notifications_enabled'])?$viewData->hostInfo['notifications_enabled']:-1;
$hostNotifInt = isset($viewData->hostInfo['notification_interval'])?$viewData->hostInfo['notification_interval']:'';
$hostNotifPeriod = isset($viewData->hostInfo['notification_period'])?$viewData->hostInfo['notification_period']:'';
$hostNotifOpts = isset($viewData->hostInfo['notification_options'])?$viewData->hostInfo['notification_options']:array();
$hostNotesUrl = isset($viewData->hostInfo['notes_url'])?$viewData->hostInfo['notes_url']:'';


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
    $("#checkcmd")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Check Command",
            multiple: false,
        }).multiselectfilter(),
    $("#initstate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Initial State",
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
    $("#contacts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Contacts",
        }).multiselectfilter(),
    $("#contactgrps")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Contact Groups",
        }).multiselectfilter();
});
</script>
<script type="text/javascript">
function getCmdDefinition() {
    var cmd = $('#checkcmd').val();
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=command&action=getCmdline&deployment=<?php echo $deployment?>&cmdName=' + encodeURIComponent(cmd),
        dataType: 'html',
        success: function( data ) {
            $('#cmdresults').html( data );
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
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=command&action=getCmdline&deployment=<?php echo $deployment?>&cmdName=<?php echo $hostChkCommand?>',
        dataType: 'html',
        success: function( data ) {
            $('#cmdresults').html( data );
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
<div id="host-template" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="host-template" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=hosttemp" name="host_template_submit_form">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="4">Add Host Template to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="4">Modify Host Template Information for <?php echo $hostName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="4">Copy Host Template Information for <?php echo $hostName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: generic-host</font></th>
        <td style="text-align:left;" colspan="3"><input type="text" value="<?php echo $hostName?>" size="64" maxlength="128" id="hostName" name="hostName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:<br /><font size="2">Ex: default host alert setup</font></th>
        <td style="text-align:left;" colspan="3"><input type="text" value="<?php echo $hostAlias?>" size="64" maxlength="128" id="hostAlias" name="hostAlias" /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Use Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;" colspan="3">
            <select id="usetemplate" name="usetemplate" multiple="multiple">
                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->hosttemplates as $ctemplate => $ctArray) {
    if (!empty($hostName)) {
        /* Prevent Self-Inclusion as Template */
        if ($ctemplate == $hostName) continue;
        if ((isset($ctArray['use'])) && ($ctArray['use'] == $hostName)) continue;
    }
    if ($ctemplate == $hostUse) {
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
        <th style="width:30%;text-align:right;" rowspan="2">Check Command:</th>
        <td style="text-align:left;" colspan="3">
            <select id="checkcmd" name="checkcmd" multiple="multiple" onChange="getCmdDefinition()">
                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->hostchkcmds as $cmd) {
    if ($cmd == $hostChkCommand) {
?>
                <option value="<?php echo $cmd?>" selected><?php echo $cmd?></option>
<?php
    } else {
?>
                <option value="<?php echo $cmd?>"><?php echo $cmd?></option>
<?php
    }
}
?>
            </select>
        </td>
        </tr><tr>
            <td colspan="3">
                <div class="divCacGroup" style="width:98%;height:20px;text-align:left;">
                    <div id="cmdresults"></div>
                </div>
            </td>
        </tr>
    </tr><tr>
        <td colspan="3">
            <div class="parentClass divCacGroup" id="hostinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                State Related Information:
            </div>
            <div class="divHide parent-desc-hostinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Initial State:</th>
                        <td style="text-align:left;">
                            <select id="initstate" name="initstate" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$states = array('o' => 'up', 'd' => 'down', 'u' => 'unknown');
foreach ($states as $stateKey => $state) {
    if ($stateKey == $hostInitState) {
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
if (preg_match("/^0$/", $hostPPData)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $hostPPData)) {
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
if (preg_match("/^0$/", $hostRetStatusInfo)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $hostRetStatusInfo)) {
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
if (preg_match("/^0$/", $hostRetNStatusInfo)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $hostRetNStatusInfo)) {
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
        <td colspan="3">
            <div class="parentClass divCacGroup" id="hostchk" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Check Related Information:
            </div>
            <div class="divHide parent-desc-hostchk">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Max Check Attempts:<br /><font size="2">(# of check attempts before sending an alert)</font></th>
                        <td style="text-align:left;">
                            <select id="maxchkatts" name="maxchkatts" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$maxchks = array('1','2','3','4','5','10');
foreach ($maxchks as $chkTimes) {
    if ($chkTimes == $hostMaxChkAtts) {
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
                        <th style="width:30%;text-align:right;">Check Period:</th>
                        <td style="text-align:left;">
                            <select id="chkperiod" name="chkperiod" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->timeperiods as $timePeriod => $tpArray) {
    if ($timePeriod == $hostChkPeriod) {
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
                    </tr><tr>
                        <th style="width:30%;text-align:right;">Check Interval:<br /><font size="2">(amount of time between normal check scheduling)</font></th>
                        <td style="text-align:left;">
                            <select id="chkinterval" name="chkinterval" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
$chkInts = array('1' => '1 Min', '5' => '5 Mins', '15' => '15 Mins', '30' => '30 Mins', '60' => '1 Hour', '120' => '2 Hours');
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $hostCheckInt) {
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
$chkInts = array('1' => '1 Min', '2' => '2 Mins', '5' => '5 Mins', '15' => '15 Mins', '30' => '30 Mins', '60' => '1 Hour');
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $hostRetryInt) {
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
if (preg_match("/^0$/", $hostActChks)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $hostActChks)) {
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
if (preg_match("/^0$/", $hostPsvChks)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $hostPsvChks)) {
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
        <td colspan="3">
            <div class="parentClass divCacGroup" id="hostcontact" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Contact Related Information:
            </div>
            <div class="divHide parent-desc-hostcontact">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Contacts:<br /><font size="2">(Uncheck all to nullify / include from template)</font></th>
                        <td style="text-align:left;">
                            <select id="contacts" name="contacts[]" multiple="multiple">
<?php
foreach ($viewData->contacts as $contact => $cArray) {
    if (in_array($contact,$hostContacts)) {
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
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->contactgroups as $contactGroup => $cgArray) {
    if (in_array($contactGroup, $hostContactGrps)) {
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
        <td colspan="3">
            <div class="parentClass divCacGroup" id="hostnotif" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Notification Related Information:
            </div>
            <div class="divHide parent-desc-hostnotif">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Notifications Enabled:</th>
                        <td style="text-align:left;">
                            <select id="notifenabled" name="notifenabled" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $hostNotifEn)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $hostNotifEn)) {
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
$chkInts = array('15' => '15 Mins', '30' => '30 Mins', '60' => '1 Hour', '120' => '2 Hours', '180' => '3 Hours');
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $hostNotifInt) {
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
    if ($timePeriod == $hostNotifPeriod) {
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
                                <option value="d" <?php echo in_array('d', $hostNotifOpts)?'selected':''?>>Down</option>
                                <option value="u" <?php echo in_array('u', $hostNotifOpts)?'selected':''?>>Unknown</option>
                                <option value="r" <?php echo in_array('r', $hostNotifOpts)?'selected':''?>>Recovery</option>
                                <option value="s" <?php echo in_array('s', $hostNotifOpts)?'selected':''?>>Schedule</option>
                                <option value="n" <?php echo in_array('n', $hostNotifOpts)?'selected':''?>>None</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;" id="urls">
                <img src="static/imgs/plusSign.gif">
                Extended Host Related Information:
            </div>
            <div class="divHide parent-desc-urls" style="padding-left:100px;">
                <table>
                    <tr>
                        <th>Notes URL:</th><td><input type="text" value="<?php echo $hostNotesUrl?>" id="notesurl" name="notesurl" size="128" maxlength="512" /></td>
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
