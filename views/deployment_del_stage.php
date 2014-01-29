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
<b>WARNING:</b> This action can not be undone, the entire deployment will be removed from the data store
<div class="divCacGroup"></div>
<div class="divCacResponse">
Delete Requested for Deployment: <?php echo $viewData->deployment?><br />
</div>
<div class="divCacGroup"></div>
<a class="deployBtn" title="Delete Deployment" href="action.php?controller=deployment&action=del_write&deployment=<?php echo $viewData->deployment?>">Delete</a>
<a class="deployBtn" title="Cancel" href="action.php?controller=deployment&action=stage">Cancel</a>
</div>
<?php

require HTML_FOOTER;
