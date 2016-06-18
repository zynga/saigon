<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

if ($viewData->action == 'diff_configs') {
    $url = "action.php?&deployment={$viewData->deployment}&controller={$viewData->controller}";
    $url .= "&action={$viewData->action}";
    $url .= "&fromrev={$viewData->fromrev}&torev={$viewData->torev}";
} else {
    $url = "action.php?&deployment={$viewData->deployment}&controller={$viewData->controller}";
    $url .= "&action={$viewData->action}&revision={$viewData->revision}";
}
?>
<html>
<head>
    <link type="text/css" rel="stylesheet" href="static/css/admin.css">
    <link type="text/css" rel="stylesheet" href="static/css/jquery-ui.css" />
    <meta http-equiv="refresh" content="<?php echo $viewData->refresh?>;URL='<?php echo $url?>'">
</head>
<script type="text/javascript" src="static/js/jquery.js"></script>
<script type="text/javascript" src="static/js/jquery-ui.custom.min.js"></script>
