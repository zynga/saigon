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

$svcName = isset($viewData->svcGrpInfo['servicegroup_name'])?$viewData->svcGrpInfo['servicegroup_name']:'';
$svcAlias = isset($viewData->svcGrpInfo['alias'])?$viewData->svcGrpInfo['alias']:'';
$svcNotesUrl = isset($viewData->svcGrpInfo['notes_url'])?$viewData->svcGrpInfo['notes_url']:'';
$svcActionUrl = isset($viewData->svcGrpInfo['action_url'])?$viewData->svcGrpInfo['action_url']:'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
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
<div id="action-svc-group" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-svc-group" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=svcgrp" name="svc_group_write">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Service Group to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Service Group <?php echo $svcName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Service Group <?php echo $svcName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:35%;text-align:right;">Name:<br /><font size="2">Ex: svcgrp-check-apache-avail</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $svcName?>" size="64" maxlength="128" id="svcName" name="svcName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr> 
        <th style="width:35%;text-align:right;">Alias:<br /><font size="2">Ex: Check Apache Available ServiceGroup</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $svcAlias?>" size="64" maxlength="512" id="svcAlias" name="svcAlias" /></td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;" id="urls">
                <img src="static/imgs/plusSign.gif">
                Add Possible URL Information:
            </div>
            <div class="divHide parent-desc-urls" style="padding-left:100px;">
                <table>
                    <tr>
                        <th>Notes URL:</th><td><input type="text" value="<?php echo $svcNotesUrl?>" id="notesurl" name="notesurl" size="128" maxlength="256" /></td>
                    </tr><tr>
                        <th>Action URL:</th><td><input type="text" value="<?php echo $svcActionUrl?>" id="actionurl" name="actionurl" size="128" maxlength="256" /></td>
                    </tr>
                </table>
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
