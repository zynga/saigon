<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$action = $viewData->action;
if ($action == 'modify_plugin') {
    $modifyFlag = true;
}

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
<script type="text/javascript">
$(function() {
    $("#location")
        .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Location",
        multiple: false,
        minWidth: 300,
    }).multiselectfilter();
});
</script>
<body>
<?php
if ((isset($viewData->error)) && (!empty($viewData->error))) {
?>
<div id="error" style="border-width:2px;width:97%;left:5;top:5;position:absolute;text-align:center;background-color:red;" class="admin_box_blue divCacGroup admin_border_black">
<b>
<?php
    print $viewData->error;
?>
</b>
</div>
<div id="nagiosplugin" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="nagiosplugin" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="POST" action="action.php" name="nagios_plugin_action_write" enctype="multipart/form-data">
<input type="hidden" value="nagiosplugin" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_plugin') {
?>
        <th colspan="2">Add Nagios Plugin to <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_plugin') {
?>
        <th colspan="2">Modify Nagios Plugin <?php echo $pluginName?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">
            Name:<br /><font size="2">Ex: check_puppet.pl</font>
        </th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $pluginName?>" size="64" maxlength="128" id="name" name="name" <?php echo isset($modifyFlag)?'readonly':''?> />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            Description:<br /><font size="2">Ex: Checks Puppet YAML File<br /> to Ensure Puppet Executes</font>
        </th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $pluginDesc?>" size="64" maxlength="512" id="desc" name="desc" />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            Location:<br />
        </th>
        <td style="text-align:left;">
            <select id="location" name="location" multiple="multiple">
                <option value="/usr/local/nagios/libexec/" <?php echo is_selected($pluginLocation, '/usr/local/nagios/libexec/')?>>/usr/local/nagios/libexec/</option>
                <option value="/usr/lib/nagios/plugins/" <?php echo is_selected($pluginLocation, '/usr/lib/nagios/plugins/')?>>/usr/lib/nagios/plugins/</option>
                <option value=""></option>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            File:
        </th>
        <td style="text-align:left;">
            <input type="file" id="file" name="file" />
        </td>
    </tr>
<?php
if ($action == 'modify_plugin') {
?>
    <tr>
        <th style="width:30%;text-align:right;">
            MD5 Sum:
        </th>
        <td style="text-align:left;">
            <?php echo $pluginMD5?>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            Use Old File Contents:
        </th>
        <td style="text-align:left;">
            <input type="checkbox" id="useoldfile" name="useoldfile" value="1" />
        </td>
    </tr>
</table>
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
<?php
} else {
?>
</table>
<?php
}
?>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>
<script type="text/javascript">
    SyntaxHighlighter.all()
</script>

<?php

function is_selected($var,$check) {
    if ($var == $check) return "selected";
    return "";
}

require HTML_FOOTER;
