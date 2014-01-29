<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$jsdata = json_encode($viewData->nrpecmds);

$filelocation = isset($viewData->nrpecfg['location'])?$viewData->nrpecfg['location']:"/usr/local/nagios/etc/nrpe.d/" . $deployment . ".cfg";
$supcmds = isset($viewData->nrpecfg['cmds'])?explode(',', $viewData->nrpecfg['cmds']):array();

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $("#supcmds")
        .multiselect({
            noneSelectedText: "Select Commands",
        }).multiselectfilter();
});
</script>
<script type="text/javascript">
$(function() {
    var target = $("#nrpecmdwindow");
    var data = <?php echo $jsdata?>;
    $("select")
        .multiselect()
        .bind("multiselectclick multiselectcheckall multiselectuncheckall",
            function( event, ui ) {
                var checkedValues = $.map($(this).multiselect("getChecked"),
                    function( input ) {
                        return input.value;
                    });
                if (checkedValues.length > 0) {
                    var output;
                    $.each(checkedValues, function() {
                        if (output == undefined) {
                            output = data[this] + '<br />';
                        } else {
                            output += data[this] + '<br />';
                        }
                    });
                    target.html (
                        output
                    );
                } else {
                    target.html (
                        'Please Select Some Commands'
                    );
                }
            })
        .triggerHandler("multiselectclick");
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
<div id="nrpecfg" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="nrpecfg" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="nrpe_cfg_write">
<input type="hidden" value="nrpecfg" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="supwrite" id="action" name="action" />
<table class="noderesults">
    <thead>
        <th colspan="4">Supplemental NRPE Configuration File Management for <?php echo $deployment?></th>
    </thead>
    <tr>
        <td colspan="4">
            <div style="height:25px;padding-top:10px;" class="divCacGroup divCacSubResponse">
                Leave the Available Commands Empty, if you want to delete / nullify the supplemental config
            </div>
        </td>
    </tr><tr>
        <th style="width:25%;text-align:right;">File Location:</th>
        <td style="width:25%;text-align:left;" colspan="3">
            <input type="text" value="<?php echo $filelocation?>" size="64" maxlength="128" id="location" name="location" />
        </td>
    </tr><tr>
        <th colspan="1" style="width:25%;text-align:right;">Available Commands:</th>
        <td colspan="3" style="text-align:left;">
            <select id="supcmds" name="supcmds[]" multiple="multiple">
<?php
foreach ($viewData->nrpecmds as $nrpecmd => $nrpecmdInfo) {
    if (in_array($nrpecmd, $supcmds)) {
?>
                <option value="<?php echo $nrpecmd?>" selected><?php echo $nrpecmd?></option>
<?php
    } else {
?>
                <option value="<?php echo $nrpecmd?>"><?php echo $nrpecmd?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
<div id="supcmdsout" class="divCacGroup admin_border_black" style="border-width:2px;">
    Supplemental NRPE Command Configuration Window
    <div id="nrpecmdwindow" class="divCacSubResponse divCacGroup">
    </div>
</div>
</div>
<?php

require HTML_FOOTER;
