<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;

$cmdName = isset($viewData->clustercmdsInfo['name'])?$viewData->clustercmdsInfo['name']:'';
$cmdServer = isset($viewData->clustercmdsInfo['server'])?$viewData->clustercmdsInfo['server']:null;
$cmdType = isset($viewData->clustercmdsInfo['type'])?$viewData->clustercmdsInfo['type']:null;
$cmdWarnMode = isset($viewData->clustercmdsInfo['warnmode'])?$viewData->clustercmdsInfo['warnmode']:null;
$cmdWarnMin = isset($viewData->clustercmdsInfo['warnmin'])?$viewData->clustercmdsInfo['warnmin']:null;
$cmdWarnMax = isset($viewData->clustercmdsInfo['warnmax'])?$viewData->clustercmdsInfo['warnmax']:null;
$cmdCritMode = isset($viewData->clustercmdsInfo['critmode'])?$viewData->clustercmdsInfo['critmode']:null;
$cmdCrit = isset($viewData->clustercmdsInfo['crit'])?$viewData->clustercmdsInfo['crit']:null;
$cmdQuery = isset($viewData->clustercmdsInfo['query'])?htmlspecialchars(base64_decode($viewData->clustercmdsInfo['query'])):null;
$cmdDesc = isset($viewData->clustercmdsInfo['service_description'])?$viewData->clustercmdsInfo['service_description']:null;
$cmdQuorum = isset($viewData->clustercmdsInfo['quorum'])?$viewData->clustercmdsInfo['quorum']:null;
$cmdCCType = isset($viewData->clustercmdsInfo['cctype'])?$viewData->clustercmdsInfo['cctype']:null;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="static/js/mastermeta_tables.js"></script>
<body>
<div id="misc_cluster_cmds" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ((isset($viewData->action)) && ($viewData->action == 'delete')) {
    if ($cmdCCType == 'quorum') {
?>
        <th colspan="2">Delete Cluster Quorum Command <?php echo $cmdName?> for <?php echo $deployment?></th>
<?php
    } else {
?>
        <th colspan="2">Delete Cluster Command <?php echo $cmdName?> for <?php echo $deployment?></th>
<?php
    }
} else {
    if ($cmdCCType == 'quorum') {
?>
        <th colspan="2">View Cluster Quorum Command <?php echo $cmdName?> for <?php echo $deployment?></th>
<?php
    } else {
?>
        <th colspan="2">View Cluster Command <?php echo $cmdName?> for <?php echo $deployment?></th>
<?php
    }
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $cmdName?></td>
    </tr>
<?php
if ($cmdServer != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Nagios Server:</th>
        <td style="text-align:left;"><?php echo $cmdServer?></td>
    </tr>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Type:</th>
        <td style="text-align:left;"><?php echo $cmdType?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Description:</th>
        <td style="text-align:left;"><?php echo $cmdDesc?></td>
    </tr>
<?php
if ($cmdWarnMode != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Warning Mode:</th>
        <td style="text-align:left;"><?php echo $cmdWarnMode?></td>
    </tr>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Warning Minimum Value:</th>
        <td style="text-align:left;"><?php echo $cmdWarnMin?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Warning Maximum Value:</th>
        <td style="text-align:left;"><?php echo $cmdWarnMax?></td>
    </tr>
<?php
if ($cmdCritMode != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Critical Mode:</th>
        <td style="text-align:left;"><?php echo $cmdCritMode?></td>
    </tr>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Critical:</th>
        <td style="text-align:left;"><?php echo $cmdCrit?></td>
    </tr>
<?php
if ($cmdQuorum != null) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Quorum:</th>
        <td style="text-align:left;"><?php echo $cmdQuorum?></td>
    </tr>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Command:</th>
        <td style="text-align:left;"><pre><?php echo $cmdQuery?></pre></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ((isset($viewData->action)) && ($viewData->action == 'delete')) {
?>
<div id="subcancelbuttons">
    <a href="action.php?controller=clustercmds&action=del_write&deployment=<?php echo $deployment?>&cmdname=<?php echo $cmdName?>" class="deployBtn" title="Delete">Delete</a>
    <a href="action.php?controller=clustercmds&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Cancel">Cancel</a>
</div>
<?php
} else {
?>
<div id="subcancelbuttons">
    <a href="action.php?controller=clustercmds&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Cluster Commands">Cluster Commands</a>
</div>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
