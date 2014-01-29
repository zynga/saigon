<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$nrpecmdName = isset($viewData->nrpecmdInfo['cmd_name'])?$viewData->nrpecmdInfo['cmd_name']:'';
$nrpecmdDesc = isset($viewData->nrpecmdInfo['cmd_desc'])?$viewData->nrpecmdInfo['cmd_desc']:'';
$nrpecmdLine = isset($viewData->nrpecmdInfo['cmd_line'])?htmlspecialchars(base64_decode($viewData->nrpecmdInfo['cmd_line'])):'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="static/js/mastermeta_tables.js"></script>
<body>
<div id="misc_nrpe_cmd" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
<?php
if ((isset($viewData->action)) && ($viewData->action == 'delete')) {
?>
        <th colspan="2">Delete NRPE Command <?php echo $nrpecmdName?> for <?php echo $deployment?></th>
<?php
} else {
?>
        <th colspan="2">View NRPE Command <?php echo $nrpecmdName?> for <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $nrpecmdName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Description:</th>
        <td style="text-align:left;"><?php echo $nrpecmdDesc?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Command:</th>
        <td style="text-align:left;"><?php echo $nrpecmdLine?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
if ((isset($viewData->action)) && ($viewData->action == 'delete')) {
?>
<div id="subcancelbuttons">
    <a href="action.php?controller=nrpecmd&action=del_write&deployment=<?php echo $deployment?>&nrpecmd=<?php echo $nrpecmdName?>" class="deployBtn" title="Delete">Delete</a>
    <a href="action.php?controller=nrpecmd&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Cancel">Cancel</a>
</div>
<?php
} else {
?>
<div id="subcancelbuttons">
    <a href="action.php?controller=nrpecmd&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="NRPE Commands">NRPE Commands</a>
</div>
<?php
}
?>
</div>
<?php

require HTML_FOOTER;
