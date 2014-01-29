<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
?>
<div class="admin_box_blue" style="font-size:.85em;">
<?php
    print base64_decode($viewData->cmdline);
?>
</div>
<?php
