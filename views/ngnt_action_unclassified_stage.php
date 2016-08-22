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
$type = $viewData->ngnttype;

if ($action == 'modify_write') {
    $modifyFlag = true;
}

$nodeTemp = isset($viewData->nodeInfo['name'])?$viewData->nodeInfo['name']:'';
$nodeServices = isset($viewData->nodeInfo['services'])?$viewData->nodeInfo['services']:array();
$nodeNServices = isset($viewData->nodeInfo['nservices'])?$viewData->nodeInfo['nservices']:array();
$nodeHostGroup = isset($viewData->nodeInfo['hostgroup'])?$viewData->nodeInfo['hostgroup']:'';
$nodeHostTemplate = isset($viewData->nodeInfo['hosttemplate'])?$viewData->nodeInfo['hosttemplate']:'';
$nodeStdHostTemplate = isset($viewData->nodeInfo['stdtemplate'])?$viewData->nodeInfo['stdtemplate']:'';
$nodeContacts = isset($viewData->nodeInfo['contacts'])?$viewData->nodeInfo['contacts']:array();
$nodeContactGroups = isset($viewData->nodeInfo['contactgroups'])?$viewData->nodeInfo['contactgroups']:array();
$nodeSvcTemplate = isset($viewData->nodeInfo['svctemplate'])?$viewData->nodeInfo['svctemplate']:'';
$nodeSvcEscalations = isset($viewData->nodeInfo['svcescs'])?$viewData->nodeInfo['svcescs']:array();
if (empty($viewData->services)) {
    $viewData->error = 'Unable to detect available services to apply to nodes';
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#hostgroup")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Hostgroup",
            multiple: false,
        }).multiselectfilter(),
    $("#stdtemplate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Standard Template",
            multiple: false,
        }).multiselectfilter(),
    $("#hosttemplate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Host Template",
            multiple: false,
        }).multiselectfilter(),
    $("#svctemplate")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Service Template",
            multiple: false,
        }).multiselectfilter(),
    $("#services")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Services",
        }).multiselectfilter(),
    $("#nservices")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Negate Services",
        }).multiselectfilter(),
    $("#svcescs")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Service Escalations",
        }).multiselectfilter(),
    $("#contacts")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Contacts",
        }).multiselectfilter(),
    $("#contactgroups")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Contact Groups",
        }).multiselectfilter();
});
</script>
<body>
<?php
if ((isset($viewData->error)) && (!empty($viewData->error))) {
?>
<div class="divCacGroup"></div>
<div id="error" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;background-color:red;border-width:2px">
    <div class="divCacGroup" style="text-align:center;">
        <b>Error Detected: <?php echo $viewData->error?></b>
    </div>
</div>
<div class="divCacGroup"></div>
<?php
} else {
?>
<div class="divCacGroup"></div>
<div id="error" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;background-color:red;border-width:2px">
    <div class="divCacGroup" style="text-align:center;">
        <b>
            Please be sure to apply at least one of the following optional settings, not applying any will generate an error...
            <div class="divCacGroup"></div>
            Saigon Standard Template or Host Template or Host Group or Service Checks or Service Template or Contacts or Contact Groups
        </b>
    </div>
</div>
<div class="divCacGroup"></div>
<?php
}
?>
<div id="container" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;border-width:2px;">
<form method="POST" action="action.php" name="ngnt_form">
<input type="hidden" value="ngnt" id="controller" name="controller" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $type?>" id="ngnttype" name="ngnttype" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Create Node Template for <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: redis-host-template</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $nodeTemp?>" size="64" maxlength="128" id="nodeTemp" name="nodeTemp" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr>
<?php
if (!empty($viewData->stdtemplates)) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Saigon Standard Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="stdtemplate" name="stdtemplate" multiple="multiple">
                <option value="">Null / No Standard Template</option>
<?php
    asort($viewData->stdtemplates);
    foreach ($viewData->stdtemplates as $key => $stdtemplate) {
        if ($stdtemplate == $nodeStdHostTemplate) {
?>
                <option value="<?php echo $stdtemplate?>" selected><?php echo $stdtemplate?></option>
<?php
        } else {
?>
                <option value="<?php echo $stdtemplate?>"><?php echo $stdtemplate?></option>
<?php
        }
    }
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Negate Saigon Standard Template<br />Service Checks:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="nservices" name="nservices[]" multiple="multiple">
<?php
    foreach ($viewData->services as $svcCmd => $svcArray) {
        if ((is_array($nodeNServices)) && (in_array($svcCmd,$nodeNServices))) {
?>
                <option value="<?php echo $svcCmd?>" selected><?php echo $svcCmd?></option>
<?php
        } else {
?>
                <option value="<?php echo $svcCmd?>"><?php echo $svcCmd?></option>
<?php
        }
    }
?>
            </select>
        </td>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Host Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hosttemplate" name="hosttemplate" multiple="multiple">
                <option value="">Null / No Host Template</option>
<?php
asort($viewData->hosttemplates);
foreach ($viewData->hosttemplates as $hosttemplate => $htArray) {
    if ($hosttemplate == $nodeHostTemplate) {
?>
                <option value="<?php echo $hosttemplate?>" selected><?php echo $hosttemplate?></option>
<?php
    } else {
?>
                <option value="<?php echo $hosttemplate?>"><?php echo $hosttemplate?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Group:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hostgroup" name="hostgroup" multiple="multiple">
                <option value="">Null / No Host Group</option>
<?php
asort($viewData->hostgroups);
foreach ($viewData->hostgroups as $hostgroup => $hgArray) {
    if ($hostgroup == $nodeHostGroup) {
?>
                <option value="<?php echo $hostgroup?>" selected><?php echo $hostgroup?></option>
<?php
    } else {
?>
                <option value="<?php echo $hostgroup?>"><?php echo $hostgroup?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Checks:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="services" name="services[]" multiple="multiple">
<?php
foreach ($viewData->services as $svcCmd => $svcArray) {
    if ((is_array($nodeServices)) && (in_array($svcCmd,$nodeServices))) {
?>
                <option value="<?php echo $svcCmd?>" selected><?php echo $svcCmd?></option>
<?php
    } else {
?>
                <option value="<?php echo $svcCmd?>"><?php echo $svcCmd?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="svctemplate" name="svctemplate" multiple="multiple">
                <option value="">Null / No Service Template</option>
<?php
asort($viewData->svctemplates);
foreach ($viewData->svctemplates as $svctemplate => $stArray) {
    if ($svctemplate == $nodeSvcTemplate) {
?>
                <option value="<?php echo $svctemplate?>" selected><?php echo $svctemplate?></option>
<?php
    } else {
?>
                <option value="<?php echo $svctemplate?>"><?php echo $svctemplate?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Escalations:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="svcescs" name="svcescs[]" multiple="multiple">
<?php
foreach ($viewData->svcescs as $svcEsc => $seArray) {
    if ((is_array($nodeSvcEscalations)) && (in_array($svcEsc,$nodeSvcEscalations))) {
?>
                <option value="<?php echo $svcEsc?>" selected><?php echo $svcEsc?></option>
<?php
    } else {
?>
                <option value="<?php echo $svcEsc?>"><?php echo $svcEsc?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Contacts:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="contacts" name="contacts[]" multiple="multiple">
<?php
foreach ($viewData->contacts as $contact => $cArray) {
    if ((is_array($nodeContacts)) && (in_array($contact,$nodeContacts))) {
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
            <select id="contactgroups" name="contactgroups[]" multiple="multiple">
<?php
foreach ($viewData->contactgroups as $contactGroup => $cgArray) {
    if ((is_array($nodeContactGroups)) && (in_array($contactGroup,$nodeContactGroups))) {
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
<div class="divCacGroup"></div>
<div id="results"></div>
</div>

<?php

require HTML_FOOTER;
