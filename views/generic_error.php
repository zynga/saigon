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
<b><?php echo $viewData->header?>:</b><br />
<div class="divCacGroup"></div>
<div class="divCacResponse">
<?php echo $viewData->error?>
</div>
<div class="divCacGroup"></div>
</div>
<?php

require HTML_FOOTER;
