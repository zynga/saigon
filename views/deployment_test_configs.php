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

$output = $viewData->test_output;
$output['output'] = base64_decode($output['output']);

if ($output['timestamp'] == '0000000000') {
    $color = "red";
} else if ($output['timestamp'] < time() - 60) {
    $color = "red";
} else {
    $color = "black";
}

function complexity ($level) {
    $string = str_repeat("<img height='50%' src=static/imgs/explosion.png>", $level);
    return $string;
}

?>
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
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<body>
<div id="encapsulate" class="admin_box admin_box_blue admin_border_black divCacGroup">
    <div id="header" class="divCacGroup dataTables_wrapper" style="text-align:center;">
        <table class="noderesults">
            <tr>
                <th style="width:30%;text-align:right;">Deployment:</th>
                <td style="text-align:left"><?php echo $viewData->deployment?></td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Revision:</th>
                <td style="text-align:left"><?php echo $viewData->revision?></td>
            </tr>
<?php
if (($viewData->jobadded === false) && ($viewData->running === false)) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Nagios Test Exit Code:</th>
                <td style="text-align:left;"><?php echo $output['exitcode']?></td>
            </tr>
<?php
}
?>
            <tr>
                <th style="width:30%;text-align:right;">Current Time:</th>
                <td style="text-align:left;"><?php echo strftime("%F %T", time())?></td>
            </tr>
<?php
if ($output['timestamp'] != '0000000000') {
?>
            <tr>
                <th style="width:30%;text-align:right;">Last Test Time:</th>
                <td style="text-align:left;">
                    <font color="<?php echo $color?>"><?php echo strftime("%F %T", $output['timestamp'])?></font>
                </td>
            </tr>
<?php
}
if (isset($output['totaltime'])) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Total Time Spent on Last Test:</th>
                <td style="text-align:left;">
                    <?php echo $output['totaltime']?> Seconds
                </td>
            </tr>
<?php
}
if ($viewData->jobadded === true) {
?>
            <tr>
                <th style="width:30%;text-align:right;">New Test Job Enqueued:</th>
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
?>
            <tr>
                <th style="width:30%;text-align:right;">Config Test Complexity Level:<br /> (#/5)</th>
                <td style="text-align:left;"><?php echo complexity($viewData->harsh)?></td>
            </tr>
<?php
if (($viewData->jobadded === true) ||
    (($viewData->jobadded === false) && ($viewData->running === true))) {
?>
            <tr>
                <th style="width:30%;text-align:right;">Page Refresh Time:</th>
                <td style="text-align:left;"><?php echo $viewData->refresh?> Seconds</td>
            </tr><tr>
                <th style="width:30%;text-align:right;">Processing Note:</th>
                <td style="text-align:left;"><?php echo $output['output']?></td>
            </tr>
        </table>
    </div>
    <div class="divCacGroup"></div>
<?php
} else {
?>
        </table>
    </div>
    <div class="divCacGroup"></div>
    <div id="test-results" class="parentClass divCacGroup admin_box">
        <img src="static/imgs/plusSign.gif">
        Nagios Test Results
    </div>
    <div class="parent-desc-test-results divHide divCacGroup admin_box admin_box_blue admin_border_black" style="border-width:2px;">
        <div id="pre-test-results" style="overflow:auto;min-width:400px">
            <pre>
<?php echo $output['output']?>
            </pre>
        </div>
    </div>
<?php
}
?>
</div>

<?php
require HTML_FOOTER;
