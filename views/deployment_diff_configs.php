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

?>
<link href="static/css/shCore.css" rel="stylesheet" type="text/css" />
<link href="static/css/tables.css" rel="stylesheet" type="text/css" />
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
<div class="admin_box admin_box_blue admin_border_black divCacGroup" style="width:98%;">
    <div class="divCacGroup dataTables_wrapper" style="text-align:center;">
        <table class="noderesults">
            <tr>
                <th style="width:30%;text-align:right;">Deployment:</th>
                <td style="text-align:left"><?php echo $viewData->deployment?></td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Current Time:</th>
                <td style="text-align:left;"><?php echo strftime("%F %T", time())?></td>
            </tr>
<?php
if ((isset($viewData->meta['status'])) && ($viewData->meta['status'] == 'success')) {
?>
            <tr>
                <th style="widht:30%;text-align:right;">Start Time:</th>
                <td style="text-align:left;"><?php echo strftime("%F %T", $viewData->meta['starttime'])?></td>
            </tr><tr>
                <th style="widht:30%;text-align:right;">End Time:</th>
                <td style="text-align:left;"><?php echo strftime("%F %T", $viewData->meta['timestamp'])?></td>
            </tr><tr>
                <th style="widht:30%;text-align:right;">Total Time to Diff:</th>
                <td style="text-align:left;"><?php echo $viewData->meta['totaltime']?> Seconds</td>
            </tr>
<?php
}
if ((isset($viewData->meta['fromrev'])) && (!empty($viewData->meta['fromrev']))) {
?>
            <tr>
                <th style="widht:30%;text-align:right;">From Revision:</th>
                <td style="text-align:left;"><?php echo $viewData->meta['fromrev']?></td>
            </tr>
<?php
}
if ((isset($viewData->meta['torev'])) && (!empty($viewData->meta['torev']))) {
?>
            <tr>
                <th style="widht:30%;text-align:right;">To Revision:</th>
                <td style="text-align:left;"><?php echo $viewData->meta['torev']?></td>
            </tr>
<?php
}
if (($viewData->running === true) || ($viewData->jobadded === true)) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Page Refresh Time:</th>
                <td style="text-align:left;"><?php echo $viewData->refresh?> Seconds</td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Job Running:</th>
                <td style="text-align:left;">Yes</td>
            </tr>
<?php
}
if ((isset($viewData->meta['output'])) && (!empty($viewData->meta['output']))) {
?>
            <tr>
                <th style="widht:30%;text-align:right;">Job Output:</th>
                <td style="text-align:left;"><?php echo base64_decode($viewData->meta['output'])?></td>
            </tr>
<?php
}
?>
        </table>
    </div>
    <div class="divCacGroup"></div>
<?php
if ((isset($viewData->meta['status'])) && ($viewData->meta['status'] == 'success')) {
?>
    <div class="divCacGroup dataTables_wrapper" style="text-align:center;">
        Nagios Core Configuration Files...
    </div>
<?php
    foreach ($viewData->diff as $dKey => $dObj) {
        $val = $dObj->getGroupedOpcodes();
        if (empty($val)) continue;
        $tmpArray = explode(".", $dKey);
?>
    <div id="file-container" class="divCacGroup">
        <div id="<?php echo $tmpArray[0]?>" class="parentClass" style="text-align:left;">
            <img src="static/imgs/plusSign.gif">
            File: <?php echo $dKey?>
        </div>
        <div class="parent-desc-<?php echo $tmpArray[0]?> divHide">
            <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php
        $renderer = new Diff_Renderer_Text_Unified();
        print htmlspecialchars($dObj->render($renderer));
?>
            </pre>
        </div>
    </div>
<?php
    }
?>
    <div class="divCacGroup"></div>
    <div class="divCacGroup dataTables_wrapper" style="text-align:center;">
        Nagios Cluster Plugin Files...
    </div>
<?php
    foreach ($viewData->nagplugins as $dKey => $dObj) {
        $val = $dObj->getGroupedOpcodes();
        if (empty($val)) continue;
        $tmpArray = explode(".", $dKey);
?>
    <div id="file-container" class="divCacGroup">
        <div id="np-<?php echo $tmpArray[0]?>" class="parentClass" style="text-align:left;">
            <img src="static/imgs/plusSign.gif">
            File: <?php echo $dKey?>
        </div>
        <div class="parent-desc-np-<?php echo $tmpArray[0]?> divHide">
            <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php
        $renderer = new Diff_Renderer_Text_Unified();
        print htmlspecialchars($dObj->render($renderer));
?>
            </pre>
        </div>
    </div>
<?php
    }
?>
    <div class="divCacGroup"></div>
    <div class="divCacGroup dataTables_wrapper" style="text-align:center;">
        NRPE Core Plugin Files...
    </div>
<?php
    foreach ($viewData->cplugins as $dKey => $dObj) {
        $val = $dObj->getGroupedOpcodes();
        if (empty($val)) continue;
        $tmpArray = explode(".", $dKey);
?>
    <div id="file-container" class="divCacGroup">
        <div id="nrpep-<?php echo $tmpArray[0]?>" class="parentClass" style="text-align:left;">
            <img src="static/imgs/plusSign.gif">
            File: <?php echo $dKey?>
        </div>
        <div class="parent-desc-nrpep-<?php echo $tmpArray[0]?> divHide">
            <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php
        $renderer = new Diff_Renderer_Text_Unified();
        print htmlspecialchars($dObj->render($renderer));
?>
            </pre>
        </div>
    </div>
<?php
    }
?>
    <div class="divCacGroup"></div>
    <div class="divCacGroup dataTables_wrapper" style="text-align:center;">
        NRPE Supplemental Plugin Files...
    </div>
<?php
    foreach ($viewData->splugins as $dKey => $dObj) {
        $val = $dObj->getGroupedOpcodes();
        if (empty($val)) continue;
        $tmpArray = explode(".", $dKey);
?>
    <div id="file-container" class="divCacGroup">
        <div id="nrpesp-<?php echo $tmpArray[0]?>" class="parentClass" style="text-align:left;">
            <img src="static/imgs/plusSign.gif">
            File: <?php echo $dKey?>
        </div>
        <div class="parent-desc-nrpesp-<?php echo $tmpArray[0]?> divHide">
            <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php
        $renderer = new Diff_Renderer_Text_Unified();
        print htmlspecialchars($dObj->render($renderer));
?>
            </pre>
        </div>
    </div>
<?php
    }
}
?>

<script type="text/javascript">
    SyntaxHighlighter.all()
</script>
<div class="divCacGroup"></div>
</div>
<?php
require HTML_FOOTER;
