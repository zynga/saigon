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

$cgName = isset($viewData->contactInfo['contactgroup_name'])?$viewData->contactInfo['contactgroup_name']:'<i>null or incl from template</i>';
$cgAlias = isset($viewData->contactInfo['alias'])?$viewData->contactInfo['alias']:'<i>null or incl from template</i>';

if ((isset($viewData->contactInfo['members'])) && (!empty($viewData->contactInfo['members']))) {
    $cgMembers = implode(',', $viewData->contactInfo['members']);
}
else {
    $cgMembers = '<i>null or incl from template</i>';
}

if ((isset($viewData->contactInfo['contactgroup_members'])) && (!empty($viewData->contactInfo['contactgroup_members']))) {
    $cgGroupMembers = implode(',', $viewData->contactInfo['contactgroup_members']);
}
else {
    $cgGroupMembers = '<i>null or incl from template</i>';
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="contacts" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Contact Group Information for <?php echo $cgName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">Contact Group Information for <?php echo $cgName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $cgName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:</th>
        <td style="text-align:left;"><?php echo $cgAlias?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Member(s):</th>
        <td style="text-align:left;"><?php echo $cgMembers?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Member Group(s):</th>
        <td style="text-align:left;"><?php echo $cgGroupMembers?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=contactgrp&action=<?php echo $action?>&deployment=<?php echo $deployment?>&cgName=<?php echo $cgName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=contactgrp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=contactgrp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Contact Groups</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
