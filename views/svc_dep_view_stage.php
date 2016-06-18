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

$svcDepName = $viewData->svcDepInfo['name'];

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="svcs" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ($viewData->delFlag === true) {
?>
        <th colspan="2">Delete Service Group Information for <?php echo $svcDepName?> from <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">Service Group Information for <?php echo $svcDepName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $svcDepName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Parent Service:</th>
        <td style="text-align:left;"><?php echo $viewData->svcDepInfo['service_description']?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Dependent Service:</th>
        <td style="text-align:left;"><?php echo $viewData->svcDepInfo['dependent_service_description']?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Inherit Parent Dependencies:</th>
<?php
    if ((isset($viewData->svcDepInfo['inherits_parent'])) && ($viewData->svcDepInfo['inherits_parent'] == 1)) {
?>
        <td style="text-align:left;">Yes</td>
<?php
    }
    else {
?>
        <td style="text-align:left;">No</td>
<?php
    }
?>
    </tr><tr>
        <th style="width:30%;text-align:right;">Execution Criteria:</th>
        <td style="text-align:left;"><?php echo implode(',', $viewData->svcDepInfo['execution_failure_criteria'])?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Notification Criteria:</th>
        <td style="text-align:left;"><?php echo implode(',', $viewData->svcDepInfo['notification_failure_criteria'])?></td>
    </tr><tr>

</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($viewData->delFlag === true) {
?>
<a href="action.php?controller=svcdep&action=<?php echo $action?>&deployment=<?php echo $deployment?>&svcDep=<?php echo $svcDepName?>" class="deployBtn">Delete</a>
<a href="action.php?controller=svcdep&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=svcdep&action=stage&deployment=<?php echo $deployment?>" class="deployBtn">Service Dependencies</a>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
