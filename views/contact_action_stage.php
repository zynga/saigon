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
} else if ($action == 'copy_to_write') {
    $copyToFlag = true;
}

$contactName = isset($viewData->contactInfo['contact_name'])?$viewData->contactInfo['contact_name']:'';
$contactAlias = isset($viewData->contactInfo['alias'])?$viewData->contactInfo['alias']:'';
$contactEmail = isset($viewData->contactInfo['email'])?$viewData->contactInfo['email']:'';
$contactUse = isset($viewData->contactInfo['use'])?$viewData->contactInfo['use']:'';
$contactHostNotifEn = isset($viewData->contactInfo['host_notifications_enabled'])?$viewData->contactInfo['host_notifications_enabled']:'-1';
$contactHostNotifCmd = isset($viewData->contactInfo['host_notification_commands'])?$viewData->contactInfo['host_notification_commands']:'';
$contactHostNotifTime = isset($viewData->contactInfo['host_notification_period'])?$viewData->contactInfo['host_notification_period']:'';
$contactHostNotifOpts = isset($viewData->contactInfo['host_notification_options'])?$viewData->contactInfo['host_notification_options']:array();
$contactSvcNotifEn = isset($viewData->contactInfo['service_notifications_enabled'])?$viewData->contactInfo['service_notifications_enabled']:'-1';
$contactSvcNotifCmd = isset($viewData->contactInfo['service_notification_commands'])?$viewData->contactInfo['service_notification_commands']:'';
$contactSvcNotifTime = isset($viewData->contactInfo['service_notification_period'])?$viewData->contactInfo['service_notification_period']:'';
$contactSvcNotifOpts = isset($viewData->contactInfo['service_notification_options'])?$viewData->contactInfo['service_notification_options']:array();

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#hostnotifenabled")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#usetemplate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Template",
            multiple: false,
        }).multiselectfilter(),
    $("#hostnotifperiod")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Timeperiod",
            multiple: false,
        }).multiselectfilter(),
    $("#hostnotifopts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Options",
        }).multiselectfilter(),
    $("#hostnotifcmd")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Command",
            multiple: false,
        }).multiselectfilter(),
    $("#svcnotifenabled")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select State",
            multiple: false,
        }).multiselectfilter(),
    $("#svcnotifperiod")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Timeperiod",
            multiple: false,
        }).multiselectfilter(),
    $("#svcnotifopts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Options",
        }).multiselectfilter(),
    $("#svcnotifcmd")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Notification Command",
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
<?php
if ($action == 'copy_to_write') {
?>
<script type="text/javascript">
$(function() {
    $("#todeployment")
        .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Deployment",
        multiple: false,
    }).multiselectfilter();
});
</script>
<?php
}
?>
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
<div id="action-contact" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-contact" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=contact" name="contact_write">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Contact to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Contact <?php echo $contactName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Contact <?php echo $contactName?> to <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_to_write') {
?>
        <th colspan="2">Copy Contact <?php echo $contactName?> from <?php echo $deployment?> to another deployment</th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: username</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $contactName?>" size="64" maxlength="128" id="contactName" name="contactName" <?php echo ((isset($modifyFlag)) || (isset($copyToFlag)))?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:<br /><font size="2">Ex: FirstName LastName</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $contactAlias?>" size="64" maxlength="512" id="contactAlias" name="contactAlias" <?php echo isset($copyToFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Email:<br /><font size="2">Ex: user@host.com</font></th>
        <td style="text-align:left;"><input type="email" value="<?php echo $contactEmail?>" size="64" maxlength="128" id="contactEmail" name="contactEmail" <?php echo isset($copyToFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Use Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="usetemplate" name="usetemplate" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
            <option value=""> - Null - </option>
<?php
foreach ($viewData->contacttemplates as $ctemplate => $ctArray) {
    if ($ctemplate == $contactUse) {
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
        <td colspan="2">
            <div class="parentClass divCacGroup" id="hostnotifinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Host Notification Related Information:
            </div>
            <div class="divHide parent-desc-hostnotifinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Host Notifications Enabled:</th>
                        <td style="text-align:left;">
                            <select id="hostnotifenabled" name="hostnotifenabled" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $contactHostNotifEn)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $contactHostNotifEn)) {
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
                        <th style="width:30%;text-align:right;">Host Notification Period:</th>
                        <td style="text-align:left;">
                            <select id="hostnotifperiod" name="hostnotifperiod" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->timeperiods as $timePeriod => $tpArray) {
    if ($timePeriod == $contactHostNotifTime) {
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
                        <th style="width:30%;text-align:right;">Host Notification Options:<br /><font size="2">(Uncheck all to nullify/incl from template)</th>
                        <td style="text-align:left;">
                            <select id="hostnotifopts" name="hostnotifopts[]" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value="d" <?php echo in_array('d', $contactHostNotifOpts)?'selected':''?>>Down</option>
                                <option value="u" <?php echo in_array('u', $contactHostNotifOpts)?'selected':''?>>Unknown</option>
                                <option value="r" <?php echo in_array('r', $contactHostNotifOpts)?'selected':''?>>Recovery</option>
                                <option value="s" <?php echo in_array('s', $contactHostNotifOpts)?'selected':''?>>Schedule</option>
                                <option value="n" <?php echo in_array('n', $contactHostNotifOpts)?'selected':''?>>None</option>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Host Notification Command:</th>
                        <td style="text-align:left;">
                            <select id="hostnotifcmd" name="hostnotifcmd" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->notifycmds as $cmd) {
    if ($cmd == $contactHostNotifCmd) {
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
                    </tr>
                </table>
            </div>
        </td>
    </tr><tr>
        <td colspan="2">
            <div class="parentClass divCacGroup" id="svcnotifinfo" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Service Notification Related Information:
            </div>
            <div class="divHide parent-desc-svcnotifinfo">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Service Notifications Enabled:</th>
                        <td style="text-align:left;">
                            <select id="svcnotifenabled" name="svcnotifenabled" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value=""> - Null or incl from Template - </option>
<?php
if (preg_match("/^0$/", $contactSvcNotifEn)) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
}
else if (preg_match("/^1$/", $contactSvcNotifEn)) {
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
                        <th style="width:30%;text-align:right;">Service Notification Period:</th>
                        <td style="text-align:left;">
                            <select id="svcnotifperiod" name="svcnotifperiod" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->timeperiods as $timePeriod => $tpArray) {
    if ($timePeriod == $contactSvcNotifTime) {
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
                        <th style="width:30%;text-align:right;">Service Notification Options:<br /><font size="2">(Uncheck all to nullify/incl from template)</th>
                        <td style="text-align:left;">
                            <select id="svcnotifopts" name="svcnotifopts[]" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value="w" <?php echo in_array('w', $contactSvcNotifOpts)?'selected':''?>>Warnings</option>
                                <option value="u" <?php echo in_array('u', $contactSvcNotifOpts)?'selected':''?>>Unknowns</option>
                                <option value="c" <?php echo in_array('c', $contactSvcNotifOpts)?'selected':''?>>Critical</option>
                                <option value="r" <?php echo in_array('r', $contactSvcNotifOpts)?'selected':''?>>Recovery</option>
                                <option value="s" <?php echo in_array('s', $contactSvcNotifOpts)?'selected':''?>>Schedule</option>
                                <option value="n" <?php echo in_array('n', $contactSvcNotifOpts)?'selected':''?>>None</option>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Service Notification Command:</th>
                        <td style="text-align:left;">
                            <select id="svcnotifcmd" name="svcnotifcmd" multiple="multiple" <?php echo isset($copyToFlag)?'disabled':''?>>
                                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->notifycmds as $cmd) {
    if ($cmd == $contactSvcNotifCmd) {
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
                    </tr>
                </table>
            </div>
        </td>
    </tr>
<?php
if ($action == 'copy_to_write') {
?>
    <tr>
        <th style="width:30%;text-align:right;">Deployment to Copy Command to:</th>
        <td style="text-align:left;">
            <select id="todeployment" name="todeployment" multiple="multiple">
<?php
    foreach ($viewData->availdeployments as $deploy) {
        if ($deploy == $viewData->deployment) continue;
?>
                <option value="<?php echo $deploy?>"><?php echo $deploy?></option>
<?php
    }
?>
            </select>
        </td>
    </tr>
<?php
}
?>
</table>
<?php
if ($action == 'copy_to_write') {
?>
<input type="hidden" id="usetemplate" name="usetemplate" value="<?php echo returnData($ctemplate)?>" />
<input type="hidden" id="hostnotifenabled" name="hostnotifenabled" value="<?php echo returnData($contactHostNotifEn)?>" />
<input type="hidden" id="hostnotifperiod" name="hostnotifperiod" value="<?php echo returnData($contactHostNotifTime)?>" />
<input type="hidden" id="hostnotifcmd" name="hostnotifcmd" value="<?php echo returnData($contactHostNotifCmd)?>" />
<input type="hidden" id="svcnotifenabled" name="svcnotifenabled" value="<?php echo returnData($contactSvcNotifEn)?>" />
<input type="hidden" id="svcnotifperiod" name="svcnotifperiod" value="<?php echo returnData($contactSvcNotifTime)?>" />
<input type="hidden" id="svcnotifcmd" name="svcnotifcmd" value="<?php echo returnData($contactSvcNotifCmd)?>" />
<?php
    foreach ($contactHostNotifOpts as $hostNotifOpt) {
?>
<input type="hidden" name="hostnotifopts[]" value="<?php echo $hostNotifOpt?>" />
<?php
    }
    foreach ($contactSvcNotifOpts as $svcNotifOpt) {
?>
<input type="hidden" name="svcnotifopts[]" value="<?php echo $svcNotifOpt?>" />
<?php
    }
}
?>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>
<?php

function returnData($payload) {
    if ((is_numeric($payload)) && ($payload == -1)) {
        return;
    } else if ((is_numeric($payload)) && ($payload == 1)) {
        return 'on';
    } else if ((is_numeric($payload)) && ($payload == 0)) {
        return 'off';
    }
    return $payload;
}

require HTML_FOOTER;
