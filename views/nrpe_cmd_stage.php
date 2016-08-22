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
<div id="avail-nrpecmds" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available NRPE Commands</h2>
<table style="padding:5px;" id="table_nrpecmdResults" class="noderesults">
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
if ((isset($viewData->nrpecmds)) && (!empty($viewData->nrpecmds))) {
    foreach ($viewData->nrpecmds as $nrpecmd => $cmdArray) {
?>
        <tr>
            <td><?php echo $nrpecmd?></td>
            <td><?php echo $cmdArray['cmd_desc']?></td>
            <td><?php echo $cmdArray['deployment']?></td>
            <td>
<?php
        if ($viewData->deployment != 'common') {
            if ($cmdArray['deployment'] != $viewData->deployment) {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=nrpecmd&action=copy_common_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Copy</a>
<?php
            } else {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=nrpecmd&action=copy_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Copy</a>
                <a class="deployBtn" title="Copy To" href="action.php?controller=nrpecmd&action=copy_to_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Copy To</a>
                <div class="divCacGroup"></div>
                <a class="deployBtn" title="Modify" href="action.php?controller=nrpecmd&action=modify_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Modify</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=nrpecmd&action=del_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Delete</a>
<?php
            }
        } else {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=nrpecmd&action=copy_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Copy</a>
                <a class="deployBtn" title="Copy To" href="action.php?controller=nrpecmd&action=copy_to_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Copy To</a>
                <div class="divCacGroup"></div>
                <a class="deployBtn" title="Modify" href="action.php?controller=nrpecmd&action=modify_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Modify</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=nrpecmd&action=del_stage&deployment=<?php echo $deployment?>&cmdname=<?php echo $nrpecmd?>">Delete</a>
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
<a href="action.php?controller=nrpecmd&action=add_stage&deployment=<?php echo $deployment?>" class="menuItem">Add NRPE Command</a>
</div>
<div>

<?php

require HTML_FOOTER;
