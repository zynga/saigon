<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;

function isdefined ($hostinfo, $key, $split = false) {
    if (isset($hostinfo[$key]) && (!empty($hostinfo[$key]))) {
        if ($split === false) return $hostinfo[$key];
        elseif (is_array($hostinfo[$key])) return implode(',', $hostinfo[$key]);
        else return preg_replace('/,/', ', ', $hostinfo[$key]);
    }
    return '<i>null or incl from template</i>';
}

function onoffdefined ($hostinfo, $key) {
    if ((isset($hostinfo[$key])) && (!empty($hostinfo[$key]))) {
        if ($hostinfo[$key] == 0) return 'Off';
        else return 'On';
    }
    return '<i>null or incl from template</i>';
}

$deployment = $viewData->deployment;
$action = $viewData->action;

if ($action == 'modify_write') {
    $modifyFlag = true;
}

$svcName = isdefined($viewData->svcInfo, 'name');
$svcDesc = isdefined($viewData->svcInfo, 'service_description');
$svcUse = isdefined($viewData->svcInfo, 'use');
$svcChkCommand = isdefined($viewData->svcInfo, 'check_command');
$svcInitState = isdefined($viewData->svcInfo, 'initial_state');
$svcMaxChkAtts = isdefined($viewData->svcInfo, 'max_check_attempts');
$svcCheckInt = isdefined($viewData->svcInfo, 'check_interval');
$svcRetryInt = isdefined($viewData->svcInfo, 'retry_interval');
$svcActChks = onoffdefined($viewData->svcInfo, 'active_checks_enabled');
$svcPsvChks = onoffdefined($viewData->svcInfo, 'passive_checks_enabled');
$svcChkPeriod = isdefined($viewData->svcInfo, 'check_period');
$svcPPData = onoffdefined($viewData->svcInfo, 'process_perf_data');
$svcRetStatusInfo = onoffdefined($viewData->svcInfo, 'retain_status_information');
$svcRetNStatusInfo = onoffdefined($viewData->svcInfo, 'retain_nonstatus_information');
$svcContacts = isdefined($viewData->svcInfo, 'contacts', true);
$svcContactGrps = isdefined($viewData->svcInfo, 'contact_groups', true);
$svcNotifEn = onoffdefined($viewData->svcInfo, 'notifications_enabled');
$svcNotifInt = isdefined($viewData->svcInfo, 'notification_interval');
$svcNotifPeriod = isdefined($viewData->svcInfo, 'notification_period');
$svcNotifOpts = isdefined($viewData->svcInfo, 'notification_options', true);
$svcNotesUrl = isdefined($viewData->svcInfo, 'notes_url');
$svcActionUrl = isdefined($viewData->svcInfo, 'action_url');

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="svcs" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Service Information for <?php echo $svcName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">View Service Information for <?php echo $svcName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:35%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $svcName?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Description:</th>
        <td style="text-align:left;"><?php echo $svcDesc?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Template:</th>
        <td style="text-align:left;"><?php echo $svcUse?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Command:</th>
        <td style="text-align:left;"><?php echo $svcChkCommand?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Initial State:</th>
        <td style="text-align:left;"><?php echo $svcInitState?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Max Check Attempts:<br /><font size="2">(# of check attempts before sending an alert)</font></th>
        <td style="text-align:left;"><?php echo $svcMaxChkAtts?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Interval:<br /><font size="2">(amount of minutes between normal check scheduling)</font></th>
        <td style="text-align:left;"><?php echo $svcCheckInt?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Retry Interval:<br /><font size="2">(amount of minutes between error check scheduling)</font></th>
        <td style="text-align:left;"><?php echo $svcRetryInt?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Active Checks Enabled:</th>
        <td style="text-align:left;"><?php echo $svcActChks?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Passive Checks Enabled:</th>
        <td style="text-align:left;"><?php echo $svcPsvChks?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Period:</th>
        <td style="text-align:left;"><?php echo $svcChkPeriod?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Process Performance Data:</th>
        <td style="text-align:left;"><?php echo $svcPPData?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Retain Status Information:</th>
        <td style="text-align:left;"><?php echo $svcRetStatusInfo?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Retain Non-Status Information:</th>
        <td style="text-align:left;"><?php echo $svcRetNStatusInfo?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Contacts:</th>
        <td style="text-align:left;"><?php echo $svcContacts?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Contact Groups:</th>
        <td style="text-align:left;"><?php echo $svcContactGrps?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notifications Enabled:</th>
        <td style="text-align:left;"><?php echo $svcNotifEn?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notification Interval:<br /><font size="2">(amount of minutes between sending alerts)</font></th>
        <td style="text-align:left;"><?php echo $svcNotifInt?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notification Period:</th>
        <td style="text-align:left;"><?php echo $svcNotifPeriod?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notification Options:</th>
        <td style="text-align:left;"><?php echo $svcNotifOpts?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notes URL:</th>
        <td style="text-align:left;"><?php echo $svcNotesUrl?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Action URL:</th>
        <td style="text-align:left;"><?php echo $svcActionUrl?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=svc&action=<?php echo $action?>&deployment=<?php echo $deployment?>&svc=<?php echo $svcName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=svc&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=svc&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Services</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
