<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
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
<div style="width:98%;padding:5px;">
<div class="divCacGroup admin_box_blue admin_box admin_border_black">
<form method="POST" action="action.php">
<input type="hidden" id="deployment" name="deployment" value="<?php echo $viewData->deployment?>" />
<input type="hidden" id="action" name="action" value="write" />
<input type="hidden" id="controller" name="controller" value="resourcecfg" />
<table class="noderesults">
    <thead>
        <tr>
            <th colspan="4">
                Resource Config Declarations
            </th>
        </tr>
    </thead>
    <tr>
        <td colspan="4">
            <div class="divCacGroup divCacSubResponse" style="text-align:left;">
                These resource config declarations are used to either shorten path requirements
                for command execution, or for including usernames / passwords which aren't
                exposable via the Nagios User Interface (security by obscurity).
                Any directory specified should not have a trailing / on it.
                <div class="divCacGroup"></div>
                Note, these resource declarations are not and can not be used in NRPE Configs, these are
                strictly for helping Nagios to build its pre-parsed commands to execute, which may then
                be passed down to NRPE.
            </div>
        </td>
    </tr>
<?php
$rowswitch = 0;
for ($i=1;$i<=32;$i++) {
    $key = "USER" . $i;
    if ($rowswitch == 0) {
?>
    <tr>
<?php
    }
    if ((isset($viewData->rcfg[$key])) && (!empty($viewData->rcfg[$key]))) {
?>
        <th style="width:20%;text-align:right;"><?php echo $key?></th>
        <td style="width:20%;text-align:left;">
            <input type="text" id="<?php echo $key?>" name="<?php echo $key?>" value="<?php echo base64_decode($viewData->rcfg[$key])?>" size="48" maxlength="256" />
        </td>
<?php
        $rowswitch++;
    } else {
?>
        <th style="width:20%;text-align:right;"><?php echo $key?></th>
        <td style="width:20%;text-align:left;">
            <input type="text" id="<?php echo $key?>" name="<?php echo $key?>" value="" size="48" maxlength="256" />
        </td>
<?php
        $rowswitch++;
    }
    if ($rowswitch == 2) {
        $rowswitch = 0;
?>
    </tr>
<?php
    }
}
?>
    <tr>
        <td colspan="4">
            <div class="parentClass divCacGroup" id="delrel" style="text-align:left;text-indent:25px;background-color:#FE2E2E;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Delete Resource Configuration:
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
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
</div>
</div>
<?php

require HTML_FOOTER;
