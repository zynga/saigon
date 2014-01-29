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
<div id="table_brokermodules_results">
<table class="noderesults">
    <thead>
        <tr>
            <th colspan="3">
                Enabled Broker Modules
            </th>
        </tr>
    </thead>
    <tbody>
<?php
if ((isset($_SESSION[$deployment]['brokermods'])) && (!empty($_SESSION[$deployment]['brokermods']))) {
    foreach ($_SESSION[$deployment]['brokermods'] as $md5Key => $b64data) {
?>
        <tr>
            <th>Broker Module:</th>
            <td style="width=60%;"><?php echo htmlspecialchars(base64_decode($b64data))?></td>
            <td>
                <a
                    href="action.php?controller=nagioscfg&action=del_brokermod&bmod=<?php echo $md5Key?>&deployment=<?php echo $deployment?>"
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
