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

$contactName = isset($viewData->contactInfo['name'])?$viewData->contactInfo['name']:'';
$contactAlias = isset($viewData->contactInfo['alias'])?$viewData->contactInfo['alias']:'';
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
<div id="contact-template" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="contact-template" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=contacttemp" name="contact_template_submit_form">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Contact Template to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Contact Template Information for <?php echo $contactName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Contact Template Information for <?php echo $contactName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: generic-contact</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $contactName?>" size="64" maxlength="128" id="contactName" name="contactName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:<br /><font size="2">Ex: default contact alert setup</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $contactAlias?>" size="64" maxlength="128" id="contactAlias" name="contactAlias" /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Use Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="usetemplate" name="usetemplate" multiple="multiple">
                <option value=""> - Null or incl from Template - </option>
<?php
foreach ($viewData->contacttemplates as $ctemplate => $ctArray) {
    if (!empty($contactName)) {
        /* Prevent Self-Inclusion as Template */
        if ($ctemplate == $contactName) continue;
        if ((isset($ctArray['use'])) && ($ctArray['use'] == $contactName)) continue;
    }
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
                            <select id="hostnotifenabled" name="hostnotifenabled" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if ($contactHostNotifEn == 0) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
} else if ($contactHostNotifEn == 1) {
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
                            <select id="hostnotifperiod" name="hostnotifperiod" multiple="multiple">
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
                            <select id="hostnotifopts" name="hostnotifopts[]" multiple="multiple">
                                <option value="d" <?php echo in_array('d', $contactHostNotifOpts)?'selected':''?>>Down</option>
                                <option value="u" <?php echo in_array('u', $contactHostNotifOpts)?'selected':''?>>Unknown</option>
                                <option value="r" <?php echo in_array('r', $contactHostNotifOpts)?'selected':''?>>Recovery</option>
                                <option value="s" <?php echo in_array('s', $contactHostNotifOpts)?'selected':''?>>Schedule</option>
                                <option value="n" <?php echo in_array('n', $contactHostNotifOpts)?'selected':''?>>None</option>
                            </select>
                        </td>
                        <th style="width:30%;text-align:right;">Host Notification Command:</th>
                        <td style="text-align:left;">
                            <select id="hostnotifcmd" name="hostnotifcmd" multiple="multiple">
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
                            <select id="svcnotifenabled" name="svcnotifenabled" multiple="multiple">
                                <option value=""> - Null or incl from Template - </option>
<?php
if ($contactSvcNotifEn == 0) {
?>
                                <option value="on">On</option>
                                <option value="off" selected>Off</option>
<?php
} else if ($contactSvcNotifEn == 1) {
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
                            <select id="svcnotifperiod" name="svcnotifperiod" multiple="multiple">
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
                            <select id="svcnotifopts" name="svcnotifopts[]" multiple="multiple">
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
                            <select id="svcnotifcmd" name="svcnotifcmd" multiple="multiple">
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
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>
<?php

require HTML_FOOTER;
