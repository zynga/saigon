<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;

?>
<body>
<div class="divCacGroup admin_box_bg admin_box_blue admin_border_black">
<?php
if ((isset($viewData->todeployment)) && (!empty($viewData->todeployment))) {
?>
Successfully wrote Cluster Command Information for <?php echo $viewData->command?> in Deployment <?php echo $viewData->todeployment?> to datastore...
<?php
} else {
?>
Successfully wrote Cluster Command Information for <?php echo $viewData->command?> in Deployment <?php echo $viewData->deployment?> to datastore...
<?php
}
?>
<div class="divCacGroup"></div>
<a href="action.php?controller=clustercmds&action=stage&deployment=<?php echo $viewData->deployment?>" class="deployBtn">Cluster Commands</a>
</div>
<?php

require HTML_FOOTER;
