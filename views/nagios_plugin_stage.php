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
<div id="avail-nagiosplugins" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Nagios Plugins</h2>
<table style="padding:5px;" id="table_nagiospluginResults" class="noderesults">
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
if ((isset($viewData->plugins)) && (!empty($viewData->plugins))) {
    foreach ($viewData->plugins as $nagiosplugin => $pluginArray) {
?>
        <tr>
            <td><?php echo $nagiosplugin?></td>
            <td><?php echo $pluginArray['desc']?></td>
            <td><?php echo $pluginArray['deployment']?></td>
            <td>
<?php
        if ($viewData->deployment != 'common') {
            if ($pluginArray['deployment'] != $viewData->deployment) {
?>
                <a class="deployBtn" title="View" href="action.php?controller=nagiosplugin&action=show_plugin_common&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">View</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=nagiosplugin&action=copy_common_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Copy</a>
<?php
            } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=nagiosplugin&action=modify_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Modify</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=nagiosplugin&action=delete_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Delete</a>
                <a class="deployBtn" title="Copy To" href="action.php?controller=nagiosplugin&action=copy_to_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Copy To</a>
                <a class="deployBtn" title="View" href="action.php?controller=nagiosplugin&action=show_plugin&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">View</a>
<?php
            }
        } else {
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=nagiosplugin&action=modify_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Modify</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=nagiosplugin&action=delete_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Delete</a>
                <a class="deployBtn" title="Copy To" href="action.php?controller=nagiosplugin&action=copy_to_stage&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">Copy To</a>
                <a class="deployBtn" title="View" href="action.php?controller=nagiosplugin&action=show_plugin&deployment=<?php echo $deployment?>&plugin=<?php echo $nagiosplugin?>">View</a>
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
<a href="action.php?controller=nagiosplugin&action=add_stage&deployment=<?php echo $deployment?>" class="menuItem">Add Nagios Plugin</a>
</div>
<div>

<?php

require HTML_FOOTER;
