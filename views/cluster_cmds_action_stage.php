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
$ccType = $viewData->cctype;
if ($action == 'modify_write') {
    $modifyFlag = true;
}

$cmdName = isset($viewData->clustercmdsInfo['name'])?$viewData->clustercmdsInfo['name']:'';
$cmdServer = isset($viewData->clustercmdsInfo['server'])?$viewData->clustercmdsInfo['server']:'';
$cmdType = isset($viewData->clustercmdsInfo['type'])?$viewData->clustercmdsInfo['type']:'service';
$cmdWarnMode = isset($viewData->clustercmdsInfo['warnmode'])?$viewData->clustercmdsInfo['warnmode']:'integer';
$cmdWarnMin = isset($viewData->clustercmdsInfo['warnmin'])?$viewData->clustercmdsInfo['warnmin']:'';
$cmdWarnMax = isset($viewData->clustercmdsInfo['warnmax'])?$viewData->clustercmdsInfo['warnmax']:'';
$cmdCritMode = isset($viewData->clustercmdsInfo['critmode'])?$viewData->clustercmdsInfo['critmode']:'integer';
$cmdCrit = isset($viewData->clustercmdsInfo['crit'])?$viewData->clustercmdsInfo['crit']:'';
$cmdQuery = isset($viewData->clustercmdsInfo['query'])?htmlspecialchars(base64_decode($viewData->clustercmdsInfo['query'])):'';
$cmdDesc = isset($viewData->clustercmdsInfo['service_description'])?$viewData->clustercmdsInfo['service_description']:'';

$svcUse = isset($viewData->clustercmdsInfo['use'])?$viewData->clustercmdsInfo['use']:'';
$svcServiceGroup = isset($viewData->clustercmdsInfo['servicegroups'])?$viewData->clustercmdsInfo['servicegroups']:array();
$svcInitState = isset($viewData->clustercmdsInfo['initial_state'])?$viewData->clustercmdsInfo['initial_state']:'';
$svcMaxChkAtts = isset($viewData->clustercmdsInfo['max_check_attempts'])?$viewData->clustercmdsInfo['max_check_attempts']:'';
$svcCheckInt = isset($viewData->clustercmdsInfo['check_interval'])?$viewData->clustercmdsInfo['check_interval']:'';
$svcRetryInt = isset($viewData->clustercmdsInfo['retry_interval'])?$viewData->clustercmdsInfo['retry_interval']:'';
$svcActChks = isset($viewData->clustercmdsInfo['active_checks_enabled'])?$viewData->clustercmdsInfo['active_checks_enabled']:-1;
$svcChkPeriod = isset($viewData->clustercmdsInfo['check_period'])?$viewData->clustercmdsInfo['check_period']:'';
$svcRetStatusInfo = isset($viewData->clustercmdsInfo['retain_status_information'])?$viewData->clustercmdsInfo['retain_status_information']:-1;
$svcRetNStatusInfo = isset($viewData->clustercmdsInfo['retain_nonstatus_information'])?$viewData->clustercmdsInfo['retain_nonstatus_information']:-1;
$svcContacts = isset($viewData->clustercmdsInfo['contacts'])?$viewData->clustercmdsInfo['contacts']:array();
$svcContactGrps = isset($viewData->clustercmdsInfo['contact_groups'])?$viewData->clustercmdsInfo['contact_groups']:array();
$svcNotifEn = isset($viewData->clustercmdsInfo['notifications_enabled'])?$viewData->clustercmdsInfo['notifications_enabled']:-1;
$svcNotifInt = isset($viewData->clustercmdsInfo['notification_interval'])?$viewData->clustercmdsInfo['notification_interval']:'';
$svcNotifPeriod = isset($viewData->clustercmdsInfo['notification_period'])?$viewData->clustercmdsInfo['notification_period']:'';
$svcNotifOpts = isset($viewData->clustercmdsInfo['notification_options'])?$viewData->clustercmdsInfo['notification_options']:array();
$svcChkFreshness = isset($viewData->clustercmdsInfo['check_freshness'])?$viewData->clustercmdsInfo['check_freshness']:-1;
$svcChkFreshInt = isset($viewData->clustercmdsInfo['freshness_threshold'])?$viewData->clustercmdsInfo['freshness_threshold']:'';
$svcNotesUrl = isset($viewData->clustercmdsInfo['notes_url'])?$viewData->clustercmdsInfo['notes_url']:'';
$svcActionUrl = isset($viewData->clustercmdsInfo['action_url'])?$viewData->clustercmdsInfo['action_url']:'';

?>
<script type="text/javascript">
$(function() {
    $("#cmdtype")
        .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Command Type",
        multiple: false,
    }).multiselectfilter(),
    $("#activechk")
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
    $("#warnmode")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Warning Mode",
            multiple: false,
        }).multiselectfilter(),
    $("#critmode")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Critical Mode",
            multiple: false,
        }).multiselectfilter();
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
function checklapiquery() {
    $('#lapibutton').attr('disabled','disabled');
    $('#lapibutton').addClass('grey');
    $('#results').empty();
    var sData = {};
    sData['controller'] = "clustercmds";
    sData['action'] = "view_matches";
    sData['query'] = $('#cmdquery').val();
    sData['type'] = $('#cmdtype').val();
    sData['server'] = $('#cmdserver').val();
    $.ajax({
        url: 'action.php',
        data: sData,
        type: 'POST',
        dataType: 'html',
        success: function( data ) {
            $('#results').html( data );
            $('#lapibutton').removeClass('grey');
            $('#lapibutton').removeAttr('disabled');
        }
    });
}
</script>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
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
<div id="clustercmds" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="clustercmds" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
    <form method="post" action="action.php" name="cluster_commands_action_write">
    <input type="hidden" value="clustercmds" id="controller" name="controller" />
    <input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
    <input type="hidden" value="<?php echo $action?>" id="action" name="action" />
    <input type="hidden" value="<?php echo $ccType?>" id="cctype" name="cctype" />
    <table class="noderesults">
        <thead>
<?php
if ($action == 'add_write') {
?>
            <th colspan="4">Add Cluster Command to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
            <th colspan="4">Modify Cluster Command <?php echo $cmdName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
            <th colspan="4">Copy Cluster Command <?php echo $cmdName?> to <?php echo $deployment?></th>
<?php
}
?>
        </thead>
        <tr>
            <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: check_mysql_cluster_conn</font></th>
            <td colspan="3" style="text-align:left;">
                <input type="text" value="<?php echo $cmdName?>" size="64" maxlength="128" id="cmdname" name="cmdname" <?php echo (isset($modifyFlag))?'readonly':''?> />
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Nagios Server:<br /><font size="2">Ex: cloudeng-infra-nagios-ch-001:6557</font></th>
            <td colspan="3" style="text-align:left;">
                <input type="text" value="<?php echo $cmdServer?>" size="64" maxlength="128" id="cmdserver" name="cmdserver" />
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Type:</th>
            <td colspan="3" style="text-align:left;">
                <select id="cmdtype" name="cmdtype" multiple="multiple">
<?php
$types = array('service', 'host');
foreach ($types as $type) {
    if ($type == $cmdType) {
?>
                    <option value="<?php echo $type?>" selected><?php echo $type?></option>
<?php
    } else {
?>
                    <option value="<?php echo $type?>"><?php echo $type?></option>
<?php
    }
}
?>
                </select>
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Description:<br /><font size="2">Ex: Check MySQL Cluster Connections</font></th>
            <td colspan="3" style="text-align:left;">
                <input type="text" value="<?php echo $cmdDesc?>" size="64" maxlength="512" id="cmddesc" name="cmddesc" />
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Warning Mode:<br/><font size="2">How the script should treat the input value</font></th>
            <td colspan="3" style="text-align:left;">
                <select id="warnmode" name="warnmode" multiple="multiple">
<?php
$types = array('integer', 'percentage');
foreach ($types as $type) {
    if ($type == $cmdWarnMode) {
?>
                    <option value="<?php echo $type?>" selected><?php echo $type?></option>
<?php
    } else {
?>
                    <option value="<?php echo $type?>"><?php echo $type?></option>
<?php
    }
}
?>
                </select>
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Warning Minimum Value:<br /><font size="2">Minimum # of Warning Values to trip Warning alert</font></th>
            <td style="text-align:left;">
                <input type="text" value="<?php echo $cmdWarnMin?>" size="7" maxlength="6" id="cmdwarnmin" name="cmdwarnmin" />
            </td>
            <th style="width:30%;text-align:right;">Warning Maximum Value:<br /><font size="2">Maximum # of Warning Values to trip Critical alert</font></th>
            <td style="text-align:left;">
                <input type="text" value="<?php echo $cmdWarnMax?>" size="7" maxlength="6" id="cmdwarnmax" name="cmdwarnmax" />
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Critical Mode:<br /><font size="2">How the script should treat the input value</font></th>
            <td colspan="3" style="text-align:left;">
                <select id="critmode" name="critmode" multiple="multiple">
<?php
$types = array('integer', 'percentage');
foreach ($types as $type) {
    if ($type == $cmdCritMode) {
?>
                    <option value="<?php echo $type?>" selected><?php echo $type?></option>
<?php
    } else {
?>
                    <option value="<?php echo $type?>"><?php echo $type?></option>
<?php
    }
}
?>
                </select>
            </td>
        </tr><tr>
            <th style="width:30%;text-align:right;">Critical Value:<br /><font size="2"># of Critical Values to trip Critical alert</font></th>
            <td colspan="3" style="text-align:left;">
                <input type="text" value="<?php echo $cmdCrit?>" size="7" maxlength="6" id="cmdcrit" name="cmdcrit" />
            </td>
        </tr>
        <th style="text-align:right;width:30%;">Use Service Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;" colspan="3">
            <select id="usetemplate" name="usetemplate" multiple="multiple">
                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->svctemplates as $ctemplate => $ctArray) {
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
        <td colspan="4">
            <div class="parentClass divCacGroup" id="svcoverride" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Service Settings:<font size="2"> (Optional if service template is used)</font>
            </div>
            <div class="divHide parent-desc-svcoverride divCacGroup">

            <div class="divCacGroup"></div>
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
            <div class="divCacGroup"></div>
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
                    </tr>
                </table>
            </div>
            <div class="divCacGroup"></div>
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
            <div class="divCacGroup"></div>
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
            <div class="divCacGroup"></div>
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
            <div class="divCacGroup"></div>
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
    if (in_array($svcGroup, $svcServiceGroup)) {
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
            <div class="divCacGroup"></div>
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


            </div>
        </td>
    </tr>
    <tr>
        <th style="width:30%;text-align:right;">
            Command:<br />
            <input type="button" id="lapibutton" value="View Results" onClick="checklapiquery()" />
        </th>
        <td colspan="3" style="text-align:left;">
            <textarea rows="7" cols="75" id="cmdquery" name="cmdquery" /><?php echo $cmdQuery?></textarea>
        </td>
    </tr>
    </table>
    <div class="divCacGroup"></div>
    <div class="divCacGroup admin_box_blue" style="width:6%;">
        <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
    </div>
    </form>
    <div class="divCacGroup"></div>
    <div id="results"></div>
    <div class="divCacGroup"></div>
</div>
<?php

require HTML_FOOTER;
