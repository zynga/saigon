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
<div id="table_host_search_results">
<table class="noderesults">
    <thead>
        <tr>
            <th style="width:20%;">Location</th>
            <th style="width:20%;">Search Parameter</th>
            <th style="width:20%;">Note</th>
            <th style="width:10%;">Actions</th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($_SESSION[$deployment]['deployments'])) && (!empty($_SESSION[$deployment]['deployments']))) {
    array_multisort($_SESSION[$deployment]['deployments']);
    foreach ($_SESSION[$deployment]['deployments'] as $md5Key => $dArray) {
?>
        <tr>
            <td><?php echo $dArray['location']?></td>
            <td><?php echo $dArray['srchparam']?></td>
            <td><?php echo isset($dArray['note'])?$dArray['note']:'N/A'?></td>
            <td>
                <a
                    href="action.php?controller=deployment&action=del_hostSearch&lp=<?php echo $md5Key?>&deployment=<?php echo $deployment?>"
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
