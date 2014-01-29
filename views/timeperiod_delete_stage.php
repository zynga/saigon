<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;

$timeName = isset($viewData->timeName)?$viewData->timeName:'';
$timeAlias = isset($viewData->timeInfo['alias'])?$viewData->timeInfo['alias']:'';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="static/js/mastermeta_tables.js"></script>
<script type="text/javascript">
function insertTimeDefinition() {
    var dir = $('#timeDefine').val();
    var range = $('#timeRange').val();
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=timeperiod&action=add_timeperiod&dir=' + encodeURIComponent(dir) + '&range=' + encodeURIComponent(range),
        dataType: 'html',
        success: function( data ) {
            $('#timeDefinitions').attr('src', $('#timeDefinitions').attr('src'));
        }
    });
}
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
<div id="add-timeperiod" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="add-timeperiod" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<table class="noderesults">
    <thead>
        <th colspan="2">Delete Timeperiod <?php echo $timeName?> for <?php echo $deployment?></th>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;"><?php echo $timeName?></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias:</th>
        <td style="text-align:left;"><?php echo $timeAlias?></td>
    </tr>
</table>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" id="table_timeperiod_results">
<table class="noderesults">
        <thead>
                <tr>
                        <th>Directive</th>
                        <th>Range</th>
                </tr>
        </thead>
        <tbody>
<?php
foreach ($viewData->timeData as $md5Key => $tpArray) {
?>
                <tr>
                        <td><?php echo $tpArray['directive']?></td>
                        <td><?php echo $tpArray['range']?></td>
                </tr>
<?php
}
?>
        </tbody>
</table>
</div>
<div class="divCacGroup"></div>
<div id="subcancelbuttons">
    <a href="action.php?controller=timeperiod&action=del_write&deployment=<?php echo $deployment?>&timeperiod=<?php echo $timeName?>" class="deployBtn" title="Delete">Delete</a>
    <a href="action.php?controller=timeperiod&action=stage&deployment=<?php echo $deployment?>" class="deployBtn" title="Cancel">Cancel</a>
</div>
</div>
</div>
<?php

require HTML_FOOTER;
