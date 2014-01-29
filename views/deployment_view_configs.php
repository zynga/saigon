<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

if (($viewData->jobadded === true) ||
    (($viewData->jobadded === false) && ($viewData->running === true))) {
    require MINIFIED_REFRESH_HTML_HEADER;
} else {
    require MINIFIED_HTML_HEADER;
}

$configs = isset($viewData->results['configs'])?json_decode($viewData->results['configs'],true):array();
$meta = $viewData->results;
if (isset($meta['configs'])) unset($meta['configs']);

if ($meta['timestamp'] == '0000000000') {
    $color = "red";
} else if ($meta['timestamp'] < time() - 60) {
    $color = "red";
} else {
    $color = "black";
}


?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<link href="static/css/shCore.css" rel="stylesheet" type="text/css" />
<link href="static/css/shThemeDefault.css" rel="stylesheet" type="text/css" />
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
<body>
<div id="encapsulate" class="admin_box admin_box_blue admin_border_black divCacGroup" style="width:98%;">
    <div id="header" class="divCacGroup admin_border_black dataTables_wrapper" style="text-align:center;">
        <table class="noderesults">
            <tr>
                <th style="width:30%;text-align:right;">Deployment:</th>
                <td style="text-align:left"><?php echo $viewData->deployment?></td>
            </tr>
<?php
if (($viewData->subdeployment !== false) && (!empty($viewData->subdeployment))) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Sub Deployment:</th>
                <td style="text-align:left"><?php echo $viewData->subdeployment?></td>
            </tr>
<?php
}
?>
            <tr>
                <th style="width:30%;text-align:right;">Revision:</th>
                <td style="text-align:left"><?php echo $viewData->revision?></td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Current Time:</th>
                <td style="text-align:left;"><?php echo strftime("%F %T", time())?></td>
            </tr>
<?php
if ($meta['timestamp'] != '0000000000') {
?>
            <tr>
                <th style="width:30%;text-align:right;">Last Build Time:</th>
                <td style="text-align:left;">
                    <font color="<?php echo $color?>"><?php echo strftime("%F %T", $meta['timestamp'])?></font>
                </td>
            </tr>
<?php
}
if (isset($meta['totaltime'])) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Total Time Spent on Last Build:</th>
                <td style="text-align:left;">
                    <?php echo $meta['totaltime']?> Seconds
                </td>
            </tr>
<?php
}
if ($viewData->jobadded === true) {
?>
            <tr>
                <th style="width:30%;text-align:right;">New Build Job Enqueued:</th>
                <td style="text-align:left;"><?php echo $viewData->jobadded === true ? 'Yes' : 'No'?></td>
            </tr>
<?php
} else if ($viewData->running === true) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Job Running:</th>
                <td style="text-align:left;"><?php echo $viewData->running === true ? 'Yes' : 'No'?></td>
            </tr>
<?php
}
if ((!empty($configs)) && ($viewData->jobadded !== true) && ($viewData->running !== true)) {
?>
        </table>
    </div>
    <div class="divCacGroup"></div>
<?php
    foreach ($configs as $file => $fileOutput) {
        if (empty($fileOutput)) continue;
        $tmpArray = preg_split('/\./', $file);
?>
    <div id="file-<?php echo $tmpArray[0]?>" class="parentClass divCacGroup admin_box">
        <img src="static/imgs/plusSign.gif">
        File: <?php echo $file?>
    </div>
    <div class="parent-desc-file-<?php echo $tmpArray[0]?> divHide divCacGroup admin_box admin_box_blue admin_border_black" style="border-width:2px;">
        <div id="pre-<?php echo $tmpArray[0]?>" style="overflow:auto;min-width:400px">
            <table class="noderesults">
                <tr>
                    <th style="width:25%;text-align:right;">MD5 Sum:</th>
                    <td style="text-align:left;"><?php echo md5($fileOutput)?></td>
                </tr>
            </table>
            <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php echo $fileOutput?>
            </pre>
        </div>
    </div>
<?php
    }
} else {
?>
            <tr>
                <th style="width:30%;text-align:right;">Page Refresh Time:</th>
                <td style="text-align:left;"><?php echo $viewData->refresh?> Seconds</td>
            </tr>
        </table>
    </div>
    <div class="divCacGroup"></div>
<?php
}
?>
</div>
<script type="text/javascript">
    SyntaxHighlighter.all()
</script>
<?php
require HTML_FOOTER;
