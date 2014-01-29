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
<div id="encapsulate" style="position:absolute;top:5;left:5;width:98%;">
    <div class="divCacGroup admin_box admin_box_blue admin_border_black">
        Revision has been Reset from Current Revision...
        <div class="divCacGroup"></div>
        <div class="divCacResponse">
        <b>Deployment:</b> <?php echo $viewData->deployment?><br />
        <b>Current Active Revision:</b> <?php echo $viewData->currrev?><br />
        <b>Future / Reset Revision:</b> <?php echo $viewData->nextrev?>
        </div>
    </div>
</div>

<?php

require HTML_FOOTER;

