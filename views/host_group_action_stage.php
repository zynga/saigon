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

$hostName = isset($viewData->hostGrpInfo['hostgroup_name'])?$viewData->hostGrpInfo['hostgroup_name']:'';
$hostAlias = isset($viewData->hostGrpInfo['alias'])?$viewData->hostGrpInfo['alias']:'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
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
<div id="action-host-group" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-host-group" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php?controller=hostgrp" name="host_group_write">
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Host Group to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Host Group <?php echo $hostName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Host Group <?php echo $hostName?> to <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:35%;text-align:right;">Name:<br /><font size="2">Ex: mw-mc-userblob</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $hostName?>" size="64" maxlength="128" id="hostName" name="hostName" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:35%;text-align:right;">Alias:<br /><font size="2">Ex: MafiaWars Userblob Memcached Store</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $hostAlias?>" size="64" maxlength="512" id="hostAlias" name="hostAlias" /></td>
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
