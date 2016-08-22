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

$svcDepName = isset($viewData->svcDepInfo['name'])?$viewData->svcDepInfo['name']:'';
$svcDepParent = isset($viewData->svcDepInfo['service_description'])?$viewData->svcDepInfo['service_description']:'';
$svcDepChild = isset($viewData->svcDepInfo['dependent_service_description'])?$viewData->svcDepInfo['dependent_service_description']:'';
$svcCheckOpts = isset($viewData->svcDepInfo['execution_failure_criteria'])?$viewData->svcDepInfo['execution_failure_criteria']:array();
$svcNotifOpts = isset($viewData->svcDepInfo['notification_failure_criteria'])?$viewData->svcDepInfo['notification_failure_criteria']:array();
$svcDepInherit = isset($viewData->svcDepInfo['inherits_parent'])?$viewData->svcDepInfo['inherits_parent']:false;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#parentsvc")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Parent Service",
            multiple: false,
        }).multiselectfilter(),
    $("#dependentsvc")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select Dependent Service",
            multiple: false,
        }).multiselectfilter(),
    $("#checkcriteria")
        .multiselect({
            noneSelectedText: "Select Check Criteria",
        }).multiselectfilter(),
    $("#notifcriteria")
        .multiselect({
            noneSelectedText: "Select Notification Criteria",
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
<div id="action-svc-dep" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-svc-dep" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="svc_dep_write">
<input type="hidden" value="svcdep" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Service Depedency to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Service Depedency <?php echo $svcDepName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Service Depedency <?php echo $svcDepName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:35%;text-align:right;">Name:<br /><font size="2">Ex: check-disk-nrpe-dependency</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $svcDepName?>" size="64" maxlength="128" id="svcDepName" name="svcDepName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Parent Service:</th>
        <td style="text-align:left;">
            <select id="parentsvc" name="parentsvc" multiple="multiple">
<?php
foreach ($viewData->svcs as $service => $svcArray) {
    if ($service == $svcDepParent) {
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
        <th style="width:35%;text-align:right;">Dependent Service:</th>
        <td style="text-align:left;">
            <select id="dependentsvc" name="dependentsvc" multiple="multiple">
<?php
foreach ($viewData->svcs as $service => $svcArray) {
    if ($service == $svcDepChild) {
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
        <th style="width:35%;text-align:right;">
            Inherit Parent Dependencies:
        </th>
        <td style="text-align:left;">
            <input type="checkbox" name="inheritparent" value="on" <?php echo ($svcDepInherit !== false)?'checked':''?> />
        </td>
    </tr><tr>
        <th style="width:35%;text-align:right;">
            Execution Criteria:
        </th>
        <td style="text-align:left;">
            <select id="checkcriteria" name="checkcriteria[]" multiple="multiple">
                <option value="o" <?php echo in_array('o', $svcCheckOpts)?'selected':''?>>OK</option>
                <option value="w" <?php echo in_array('w', $svcCheckOpts)?'selected':''?>>Warning</option>
                <option value="u" <?php echo in_array('u', $svcCheckOpts)?'selected':''?>>Unknown</option>
                <option value="c" <?php echo in_array('c', $svcCheckOpts)?'selected':''?>>Critical</option>
                <option value="p" <?php echo in_array('p', $svcCheckOpts)?'selected':''?>>Pending</option>
                <option value="n" <?php echo in_array('n', $svcCheckOpts)?'selected':''?>>None / Always</option>
            </select>
        </td>
    </tr><tr>
        <td colspan="2">
            <div class="parentClass" id="exec-notes" style="text-align:left;">
                <img src="static/imgs/plusSign.gif">
                Execution Criteria Notes
            </div>
            <div class="divHide parent-desc-exec-notes divCacSubResponse" style="text-align:left;">
                This directive is used to specify the criteria that determines when the dependent service should not be checked.
                <ul>
                <li>(o) OK - don't check if parent service is in OK state</li>
                <li>(w) Warn - don't check if parent service is in WARN state</li>
                <li>(u) Unknown - don't check if parent service is in UNKNOWN state</li>
                <li>(c) Critical - don't check if parent service is in CRITICAL state</li>
                <li>(p) Pending - don't check if parent service is in PENDING state</li>
                <li>(n) None - Always check regardless of parent service state</li>
                </ul>
                Example: Don't check dependent service if parent service state is unknown or crit = u,c
            </div>
        </td>
    </tr><tr>
        <th style="width:35%;text-align:right;">
            Notification Criteria:
        </th>
        <td style="text-align:left;">
            <select id="notifcriteria" name="notifcriteria[]" multiple="multiple">
                <option value="o" <?php echo in_array('o', $svcNotifOpts)?'selected':''?>>OK</option>
                <option value="w" <?php echo in_array('w', $svcNotifOpts)?'selected':''?>>Warning</option>
                <option value="u" <?php echo in_array('u', $svcNotifOpts)?'selected':''?>>Unknown</option>
                <option value="c" <?php echo in_array('c', $svcNotifOpts)?'selected':''?>>Critical</option>
                <option value="p" <?php echo in_array('p', $svcNotifOpts)?'selected':''?>>Pending</option>
                <option value="n" <?php echo in_array('n', $svcNotifOpts)?'selected':''?>>None / Always</option>
            </select>
        </td>
    </tr><tr>
        <td colspan="2">
            <div class="parentClass" id="notif-notes" style="text-align:left;">
                <img src="static/imgs/plusSign.gif">
                Notification Criteria Notes
            </div>
            <div class="divHide parent-desc-notif-notes divCacSubResponse" style="text-align:left;">
                This directive is used to specify the criteria that determines when the dependent service should not alert.
                <ul>
                <li>(o) OK - don't alert if parent service is in OK state</li>
                <li>(w) Warn - don't alert if parent service is in WARN state</li>
                <li>(u) Unknown - don't alert if parent service is in UNKNOWN state</li>
                <li>(c) Critical - don't alert if parent service is in CRITICAL state</li>
                <li>(p) Pending - don't alert if parent service is in PENDING state</li>
                <li>(n) None - Always alert regardless of parent service state</li>
                </ul>
                Example: Don't alert on dependent service if parent service state is warn, unknown or crit = w,u,c
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
