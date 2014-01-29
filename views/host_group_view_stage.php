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

$hostName = isset($viewData->hostGrpInfo['hostgroup_name'])?$viewData->hostGrpInfo['hostgroup_name']:'<i>null or incl from template</i>';
$hostAlias = isset($viewData->hostGrpInfo['alias'])?$viewData->hostGrpInfo['alias']:'<i>null or incl from template</i>';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="hosts" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Host Group Information for <?php echo $hostName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">Host Group Information for <?php echo $hostName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $hostName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:</th>
        <td style="text-align:left;"><?php echo $hostAlias?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=hostgrp&action=<?php echo $action?>&deployment=<?php echo $deployment?>&hostName=<?php echo $hostName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=hostgrp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=hostgrp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Host Groups</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
