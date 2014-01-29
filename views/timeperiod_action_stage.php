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
    $modifyFlag = 'true';
}

$timeName = isset($viewData->timeInfo['timeperiod_name'])?$viewData->timeInfo['timeperiod_name']:'';
$timeAlias = isset($viewData->timeInfo['alias'])?$viewData->timeInfo['alias']:'';
$useTimeperiod = isset($viewData->timeInfo['use'])?$viewData->timeInfo['use']:'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#tpUse")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Use Timeperiod",
            multiple: false,
        }).multiselectfilter();
});
</script>
<script type="text/javascript">
function insertTimeDefinition() {
    var dir = $('#timeDefine').val();
    var range = $('#timeRange').val();
    var deployment = '<?php echo $deployment?>';
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=timeperiod&action=add_timeperiod&dir=' + encodeURIComponent(dir)
            + '&range=' + encodeURIComponent(range) + '&deployment=' + encodeURIComponent(deployment),
        dataType: 'html',
        success: function( data ) {
            $('#timeDefinitions').attr('src', $('#timeDefinitions').attr('src'));
        }
    });
}
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
<div id="timeperiod" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="timeperiod" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=timeperiod" name="timeperiod_write">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Timeperiod to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Timeperiod <?php echo $timeName?> for <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Timeperiod <?php echo $timeName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: 24x7</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $timeName?>" size="64" maxlength="128" id="tpName" name="tpName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:<br /><font size="2">Ex: Enables Checks / Notifications at all times </font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $timeAlias?>" size="64" maxlength="512" id="tpAlias" name="tpAlias" /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Use Timeperiod:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="tpUse" name="tpUse" multiple="multiple">
                <option value=""> - Null - </option>
<?php
foreach ($viewData->timeperiods as $timePeriod => $tpArray) {
    if (!empty($timeName)) {
        if ($timePeriod == $timeName) continue;
        if ((isset($tpArray['use'])) && ($tpArray['use'] == $timeName)) continue;
    }
    if ($timePeriod == $useTimeperiod) {
?>
                <option value="<?php echo $timePeriod?>" selected><?php echo $timePeriod?></option>
<?php
    } else {
?>
                <option value="<?php echo $timePeriod?>"><?php echo $timePeriod?></option>
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
<div class="divCacGroup admin_box_blue" id="time-define-encap">
<div id="time-define-inputs">
Time Definition: <input type="text" value="" id="timeDefine" name="timeDefine" size="32" maxlength="96" />
Time Range: <input type="text" value="" id="timeRange" name="timeRange" size="32" maxlength="96" />
<input type="submit" value="Insert" style="font-size:14px;" onClick="insertTimeDefinition()">
</div>
<iframe
    height="300px" name="timeDefinitions" id="timeDefinitions" style="min-height:100px;width:97%;left:5px;"
    src="action.php?controller=timeperiod&action=view_timeperiod&deployment=<?php echo $deployment?>">
</iframe>
</div>
</div>
<?php

require HTML_FOOTER;
