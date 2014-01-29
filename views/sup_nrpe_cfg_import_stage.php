<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
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
<div id="supnrpecfgimport" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="supnrpecfgimport" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="POST" action="action.php" name="sup_nrpe_cfg_import_stage" enctype="multipart/form-data">
<input type="hidden" value="nrpecfg" id="controller" name="controller" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="sup_import_write" id="action" name="action" />
<table class="noderesults">
    <thead>
        <th colspan="2">Import Pre-Existing Supplemental NRPE Config to <?php echo $deployment?></th>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">
            File Name and Location:<br /><font size="2">Ex: /usr/local/nagios/etc/nrpe.d/<?php echo $viewData->deployment?>.cfg</font>
        </th>
        <td style="text-align:left;">
            <input type="text" value="/usr/local/nagios/etc/nrpe.d/<?php echo $viewData->deployment?>.cfg" size="64" maxlength="256" id="location" name="location" />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">
            File:
        </th>
        <td style="text-align:left;">
            <input type="file" id="file" name="file" />
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

require HTML_FOOTER;
