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
<div id="avail-clustercmds" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Clustered Commands</h2>
<table style="padding:5px;" id="table_clustercmdsResults" class="noderesults">
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
if ((isset($viewData->clustercmds)) && (!empty($viewData->clustercmds))) {
    foreach ($viewData->clustercmds as $clustercmd => $cmdArray) {
?>
        <tr>
            <td><?php echo $clustercmd?></td>
            <td><?php echo $cmdArray['service_description']?></td>
            <td><?php echo $deployment?></td>
            <td>
<?php
        if ($cmdArray['cctype'] == 'quorum') {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=clustercmds&action=modify_quorum_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $clustercmd?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=clustercmds&action=copy_quorum_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $clustercmd?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=clustercmds&action=del_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $clustercmd?>">Delete</a>
<?php
        }
        else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=clustercmds&action=modify_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $clustercmd?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=clustercmds&action=copy_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $clustercmd?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=clustercmds&action=del_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $clustercmd?>">Delete</a>
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
<div class="divCacGroup">
    <a href="action.php?controller=clustercmds&action=add_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Cluster Command</a>
    <a href="action.php?controller=clustercmds&action=add_quorum_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Quorum Cluster Command</a>
</div>
</div>

<?php

require HTML_FOOTER;
