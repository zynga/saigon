<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="static/js/mastermeta_tables.js"></script>
<body>
<div id="avail-commands" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Commands</h2>
<table style="padding:5px;" id="table_commandResults" class="noderesults">
    <thead>
        <tr>
            <th>Name</th>
            <th>Description</th>
            <th>Deployment</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($viewData->commands)) && (!empty($viewData->commands))) {
    foreach ($viewData->commands as $command => $cmdArray) {
?>
        <tr>
            <td><?php echo $command?></td>
            <td><?php echo $cmdArray['command_desc']?></td>
            <td><?php echo $cmdArray['deployment']?></td>
            <td>
<?php
        if ($viewData->deployment != 'common') {
            if ($cmdArray['deployment'] != $viewData->deployment) {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=command&action=copyCommonStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Copy</a>
<?php
            } else {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=command&action=copyStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Copy</a>
                <a class="deployBtn" title="Copy To" href="action.php?controller=command&action=copyToStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Copy To</a>
                <div class="divCacGroup"></div>
                <a class="deployBtn" title="Modify" href="action.php?controller=command&action=modifyStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Modify</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=command&action=delStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Delete</a>
<?php
            }
        } else {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=command&action=copyStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Copy</a>
                <a class="deployBtn" title="Copy To" href="action.php?controller=command&action=copyToStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Copy To</a>
                <div class="divCacGroup"></div>
                <a class="deployBtn" title="Modify" href="action.php?controller=command&action=modifyStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Modify</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=command&action=delStage&deployment=<?php echo $deployment?>&cmdName=<?php echo $command?>">Delete</a>
<?php
        }
?>
            </td>
        </tr>
<?php
    }
}
?>
    </tbody>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer--></div>
<a href="action.php?controller=command&action=addStage&deployment=<?php echo $deployment?>" class="menuItem">Add Command</a>
</div>
<div>

<?php

require HTML_FOOTER;
