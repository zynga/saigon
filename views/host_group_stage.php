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
<div id="avail-hostgrps" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Host Groups</h2>
<table style="padding:5px;" id="table_hostgrpResults" class="noderesults">
    <thead>
        <tr>
            <th>Name</th>
            <th>Alias</th>
            <th>Deployment</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($viewData->hostgroups)) && (!empty($viewData->hostgroups))) {
    foreach ($viewData->hostgroups as $hostGroup => $hostArray) {
?>
        <tr>
            <td><?php echo $hostGroup?></td>
            <td><?php echo $hostArray['alias']?></td>
            <td><?php echo $hostArray['deployment']?></td>
            <td>
<?php
        if ($viewData->deployment != 'common') {
            if ($hostArray['deployment'] != $viewData->deployment) {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=hostgrp&action=copy_common_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Copy</a>
<?php
            } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=hostgrp&action=modify_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=hostgrp&action=copy_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=hostgrp&action=del_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Delete</a>
<?php
            }
        } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=hostgrp&action=modify_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=hostgrp&action=copy_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=hostgrp&action=del_stage&deployment=<?php echo $deployment?>&hostName=<?php echo $hostGroup?>">Delete</a>
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
<a href="action.php?controller=hostgrp&action=add_stage&deployment=<?php echo $deployment?>" class="menuItem">Add Host Group</a>
</div>
<div>

<?php

require HTML_FOOTER;
