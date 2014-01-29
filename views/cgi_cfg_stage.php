<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$cfg = $viewData->cgicfg;

$contacts = array_keys($viewData->contacts);
array_unshift($contacts, "*");

$authsysinfo = isset($cfg['authorized_for_system_information'])?explode(',', $cfg['authorized_for_system_information']):array();
$authconfinfo = isset($cfg['authorized_for_configuration_information'])?explode(',', $cfg['authorized_for_configuration_information']):array();
$authsyscmds = isset($cfg['authorized_for_system_commands'])?explode(',', $cfg['authorized_for_system_commands']):array();
$authallsvcs = isset($cfg['authorized_for_all_services'])?explode(',', $cfg['authorized_for_all_services']):array();
$authallhosts = isset($cfg['authorized_for_all_hosts'])?explode(',', $cfg['authorized_for_all_hosts']):array();
$authallsvccmds = isset($cfg['authorized_for_all_service_commands'])?explode(',', $cfg['authorized_for_all_service_commands']):array();
$authallhostcmds = isset($cfg['authorized_for_all_host_commands'])?explode(',', $cfg['authorized_for_all_host_commands']):array();
$authreadonly = isset($cfg['authorized_for_read_only'])?explode(',', $cfg['authorized_for_read_only']):array();

$splunkurl = isset($cfg['splunk_url'])?$cfg['splunk_url']:'http://127.0.0.1:8000/';
if (($value = base64_decode($splunkurl, true)) !== false) {
    $splunkurl = $value;
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#showctxhelp")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#usependstate")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#useauth")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#eschtmltags")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#refreshrate")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#statusmaplayout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#statuswrllayout")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#ensplunk")
        .multiselect({ selectedList: 1, multiple: false }).multiselectfilter(),
    $("#authsysinfo")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authconfinfo")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authsyscmds")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authreadonly")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authallsvcs")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authallhosts")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authallsvccmds")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter(),
    $("#authallhostcmds")
        .multiselect({ selectedList: 2, noneSelectedText: "Select Users" }).multiselectfilter();
});
</script>
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
<div id="modgearman" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="modgearman" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="mg_submit_form">
<input type="hidden" value="cgicfg" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="write" id="action" name="action" />
<table class="noderesults">
    <thead>
        <th colspan="4">
            <div class="divCacGroup">
            CGI Configuration for <?php echo $deployment?>
            <div class="divCacGroup"></div>
            Please make sure all fields are filled in, or selected to have a value
            </div>
        </th>
    </thead>
    <tr>
        <th style="text-align:right;width:30%;">
            Main Config File:
        </th>
        <td style="text-align:left;" colspan="3">
            <input type="text" id="maincfg" name="maincfg" value="<?php echo htmlspecialchars(base64_decode($cfg['main_config_file']))?>" size="48" maxlength="256" />
        </td>
    </tr><tr>
        <th style="text-align:right;width:30%;">
            Physical Path to HTML Files:
        </th>
        <td style="text-align:left;" colspan="3">
            <input type="text" value="<?php echo htmlspecialchars(base64_decode($cfg['physical_html_path']))?>" size="48" maxlength="256" id="htmlloc" name="htmlloc" />
        </td>
    </tr><tr>
        <th style="text-align:right;width:30%;">
            HTML URL Entry Path:
        </th>
        <td style="text-align:left;" colspan="3">
            <input type="text" id="htmlurl" name="htmlurl" size="48" maxlength="256" value="<?php echo htmlspecialchars(base64_decode($cfg['url_html_path']))?>" />
        </td>
    </tr><tr>
        <th style="text-align:right;width:30%;">
            Show Context Help:
        </th>
        <td style="text-align:left;">
            <select id="showctxhelp" name="showctxhelp" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['show_context_help'],0)?>>Disabled (Default)</option>
                <option value="1" <?php echo is_selected($cfg['show_context_help'],1)?>>Enabled</option>
            </select>
        </td>
        <th style="text-align:right;">
            Use Pending States:
        </th>
        <td style="text-align:left;">
            <select id="usependstate" name="usependstate" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['use_pending_states'],0)?>>Disabled</option>
                <option value="1" <?php echo is_selected($cfg['use_pending_states'],1)?>>Enabled (Default)</option>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Use Authentication:
        </th>
        <td style="text-align:left;">
            <select id="useauth" name="useauth" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['use_authentication'],0)?>>Disabled</option>
                <option value="1" <?php echo is_selected($cfg['use_authentication'],1)?>>Enabled (Default)</option>
            </select>
        </td>
        <th style="text-align:right;">
            Escape HTML Tags:
        </th>
        <td style="text-align:left;">
            <select id="eschtmltags" name="eschtmltags" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['escape_html_tags'],0)?>>Disabled</option>
                <option value="1" <?php echo is_selected($cfg['escape_html_tags'],1)?>>Enabled (Default)</option>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Refresh Time:
        </th>
        <td style="text-align:left;">
            <select id="refreshrate" name="refreshrate" multiple="multiple">
                <option value="30" <?php echo is_selected($cfg['refresh_rate'],30)?>>30 Seconds</option>
                <option value="45" <?php echo is_selected($cfg['refresh_rate'],45)?>>45 Seconds</option>
                <option value="60" <?php echo is_selected($cfg['refresh_rate'],60)?>>60 Seconds</option>
                <option value="75" <?php echo is_selected($cfg['refresh_rate'],75)?>>75 Seconds</option>
                <option value="90" <?php echo is_selected($cfg['refresh_rate'],90)?>>90 Seconds</option>
            </select>
        </td>
        <th style="text-align:right;">
            Ping Syntax:
        </th>
        <td style="text-align:left;">
            <input type="text" id="pingsyntax" name="pingsyntax" value="<?php echo htmlspecialchars(base64_decode($cfg['ping_syntax']))?>" size="40" maxlength="128" />
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Action URL Target:
        </th>
        <td style="text-align:left;">
            <input type="text" id="actiontarget" name="actiontarget" value="<?php echo $cfg['action_url_target']?>" size="40" maxlength="128" />
        </td>
        <th style="text-align:right;">
            Notes URL Target:
        </th>
        <td style="text-align:left;">
            <input type="text" id="notestarget" name="notestarget" value="<?php echo $cfg['notes_url_target']?>" size="40" maxlength="128" />
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Default Statusmap CGI Layout
        </th>
        <td style="text-align:left;">
            <select id="statusmaplayout" name="statusmaplayout" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['default_statusmap_layout'], 0)?>>User-Defined Coordinates</option>
                <option value="1" <?php echo is_selected($cfg['default_statusmap_layout'], 1)?>>Depth Layers</option>
                <option value="2" <?php echo is_selected($cfg['default_statusmap_layout'], 2)?>>Collapsed Tree</option>
                <option value="3" <?php echo is_selected($cfg['default_statusmap_layout'], 3)?>>Balanced Tree</option>
                <option value="4" <?php echo is_selected($cfg['default_statusmap_layout'], 4)?>>Circular</option>
                <option value="5" <?php echo is_selected($cfg['default_statusmap_layout'], 5)?>>Circular (Marked Up) (Default)</option>
            </select>
        </td>
        <th style="text-align:right;">
            Default Statuswrl (VRML) CGI Layout
        </th>
        <td style="text-align:left;">
            <select id="statuswrllayout" name="statuswrllayout" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['default_statuswrl_layout'], 0)?>>User-Defined Coordinates</option>
                <option value="2" <?php echo is_selected($cfg['default_statuswrl_layout'], 2)?>>Collapsed Tree</option>
                <option value="3" <?php echo is_selected($cfg['default_statuswrl_layout'], 3)?>>Balanced Tree</option>
                <option value="4" <?php echo is_selected($cfg['default_statuswrl_layout'], 4)?>>Circular (Default)</option>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Enable Splunk Integration:
        </th>
        <td style="text-align:left;">
            <select id="ensplunk" name="ensplunk" multiple="multiple">
                <option value="0" <?php echo is_selected($cfg['enable_splunk_integration'], 0)?>>Disabled (Default)</option>
                <option value="1" <?php echo is_selected($cfg['enable_splunk_integration'], 1)?>>Enabled</option>
            </select>
        </td>
        <th style="text-align:right;">
            Splunk Integration URL:
        </th>
        <td style="text-align:left;">
            <input type="text" id="splunkurl" name="splunkurl" value="<?php echo $splunkurl?>" size="48" maxlength="256" />
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Authorized for System Information Access:
        </th>
        <td style="text-align:left;">
            <select id="authsysinfo" name="authsysinfo[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authsysinfo)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
        <th style="text-align:right;">
            Authorized for Configuration Information Access:
        </th>
        <td style="text-align:left;">
            <select id="authconfinfo" name="authconfinfo[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authconfinfo)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Authorized for System Command Access:
        </th>
        <td style="text-align:left;">
            <select id="authsyscmds" name="authsyscmds[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authsyscmds)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
        <th style="text-align:right;">
            Authorized for Read Only Access:
        </th>
        <td style="text-align:left;">
            <select id="authreadonly" name="authreadonly[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authreadonly)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Authorized for All Services Information Access:
        </th>
        <td style="text-align:left;">
            <select id="authallsvcs" name="authallsvcs[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authallsvcs)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
        <th style="text-align:right;">
            Authorized for All Hosts Information Access:
        </th>
        <td style="text-align:left;">
            <select id="authallhosts" name="authallhosts[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authallhosts)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="text-align:right;">
            Authorized for All Services Command Access:
        </th>
        <td style="text-align:left;">
            <select id="authallsvccmds" name="authallsvccmds[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authallsvccmds)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
        <th style="text-align:right;">
            Authorized for All Hosts Command Access:
        </th>
        <td style="text-align:left;">
            <select id="authallhostcmds" name="authallhostcmds[]" multiple="multiple">
<?php
foreach ($contacts as $contact) {
?>
                <option value="<?php echo $contact?>" <?php echo is_selected_array($contact, $authallhostcmds)?>><?php echo $contact?></option>
<?php
}
?>
            </select>
        </td>
    </tr><tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="delrel" style="text-align:left;text-indent:25px;background-color:#FE2E2E;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Delete CGI Configuration:
            </div>
            <div class="divHide parent-desc-delrel">
                <table style="width:99%;">
                    <tr>
                        <th style="text-align:right;width:20%;background-color:#FE2E2E;">
                            Delete Config:
                        </th>
                        <td style="text-align:left;">
                            <input type="checkbox" name="delete" value="1" />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>

<?php

function is_selected($var,$check) {
    if ($var == $check) return "selected";
    return "";
}

function is_selected_array($var, array $array) {
    if (in_array($var, $array)) return "selected";
    return "";
}

require HTML_FOOTER;
