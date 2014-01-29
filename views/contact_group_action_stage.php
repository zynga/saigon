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

$cgName = isset($viewData->contactInfo['contactgroup_name'])?$viewData->contactInfo['contactgroup_name']:'';
$cgAlias = isset($viewData->contactInfo['alias'])?$viewData->contactInfo['alias']:'';
$cgMembers = isset($viewData->contactInfo['members'])?$viewData->contactInfo['members']:array();
$cgGroupMembers = isset($viewData->contactInfo['contactgroup_members'])?$viewData->contactInfo['contactgroup_members']:array();

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#cgMembers")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Member(s)",
        }).multiselectfilter(),
    $("#cgGroupMembers")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Group(s)",
        }).multiselectfilter();
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
<div id="action-contact-group" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-contact-group" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=contactgrp" name="contact_group_write">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Contact Group to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Contact Group <?php echo $cgName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Contact Group <?php echo $cgName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: sre-alerts</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $cgName?>" size="64" maxlength="128" id="cgName" name="cgName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:<br /><font size="2">Ex: SRE Nagios alert group</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $cgAlias?>" size="64" maxlength="512" id="cgAlias" name="cgAlias" /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Members:</th>
        <td style="text-align:left;">
            <select id="cgMembers" name="cgMembers[]" multiple="multiple">
<?php
foreach ($viewData->contacts as $contact => $contactArray) {
    if (in_array($contact, $cgMembers)) {
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
        <th style="width:30%;text-align:right;">Contact Groups:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="cgGroupMembers" name="cgGroupMembers[]" multiple="multiple">
<?php
foreach ($viewData->contactgroups as $contactGroup => $cgArray) {
    /* Prevent Self-Inclusion as Template */
    if (isset($modifyFlag)) {
        if ($contactGroup == $cgName) continue;
    }
    if (in_array($contactGroup, $cgGroupMembers)) {
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
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>
<?php

require HTML_FOOTER;
