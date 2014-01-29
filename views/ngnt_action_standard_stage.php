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
$nodeHostTemplate = isset($viewData->nodeInfo['hosttemplate'])?$viewData->nodeInfo['hosttemplate']:'';
if (empty($viewData->services)) {
    $viewData->error = 'Unable to detect available services to apply to nodes';
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
$("#hosttemplate")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Host Template",
        multiple: false,
    }).multiselectfilter(),
$("#services")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Services",
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
            Host Template or Service Checks
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
        <th colspan="2">Create Standard Node Template for <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Standard Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Standard Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: redis-host-template</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $nodeTemp?>" size="64" maxlength="128" id="nodeTemp" name="nodeTemp" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hosttemplate" name="hosttemplate" multiple="multiple">
                <option value="">Null / No HostTemplate</option>
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
