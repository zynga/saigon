<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="static/js/mastermeta_tables.js"></script>
<body>
<div id="avail-deployments" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Deployments</h2>
<table style="padding:5px;" id="table_deploymentResults" class="noderesults">
    <thead>
        <tr>
            <th style="width:15%">Name</th>
            <th style="width:25%">Description</th>
            <th style="width:20%">Auth Groups</th>
            <th style="width:10%">Common Repo</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($viewData->deployments)) && (!empty($viewData->deployments))) {
    foreach ($viewData->deployments as $deployment) {
?>
        <tr>
            <td><?php echo $deployment?></td>
            <td><?php echo $viewData->deployinfo[$deployment]['desc']?></td>
            <td><?php echo preg_replace('/,/', ', ', $viewData->deployinfo[$deployment]['authgroups'])?></td>
            <td><?php echo $viewData->deployinfo[$deployment]['commonrepo']?></td>
            <td>
<?php
        if ($deployment != 'common') {
?>
                <a class="deployBtn" title="Modify Deployment Information" href="action.php?controller=deployment&action=modify_stage&deployment=<?php echo $deployment?>">Modify</a>
                <a class="deployBtn" title="Delete Deployment Information" href="action.php?controller=deployment&action=del_stage&deployment=<?php echo $deployment?>">Delete</a>
                <div class="divCacGroup"></div>
                <a class="deployBtn" title="Show Nagios Configuration Files" href="action.php?controller=deployment&action=show_configs_stage&deployment=<?php echo $deployment?>">Show Configs</a>
                <a class="deployBtn" title="Test Nagios Configuration Files" href="action.php?controller=deployment&action=test_configs_stage&deployment=<?php echo $deployment?>">Test Configs</a>
                <a class="deployBtn" title="Diff Nagios Configuration Files" href="action.php?controller=deployment&action=diff_configs_stage&deployment=<?php echo $deployment?>">Diff Configs</a>
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
<a href="action.php?controller=deployment&action=add_stage" class="menuItem">Add Deployment</a>
</div>

<?php

require HTML_FOOTER;
