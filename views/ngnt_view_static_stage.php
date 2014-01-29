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
$nodeTemp = isset($viewData->nodeInfo['name'])?$viewData->nodeInfo['name']:'';
$nodes = isset($viewData->nodeInfo['selhosts'])?explode(',', $viewData->nodeInfo['selhosts']):'';
$nodeServices = isset($viewData->nodeInfo['services'])?preg_replace('/,/', ', ', $viewData->nodeInfo['services']):'';
$nodeHostGroup = isset($viewData->nodeInfo['hostgroup'])?$viewData->nodeInfo['hostgroup']:'';
$nodeHostTemp = isset($viewData->nodeInfo['hosttemplate'])?$viewData->nodeInfo['hosttemplate']:'';
$nodeStdHostTemp = isset($viewData->nodeInfo['stdtemplate'])?$viewData->nodeInfo['stdtemplate']:'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="container" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;border-width:2px;">
<table class="noderesults">
    <thead>
<?php
if ($action == 'del_write') {
?>
        <th colspan="2">Delete Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">View Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $nodeTemp?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Saigon Standard Template:</th>
        <td style="text-align:left;"><?php echo $nodeStdHostTemp?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Nagios Host Template:</th>
        <td style="text-align:left;"><?php echo $nodeHostTemp?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Group:</th>
        <td style="text-align:left;"><?php echo $nodeHostGroup?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Checks:</th>
        <td style="text-align:left;"><?php echo $nodeServices?></td>
    </tr>
</table>
<table class="noderesults">
    <thead>
        <th>Hostname</th>
        <th>Address</th>
    </thead>
    <tbody>
<?php
sort($viewData->hosts);
foreach ($viewData->hosts as $hostname => $hostArray) {
    if (in_array($hostArray['address'], $nodes)) {;
?>
        <tr><td><?php echo $hostArray['host_name']?></td><td><?php echo $hostArray['address']?></td></tr>
<?php
    }
}
?>
    </tbody>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ($action == 'del_write') {
?>
<a href="action.php?controller=ngnt&action=<?php echo $action?>&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $nodeTemp?>" class="deployBtn">Delete</a>
<a href="action.php?controller=ngnt&action=manage&deployment=<?php echo $deployment?>" class="deployBtn">Cancel</a>
<?php
} else {
?>
<a href="action.php?controller=ngnt&action=manage&deployment=<?php echo $deployment?>" class="deployBtn">Services</a>
<?php
}
?>
</div>

<?php

require HTML_FOOTER;
