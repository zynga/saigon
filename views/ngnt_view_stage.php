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
$nodeTempType = isset($viewData->nodeInfo['type'])?$viewData->nodeInfo['type']:'';
$nodeRegex = isset($viewData->nodeInfo['regex'])?$viewData->nodeInfo['regex']:null;
$nodeServices = isset($viewData->nodeInfo['services'])?implode(',', $viewData->nodeInfo['services']):null;
$nodeHostGroup = isset($viewData->nodeInfo['hostgroup'])?$viewData->nodeInfo['hostgroup']:null;
$nodeHostTemp = isset($viewData->nodeInfo['hosttemplate'])?$viewData->nodeInfo['hosttemplate']:null;
$nodeStdHostTemp = isset($viewData->nodeInfo['stdtemplate'])?$viewData->nodeInfo['stdtemplate']:null;
$nodePriority = isset($viewData->nodeInfo['priority'])?$viewData->nodeInfo['priority']:null;
$nodeContacts = isset($viewData->nodeInfo['contacts'])?implode(',', $viewData->nodeInfo['contacts']):null;
$nodeContactGroups = isset($viewData->nodeInfo['contactgroups'])?implode(',', $viewData->nodeInfo['contactgroups']):null;
$nodeSvcTemplate = isset($viewData->nodeInfo['svctemplate'])?$viewData->nodeInfo['svctemplate']:null;
$nodeSvcEscalations = isset($viewData->nodeInfo['svcescs'])?implode(',', $viewData->nodeInfo['svcescs']):null;
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
    </tr>
    <tr>
        <th style="text-align:right;">Template Type:</th>
        <td style="text-align:left;"><?php echo $nodeTempType?></td>
    </tr>
<?php
if ($nodeRegex != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Host Regex:</th>
        <td style="text-align:left;"><?php echo $nodeRegex?></td>
    </tr>
<?php
}
if ($nodePriority != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Priority:</th>
        <td style="text-align:left;"><?php echo $nodePriority?></td>
    </tr>
<?php
}
if ($nodeStdHostTemp != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Saigon Standard Template:</th>
        <td style="text-align:left;"><?php echo $nodeStdHostTemp?></td>
    </tr>
<?php
}
if ($nodeHostTemp != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Nagios Host Template:</th>
        <td style="text-align:left;"><?php echo $nodeHostTemp?></td>
    </tr>
<?php
}
if ($nodeHostGroup != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Host Group:</th>
        <td style="text-align:left;"><?php echo $nodeHostGroup?></td>
    </tr>
<?php
}
if ($nodeServices != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Service Checks:</th>
        <td style="text-align:left;"><?php echo $nodeServices?></td>
    </tr>
<?php
}
if ($nodeSvcTemplate != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Service Template:</th>
        <td style="text-align:left;"><?php echo $nodeSvcTemplate?></td>
    </tr>
<?php
}
if ($nodeSvcEscalations != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Service Escalations:</th>
        <td style="text-align:left;"><?php echo $nodeServices?></td>
    </tr>
<?php
}
if ($nodeContacts != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Contacts:</th>
        <td style="text-align:left;"><?php echo $nodeContacts?></td>
    </tr>
<?php
}
if ($nodeContactGroups != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Contact Groups:</th>
        <td style="text-align:left;"><?php echo $nodeContactGroups?></td>
    </tr>
<?php
}
?>
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
