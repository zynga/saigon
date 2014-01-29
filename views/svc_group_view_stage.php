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

$svcName = isset($viewData->svcGrpInfo['servicegroup_name'])?$viewData->svcGrpInfo['servicegroup_name']:'<i>null or incl from template</i>';
$svcAlias = isset($viewData->svcGrpInfo['alias'])?$viewData->svcGrpInfo['alias']:'<i>null or incl from template</i>';
$svcNotesUrl = isset($viewData->svcGrpInfo['notes_url'])?$viewData->svcGrpInfo['notes_url']:'<i>null or incl from template</i>';
$svcActionUrl = isset($viewData->svcGrpInfo['action_url'])?$viewData->svcGrpInfo['action_url']:'<i>null or incl from template</i>';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="svcs" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Service Group Information for <?php echo $svcName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">Service Group Information for <?php echo $svcName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $svcName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:</th>
        <td style="text-align:left;"><?php echo $svcAlias?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Notes URL:</th>
        <td style="text-align:left;"><?php echo $svcNotesUrl?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Action URL:</th>
        <td style="text-align:left;"><?php echo $svcActionUrl?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=svcgrp&action=<?php echo $action?>&deployment=<?php echo $deployment?>&svcName=<?php echo $svcName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=svcgrp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=svcgrp&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Service Groups</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
