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

$svcEscName = $viewData->svcescinfo['name'];
$svcEscDesc = $viewData->svcescinfo['service_description'];
$svcFirstNotif = $viewData->svcescinfo['first_notification'];
$svcLastNotif = $viewData->svcescinfo['last_notification'];
$svcEscNotif = $viewData->svcescinfo['notification_interval'];
$svcEscTimeperiod = isset($viewData->svcescinfo['timeperiod_name'])?$viewData->svcescinfo['timeperiod_name']:'<i>null</i>';

if ((isset($viewData->svcescinfo['contacts'])) && (!empty($viewData->svcescinfo['contacts']))) {
    $svcEscContacts = implode(',', $viewData->svcescinfo['contacts']);
}
else {
    $svcEscContacts = '<i>null</i>';
}

if ((isset($viewData->svcescinfo['contact_groups'])) && (!empty($viewData->svcescinfo['contact_groups']))) {
    $svcEscCGrps = implode(',', $viewData->svcescinfo['contact_groups']);
}
else {
    $svcEscCGrps = '<i>null</i>';
}

if ((isset($viewData->svcescinfo['escalation_options'])) && (!empty($viewData->svcescinfo['escalation_options']))) {
    $svcEscOpts = implode(',', $viewData->svcescinfo['escalation_options']);
}
else {
    $svcEscOpts = '<i>null</i>';
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="svcs" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Service Escalation Information for <?php echo $svcEscName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">Service Escalation Information for <?php echo $svcEscName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $svcEscName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service:</th>
        <td style="text-align:left;"><?php echo $svcEscDesc?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Escalation Contacts:</th>
        <td style="text-align:left;"><?php echo $svcEscContacts?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Escalation Contact Groups:</th>
        <td style="text-align:left;"><?php echo $svcEscCGrps?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">First Notification:</th>
        <td style="text-align:left;"><?php echo $svcFirstNotif?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Last Notification:</th>
        <td style="text-align:left;"><?php echo $svcLastNotif?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Notification Interval:</th>
        <td style="text-align:left;"><?php echo $svcEscNotif?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Notification Timeperiod:</th>
        <td style="text-align:left;"><?php echo $svcEscTimeperiod?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Escalation Options:</th>
        <td style="text-align:left;"><?php echo $svcEscOpts?></td>
    </tr><tr>

</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=svcesc&action=<?php echo $action?>&deployment=<?php echo $deployment?>&svcEsc=<?php echo $svcEscName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=svcesc&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=svcesc&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Service Escalations</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
