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
            <th style="width:30%;">Host</th>
            <th style="width:25%;">IP</th>
            <th>SubDeployment</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($_SESSION[$deployment]['static-deployments'])) && (!empty($_SESSION[$deployment]['static-deployments']))) {
    foreach ($_SESSION[$deployment]['static-deployments'] as $encIP => $dArray) {
?>
        <tr>
            <td style="width:40%;"><?php echo $dArray['host']?></td>
            <td style="width:35%;"><?php echo $dArray['ip']?></td>
            <td><?php echo isset($dArray['subdeployment'])?$dArray['subdeployment']:'N/A'?></td>
            <td>
                <a
                    href="action.php?controller=deployment&action=del_static_hostSearch&lp=<?php echo $encIP?>&deployment=<?php echo $deployment?>"
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
