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
<div id="avail-svcs" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Services</h2>
<table style="padding:5px;" id="table_svcResults" class="noderesults">
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
if ((isset($viewData->svcs)) && (!empty($viewData->svcs))) {
    foreach ($viewData->svcs as $svc => $svcArray) {
?>
        <tr>
            <td><?php echo $svc?></td>
            <td><?php echo $svcArray['service_description']?></td>
            <td><?php echo $svcArray['deployment']?></td>
            <td>
<?php
        if ($viewData->deployment != 'common') {
            if ($svcArray['deployment'] != $viewData->deployment) {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=svc&action=copy_common_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Copy</a>
<?php
            } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=svc&action=modify_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=svc&action=copy_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=svc&action=del_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Delete</a>
<?php
            }
        } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=svc&action=modify_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=svc&action=copy_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=svc&action=del_stage&deployment=<?php echo $deployment?>&svc=<?php echo $svc?>">Delete</a>
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
<a href="action.php?controller=svc&action=add_stage&deployment=<?php echo $deployment?>" class="menuItem">Add Service</a>
</div>
<div>
<?php

require HTML_FOOTER;
