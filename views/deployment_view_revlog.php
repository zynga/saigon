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
<div id="encapsulate" class="admin_box admin_box_blue admin_border_black divCacGroup" style="width:98%;">
    <div id="header" class="divCacGroup admin_border_black dataTables_wrapper" style="text-align:center;">
        Revision Log for Deployment: <?php echo $viewData->deployment?>
    </div>
<?php
foreach ($viewData->revisions as $rev => $revArray) {
?>
    <div id="rev-<?php echo $rev?>" class="parentClass divCacGroup admin_box">
        <img src="static/imgs/plusSign.gif">
        Revision: <?php echo $rev?>
    </div>
    <div class="parent-desc-rev-<?php echo $rev?> divHide divCacGroup admin_box admin_box_blue admin_border_black" style="border-width:2px;">
        <table class="noderesults">
            <tr>
                <th style="width:30%;text-align:right;">Modified by:</th>
                <td style="text-align:left;"><?php echo $revArray['users']?></td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Change Time:</th>
                <td style="text-align:left;"><?php echo $revArray['revtime']?></td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Change Note:</th>
                <td style="text-align:left;"><?php echo $revArray['revnote']?></td>
            </tr>
        </table>
    </div>
<?php
}
?>
</div>

<?php
require HTML_FOOTER;

