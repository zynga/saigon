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
Successfully wrote Supplemental NRPE Configuration Information for Deployment <?php echo $viewData->deployment?> to datastore...
<div class="divCacGroup"></div>
<a href="action.php?controller=nrpecfg&action=supstage&deployment=<?php echo $viewData->deployment?>" class="deployBtn">Supplemental NRPE Configuration</a>
</div>
<?php

require HTML_FOOTER;
