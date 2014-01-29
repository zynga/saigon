<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$cmdName = isset($viewData->commandInfo['command_name'])?$viewData->commandInfo['command_name']:'';
$cmdDesc = isset($viewData->commandInfo['command_desc'])?$viewData->commandInfo['command_desc']:'';
$cmdLine = isset($viewData->commandInfo['command_line'])?htmlspecialchars(base64_decode($viewData->commandInfo['command_line'])):'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="static/js/mastermeta_tables.js"></script>
<body>
<div id="delete_cmd" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<table class="noderesults">
    <thead>
        <th colspan="2">Delete Command <?php echo $cmdName?> for <?php echo $deployment?></th>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $cmdName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Description:</th>
        <td style="text-align:left;"><?php echo $cmdDesc?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Command:</th>
        <td style="text-align:left;"><?php echo $cmdLine?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div id="subcancelbuttons">
    <a href="action.php?controller=command&action=delWrite&deployment=<?php echo $deployment?>&command=<?php echo $cmdName?>" class="deployBtn" title="Delete">Delete</a>
    <a href="action.php?controller=command&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Cancel">Cancel</a>
</div>
</div>
<?php

require HTML_FOOTER;
