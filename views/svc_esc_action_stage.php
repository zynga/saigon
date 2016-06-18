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

$svcEscName = isset($viewData->svcescinfo['name'])?$viewData->svcescinfo['name']:'';
$svcEscDesc = isset($viewData->svcescinfo['service_description'])?$viewData->svcescinfo['service_description']:'';
$svcEscContacts = isset($viewData->svcescinfo['contacts'])?$viewData->svcescinfo['contacts']:array();
$svcEscCGrps = isset($viewData->svcescinfo['contact_groups'])?$viewData->svcescinfo['contact_groups']:array();
$svcEscFirstNotif = isset($viewData->svcescinfo['first_notification'])?$viewData->svcescinfo['first_notification']:'';
$svcEscLastNotif = isset($viewData->svcescinfo['last_notification'])?$viewData->svcescinfo['last_notification']:'';
$svcEscNotif = isset($viewData->svcescinfo['notification_interval'])?$viewData->svcescinfo['notification_interval']:'';
$svcEscTimeperiod = isset($viewData->svcescinfo['escalation_period'])?$viewData->svcescinfo['escalation_period']:'';
$svcEscOpts = isset($viewData->svcescinfo['escalation_options'])?$viewData->svcescinfo['escalation_options']:array();

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#service")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Service",
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
        }).multiselectfilter(),
    $("#firstnotif")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "First Notification Alert",
            multiple: false,
        }).multiselectfilter(),
    $("#lastnotif")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Last Notification Alert",
            multiple: false,
        }).multiselectfilter(),
    $("#notifinterval")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Notification Interval",
            multiple: false,
        }).multiselectfilter(),
    $("#timeperiod")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Escalation TimePeriod",
            multiple: false,
        }).multiselectfilter(),
    $("#escopts")
        .multiselect({
            noneSelectedText: "Select Escalation Options",
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
<div id="action-svc-esc" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-svc-esc" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="svc_esc_write">
<input type="hidden" value="svcesc" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Service Escalation to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Service Escalation <?php echo $svcEscName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Service Escalation <?php echo $svcEscName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:35%;text-align:right;">Name:<br /><font size="2">Ex: check-disk-nrpe-escalation</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $svcEscName?>" size="64" maxlength="128" id="svcEscName" name="svcEscName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Service:</th>
        <td style="text-align:left;">
            <select id="service" name="service" multiple="multiple">
<?php
foreach ($viewData->svcs as $service => $svcArray) {
    if ($service == $svcEscDesc) {
?>
                <option value="<?php echo $service?>" selected><?php echo $service?></option>
<?php
    } else {
?>
                <option value="<?php echo $service?>"><?php echo $service?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Contacts:</th>
        <td style="text-align:left;">
            <select id="contacts" name="contacts[]" multiple="multiple">
<?php
foreach ($viewData->contacts as $contact => $contactArray) {
    if (in_array($contact, $svcEscContacts)) {
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
    </tr><tr>
        <th style="width:35%;text-align:right;">Contacts Groups:</th>
        <td style="text-align:left;">
            <select id="contactgrps" name="contactgrps[]" multiple="multiple">
<?php
foreach ($viewData->contactgrps as $cGroup => $cGroupArray) {
    if (in_array($cGroup, $svcEscCGrps)) {
?>
                <option value="<?php echo $cGroup?>" selected><?php echo $cGroup?></option>
<?php
    } else {
?>
                <option value="<?php echo $cGroup?>"><?php echo $cGroup?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">First Notification:<br /><font size="2">(# of sent notifications before initiating escalation)</font></th>
        <td style="text-align:left;">
            <select id="firstnotif" name="firstnotif" multiple="multiple">
<?php
$notifcount = array('1','2','3','4','5','10');
foreach ($notifcount as $nCount) {
    if ($nCount == $svcEscFirstNotif) {
?>  
                <option value="<?php echo $nCount?>" selected><?php echo $nCount?></option>
<?php   
    } else {
?>
                <option value="<?php echo $nCount?>"><?php echo $nCount?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">Last Notification:<br /><font size="2">(# of sent notifications before stopping escalation)</font></th>
        <td style="text-align:left;">
            <select id="lastnotif" name="lastnotif" multiple="multiple">
<?php
$notifcount = array('all','1','2','3','4','5','10');
foreach ($notifcount as $nCount) {
    if ($nCount == $svcEscLastNotif) {
?>  
                <option value="<?php echo $nCount?>" selected><?php echo $nCount?></option>
<?php   
    } else {
?>
                <option value="<?php echo $nCount?>"><?php echo $nCount?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">Notification Interval:<br /><font size="2">(amount of time between sending alerts)</font></th>
        <td style="text-align:left;">
            <select id="notifinterval" name="notifinterval" multiple="multiple">
<?php
$chkInts = array('15' => '15 Mins', '30' => '30 Mins', '60' => '1 Hour', '120' => '2 Hours', '180' => '3 Hours');
foreach ($chkInts as $chkTime => $chkVal) {
    if ($chkTime == $svcEscNotif) {
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
        <th style="width:35%;text-align:right;">Timeperiod:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="timeperiod" name="timeperiod" multiple="multiple">
                <option value=""> - Use Service's Timeperiod - </option>
<?php
foreach ($viewData->timeperiods as $timeperiod => $tpArray) {
    if ($timeperiod == $svcEscTimeperiod) {
?>
                <option value="<?php echo $timeperiod?>" selected><?php echo $timeperiod?></option>
<?php
    } else {
?>
                <option value="<?php echo $timeperiod?>"><?php echo $timeperiod?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:35%;text-align:right;">
            Escalation Options:<br /><font size="2">(Optional)</font>
        </th>
        <td style="text-align:left;">
            <select id="escopts" name="escopts[]" multiple="multiple">
                <option value="w" <?php echo in_array('w', $svcEscOpts)?'selected':''?>>Warning</option>
                <option value="u" <?php echo in_array('u', $svcEscOpts)?'selected':''?>>Unknown</option>
                <option value="c" <?php echo in_array('c', $svcEscOpts)?'selected':''?>>Critical</option>
                <option value="n" <?php echo in_array('r', $svcEscOpts)?'selected':''?>>Recovery</option>
            </select>
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
