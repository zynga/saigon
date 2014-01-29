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
<body>
<div id="table_timeperiod_results">
<table class="noderesults">
    <thead>
        <tr>
            <th>Directive</th>
            <th>Range</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($_SESSION[$deployment]['timeperiods'])) && (!empty($_SESSION[$deployment]['timeperiods']))) {
    foreach ($_SESSION[$deployment]['timeperiods'] as $md5Key => $tpArray) {
?>
        <tr>
            <td><?php echo $tpArray['directive']?></td>
            <td><?php echo $tpArray['range']?></td>
            <td>
                <a
                    href="action.php?controller=timeperiod&action=del_timeperiod&dir=<?php echo $md5Key?>&deployment=<?php echo $deployment?>"
                    title="Delete" class="deployBtn">Delete</a>
            </td>
        </tr>
<?php
    }
}
?>
    </tbody>
</table>
</div>
<?php
require HTML_FOOTER;
