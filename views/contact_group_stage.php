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
<div id="avail-contactgrps" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Contact Groups</h2>
<table style="padding:5px;" id="table_contactgrpResults" class="noderesults">
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
if ((isset($viewData->contactgroups)) && (!empty($viewData->contactgroups))) {
    foreach ($viewData->contactgroups as $contactGroup => $cgArray) {
?>
        <tr>
            <td><?php echo $contactGroup?></td>
            <td><?php echo $cgArray['alias']?></td>
            <td><?php echo $cgArray['deployment']?></td>
            <td>
<?php
        if ($viewData->deployment != 'common') {
            if ($cgArray['deployment'] != $viewData->deployment) {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=contactgrp&action=copy_common_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Copy</a>
<?php
            } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=contactgrp&action=modify_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=contactgrp&action=copy_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=contactgrp&action=del_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Delete</a>
<?php
            }
        } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=contactgrp&action=modify_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=contactgrp&action=copy_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=contactgrp&action=del_stage&deployment=<?php echo $deployment?>&cgName=<?php echo $contactGroup?>">Delete</a>
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
<a href="action.php?controller=contactgrp&action=add_stage&deployment=<?php echo $deployment?>" class="menuItem">Add Contact Group</a>
</div>
<div>

<?php

require HTML_FOOTER;
