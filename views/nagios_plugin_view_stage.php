<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
if ((isset($viewData->ccs)) && ($viewData->ccs === true)) {
    $deployment = 'common';
} else {
    $deployment = $viewData->deployment;
}
$action = $viewData->action;
$pluginName = isset($viewData->plugin['name'])?$viewData->plugin['name']:'';
$pluginDesc = isset($viewData->plugin['desc'])?$viewData->plugin['desc']:'';
$pluginContents = isset($viewData->plugin['file'])?htmlspecialchars(base64_decode($viewData->plugin['file'])):'';
$pluginMD5 = isset($viewData->plugin['md5'])?$viewData->plugin['md5']:'';
$pluginLocation = isset($viewData->plugin['location'])?htmlspecialchars(base64_decode($viewData->plugin['location'])):'';

?>
<link href="static/css/shCore.css" rel="stylesheet" type="text/css" />
<link href="static/css/shThemeDefault.css" rel="stylesheet" type="text/css" />
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/shCore.js"></script>
<script type="text/javascript" src="static/js/brushes/shBrushPhp.js"></script>
<script type="text/javascript">
$(function() {
    $('.parentClass').click(function() {
    $('.parent-desc-' + $(this).attr("id")).slideToggle("fast");
    if ($(this).find("img").attr("src") == "static/imgs/minusSign.gif") {
        $(this).find("img").attr("src", "static/imgs/plusSign.gif");
    } else {
        $(this).find("img").attr("src", "static/imgs/minusSign.gif");
    }
    });
});
</script>
<?php
if (($action == 'copy_to_write') && (!isset($viewData->ccs))) {
?>
<script type="text/javascript">
$(function() {
    $("#todeployment")
        .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Deployment",
        multiple: false,
    }).multiselectfilter();
});
</script>
<?php
}
?>
<body>
<div id="nagios_plugin" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
if ($action == 'copy_to_write') {
?>
<form method="post" action="action.php" name="nagios_plugin_copy_to">
<input type="hidden" value="nagiosplugin" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<input type="hidden" value="<?php echo $pluginName?>" id="plugin" name="plugin" />
<?php
    if ((isset($viewData->ccs)) && ($viewData->ccs === true)) {
?>
<input type="hidden" value="1" id="ccs" name="ccs" />
<?php
    }
}
?>
<table class="noderesults">
    <thead>
<?php
if ($action == 'delete') {
?>
        <th colspan="2">Delete Nagios Plugin <?php echo $pluginName?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_to_write') {
?>
        <th colspan="2">Copy Nagios Plugin <?php echo $pluginName?> from <?php echo $deployment?> to another deployment</th>
<?php
} else {
?>
        <th colspan="2">View Nagios Plugin <?php echo $pluginName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">
            Name:
        </th>
        <td style="text-align:left;">
            <?php echo $pluginName?>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            Description:
        </th>
        <td style="text-align:left;">
            <?php echo $pluginDesc?>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            Location:
        </th>
        <td style="text-align:left;">
            <?php echo $pluginLocation?>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            MD5 Sum:
        </th>
        <td style="text-align:left;">
            <?php echo $pluginMD5?>
        </td>
    </tr>
<?php
if ($action == 'copy_to_write') {
?>
    <tr>
        <th style="width:30%;text-align:right;">Deployment to Copy Plugin to:</th>
        <td style="text-align:left;">
<?php
    if ((isset($viewData->ccs)) && ($viewData->ccs === true)) {
?>
            <input type="hidden" value="<?php echo $viewData->deployment?>" id="todeployment" name="todeployment" readonly />
            <?php echo $viewData->deployment?>
<?php
    } else {
?>
            <select id="todeployment" name="todeployment" multiple="multiple">
<?php
       foreach ($viewData->availdeployments as $deploy) {
            if ($deploy == $viewData->deployment);
?>
                <option value="<?php echo $deploy?>"><?php echo $deploy?></option>
<?php
            }
?>
            </select>
<?php
    }
?>
        </td>
    </tr>
<?php
}
?>
</table>
<?php
if ($action == 'copy_to_write') {
?>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
} else if ($action == 'delete') {
?>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div id="subcancelbuttons">
    <a href="action.php?controller=nagiosplugin&action=delete_plugin&deployment=<?php echo $deployment?>&plugin=<?php echo $pluginName?>" class="deployBtn" title="Delete">Delete</a>
    <a href="action.php?controller=nagiosplugin&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Cancel">Cancel</a>
</div>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
} else {
?>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div id="subcancelbuttons">
    <a href="action.php?controller=nagiosplugin&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Nagios Plugins">Nagios Plugins</a>
</div>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<?php
}
?>
<div style="width:98%;" class="divCacGroup">
    <div class="parentClass" id="filecontents">
        <img src="static/imgs/plusSign.gif">
        File Contents: 
    </div>
    <div class="divHide parent-desc-filecontents">
        <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php echo $pluginContents?>
        </pre>
    </div>
</div>
</div>
<script type="text/javascript">
    SyntaxHighlighter.all()
</script>
<?php

require HTML_FOOTER;
