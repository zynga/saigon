<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
if (is_array($viewData->revision)) {
    sort($viewData->revision);
    $revision = implode(", ", $viewData->revision);
} else {
    $revision = $viewData->revision;
}
?>
<body>
<div id="encapsulate" style="position:absolute;top:5;left:5;width:98%;">
    <div class="divCacGroup admin_box admin_box_blue admin_border_black">
        <b>Deployment:</b> <?php echo $viewData->deployment?><br />
        <b>Revision(s):</b> <?php echo $revision?><br />
        <div class="divCacSubResponse">
            Deployment revision(s) successfully removed...
        </div>
    </div>
</div>

<?php

require HTML_FOOTER;

