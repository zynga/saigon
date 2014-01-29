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

$hostName = isdefined($viewData->hostInfo, 'name');
$hostAlias = isdefined($viewData->hostInfo, 'alias');
$hostUse = isdefined($viewData->hostInfo, 'use');
$hostChkCommand = isdefined($viewData->hostInfo, 'check_command');
$hostInitState = isdefined($viewData->hostInfo, 'initial_state');
$hostMaxChkAtts = isdefined($viewData->hostInfo, 'max_check_attempts');
$hostCheckInt = isdefined($viewData->hostInfo, 'check_interval');
$hostRetryInt = isdefined($viewData->hostInfo, 'retry_interval');
$hostActChks = onoffdefined($viewData->hostInfo, 'active_checks_enabled');
$hostPsvChks = onoffdefined($viewData->hostInfo, 'passive_checks_enabled');
$hostChkPeriod = isdefined($viewData->hostInfo, 'check_period');
$hostPPData = onoffdefined($viewData->hostInfo, 'process_perf_data');
$hostRetStatusInfo = onoffdefined($viewData->hostInfo, 'retain_status_information');
$hostRetNStatusInfo = onoffdefined($viewData->hostInfo, 'retain_nonstatus_information');
$hostContacts = isdefined($viewData->hostInfo, 'contacts', true);
$hostContactGrps = isdefined($viewData->hostInfo, 'contact_groups', true);
$hostNotifEn = onoffdefined($viewData->hostInfo, 'notifications_enabled');
$hostNotifInt = isdefined($viewData->hostInfo, 'notification_interval');
$hostNotifPeriod = isdefined($viewData->hostInfo, 'notification_period');
$hostNotifOpts = isdefined($viewData->hostInfo, 'notification_options', true);
$hostNotesUrl = isdefined($viewData->hostInfo, 'notes_url');

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="host-template" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Host Template Information for <?php echo $hostName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">View Host Template Information for <?php echo $hostName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:35%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $hostName?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Alias:</th>
        <td style="text-align:left;"><?php echo $hostAlias?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Template:</th>
        <td style="text-align:left;"><?php echo $hostUse?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Command:</th>
        <td style="text-align:left;"><?php echo $hostChkCommand?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Initial State:</th>
        <td style="text-align:left;"><?php echo $hostInitState?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Max Check Attempts:<br /><font size="2">(# of check attempts before sending an alert)</font></th>
        <td style="text-align:left;"><?php echo $hostMaxChkAtts?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Interval:<br /><font size="2">(amount of minutes between normal check scheduling)</font></th>
        <td style="text-align:left;"><?php echo $hostCheckInt?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Retry Interval:<br /><font size="2">(amount of minutes between error check scheduling)</font></th>
        <td style="text-align:left;"><?php echo $hostRetryInt?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Active Checks Enabled:</th>
        <td style="text-align:left;"><?php echo $hostActChks?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Passive Checks Enabled:</th>
        <td style="text-align:left;"><?php echo $hostPsvChks?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Check Period:</th>
        <td style="text-align:left;"><?php echo $hostChkPeriod?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Process Performance Data:</th>
        <td style="text-align:left;"><?php echo $hostPPData?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Retain Status Information:</th>
        <td style="text-align:left;"><?php echo $hostRetStatusInfo?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Retain Non-Status Information:</th>
        <td style="text-align:left;"><?php echo $hostRetNStatusInfo?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Contacts:</th>
        <td style="text-align:left;"><?php echo $hostContacts?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Contact Groups:</th>
        <td style="text-align:left;"><?php echo $hostContactGrps?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notifications Enabled:</th>
        <td style="text-align:left;"><?php echo $hostNotifEn?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notification Interval:<br /><font size="2">(amount of minutes between sending alerts)</font></th>
        <td style="text-align:left;"><?php echo $hostNotifInt?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notification Period:</th>
        <td style="text-align:left;"><?php echo $hostNotifPeriod?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notification Options:</th>
        <td style="text-align:left;"><?php echo $hostNotifOpts?></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Notes Url:</th>
        <td style="text-align:left;"><?php echo $hostNotesUrl?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=hosttemp&action=<?php echo $action?>&deployment=<?php echo $deployment?>&hosttemp=<?php echo $hostName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=hosttemp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=hosttemp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Host Templates</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
