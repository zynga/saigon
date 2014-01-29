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

$contactName = isset($viewData->contactInfo['name'])?$viewData->contactInfo['name']:'';
$contactAlias = isset($viewData->contactInfo['alias'])?$viewData->contactInfo['alias']:'';
$contactUse = isset($viewData->contactInfo['use'])?$viewData->contactInfo['use']:'';
$contactHostNotifCmd = isset($viewData->contactInfo['host_notification_commands'])?$viewData->contactInfo['host_notification_commands']:'<i>null or incl from template</i>';
$contactHostNotifTime = isset($viewData->contactInfo['host_notification_period'])?$viewData->contactInfo['host_notification_period']:'<i>null or incl from template</i>';
$contactSvcNotifCmd = isset($viewData->contactInfo['service_notification_commands'])?$viewData->contactInfo['service_notification_commands']:'<i>null or incl from template</i>';
$contactSvcNotifTime = isset($viewData->contactInfo['service_notification_period'])?$viewData->contactInfo['service_notification_period']:'<i>null or incl from template</i>';

if (isset($viewData->contactInfo['host_notifications_enabled'])) {
    $contactHostNotifEn = $viewData->contactInfo['host_notifications_enabled'];
    if ($contactHostNotifEn == 0) $contactHostNotifEn = "Off";
    else if ($contactHostNotifEn == 1) $contactHostNotifEn = "On";
}
else {
    $contactHostNotifEn = '<i>null or incl from template</i>';
}

if (isset($viewData->contactInfo['service_notifications_enabled'])) {
    $contactSvcNotifEn = $viewData->contactInfo['service_notifications_enabled'];
    if ($contactSvcNotifEn == 0) $contactSvcNotifEn = "Off";
    else if ($contactSvcNotifEn == 1) $contactSvcNotifEn = "On";
}
else {
    $contactSvcNotifEn = '<i>null or incl from template</i>';
}

if ((isset($viewData->contactInfo['host_notification_options'])) && (!empty($viewData->contactInfo['host_notification_options']))) {
    $contactHostNotifOpts = implode(',', $viewData->contactInfo['host_notification_options']);
}
else {
    $contactHostNotifOpts = '<i>null or incl from template</i>';
}

if ((isset($viewData->contactInfo['service_notification_options'])) && (!empty($viewData->contactInfo['service_notification_options']))) {
    $contactSvcNotifOpts = implode(',', $viewData->contactInfo['service_notification_options']);
}
else {
    $contactSvcNotifOpts = '<i>null or incl from template</i>';
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="contact-template" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Contact Template Information for <?php echo $contactName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">Contact Template Information for <?php echo $contactName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $contactName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:</th>
        <td style="text-align:left;"><?php echo $contactAlias?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Template:</th>
        <td style="text-align:left;"><?php echo $contactUse?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Notifications Enabled:</th>
        <td style="text-align:left;"><?php echo $contactHostNotifEn?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Notification Period:</th>
        <td style="text-align:left;"><?php echo $contactHostNotifTime?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Notification Options:</th>
        <td style="text-align:left;"><?php echo $contactHostNotifOpts?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Notification Command:</th>
        <td style="text-align:left;"><?php echo $contactHostNotifCmd?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Notifications Enabled:</th>
        <td style="text-align:left;"><?php echo $contactSvcNotifEn?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Notification Period:</th>
        <td style="text-align:left;"><?php echo $contactSvcNotifTime?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Notification Options:</th>
        <td style="text-align:left;"><?php echo $contactSvcNotifOpts?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Notification Command:</th>
        <td style="text-align:left;"><?php echo $contactSvcNotifCmd?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=contacttemp&action=<?php echo $action?>&deployment=<?php echo $deployment?>&contacttemp=<?php echo $contactName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=contacttemp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=contacttemp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Contact Templates</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
