<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$color = "#d5eaf0";
$revcolor = "#bcd9e1";
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
<div id="avail-ngnts" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Node Templates</h2>
<div class="dataTables_wrapper">
<table style="padding:5px;" id="table_ngntResults" class="noderesults">
    <thead>
        <tr>
            <th style="width:45%;">Name</th>
            <th>Type</th>
            <th style="width:30%;">Service Count</th>
            <th>Actions</th>
        </tr>
    </thead>
<?php
if ((isset($viewData->nodeInfo)) && (!empty($viewData->nodeInfo))) {
    foreach ($viewData->nodeInfo as $template => $templateArray) {
    $type = isset($templateArray['type'])?$templateArray['type']:'dynamic';
    if ($color == "#d5eaf0") {
        $color = "#bcd9e1";
        $revcolor = "#d5eaf0";
    } else {
        $color = "#d5eaf0";
        $revcolor = "#bcd9e1";
    }
?>
        <tr>
            <td style="text-align:left;background-color:<?php echo $color?>;vertical-align:text-top;">
                <div class="parentClass divCacGroup" id="<?php echo $template?>">
                    <img src="static/imgs/plusSign.gif">
                    <?php echo $template?>
                </div>
                <div class="divHide parent-desc-<?php echo $template?>">
                    <table style="width:99%;" id="ignore">
                        <tr>
<?php
        if ($type == 'dynamic') {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Regex:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['regex']?></td>
<?php
            if ((isset($templateArray['nregex'])) && (!empty($templateArray['nregex']))) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Negate Regex:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['nregex']?></td>
<?php
            }
        } else if ($type =='static') {
?>
                            <th style="width:25%;text-align:right;font-size:85%;word-wrap:break-word;">Host IPs:</th>
                            <td colspan="3" style="width:75%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['selhosts']?></td>
<?php
        }
?>
                        </tr><tr>
<?php
        if ((isset($templateArray['hostgroup'])) && (!empty($templateArray['hostgroup']))) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">HostGroup:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['hostgroup']?></td>
<?php
        }
        if ((isset($templateArray['hosttemplate'])) && (!empty($templateArray['hosttemplate']))) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Host Template:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['hosttemplate']?></td>
<?php
        }
?>
                        </tr><tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Deployment:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $viewData->deployment?></td>
<?php
        if ((isset($templateArray['subdeployment'])) && ($templateArray['subdeployment'] != 'N/A')) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Sub Deployment:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['subdeployment']?></td>
<?php
        }
?>
                        </tr><tr>
<?php
        if ((isset($templateArray['stdtemplate'])) && (!empty($templateArray['stdtemplate']))) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Standard Template:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['stdtemplate']?></td>
<?php
        }
?>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="background-color:<?php echo $color?>;"><?php echo $type?></td>
            <td style="background-color:<?php echo $color?>;">
                <div>
                    <?php echo isset($templateArray['services'])?count($templateArray['services']):'0'?>
                </div>
                <div class="divHide parent-desc-<?php echo $template?>" align="center">
                    <table id="ignore">
<?php
        if ((isset($templateArray['services'])) && (!empty($templateArray['services']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Active Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['services'])?></td>
                        </tr>
<?php
        } else {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Active Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;">None Defined or Included from Standard Template</td>
                        </tr>
<?php
        }
        if ((isset($templateArray['nservices'])) && (!empty($templateArray['nservices']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Negate Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['nservices'])?></td>
                        </tr>
<?php
        }
?>
                    </table>
                </div>
            </td>
            <td style="background-color:<?php echo $color?>;">
                <a class="deployBtn" title="Modify" href="action.php?controller=ngnt&action=modify_<?php echo $type?>_stage&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $template?>">Modify</a>
                <a class="deployBtn" title="Copy" href="action.php?controller=ngnt&action=copy_<?php echo $type?>_stage&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $template?>">Copy</a>
                <a class="deployBtn" title="Delete" href="action.php?controller=ngnt&action=del_<?php echo $type?>_stage&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $template?>">Delete</a>
            </td>
        </tr>
<?php
    }
}
if ((isset($viewData->cstdTemplates)) && (!empty($viewData->cstdTemplates))) {
    foreach ($viewData->cstdTemplates as $template => $templateArray) {
    $type = isset($templateArray['type'])?$templateArray['type']:'dynamic';
    if ($color == "#d5eaf0") {
        $color = "#bcd9e1";
        $revcolor = "#d5eaf0";
    } else {
        $color = "#d5eaf0";
        $revcolor = "#bcd9e1";
    }
?>
        <tr>
            <td style="text-align:left;background-color:<?php echo $color?>;vertical-align:text-top;">
                <div class="parentClass divCacGroup" id="<?php echo $template?>">
                    <img src="static/imgs/plusSign.gif">
                    <?php echo $template?>
                </div>
                <div class="divHide parent-desc-<?php echo $template?>">
                    <table style="width:99%;" id="ignore">
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Deployment:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $viewData->cdeployment?></td>
<?php
        if ((isset($templateArray['hosttemplate'])) && (!empty($templateArray['hosttemplate']))) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Host Template:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['hosttemplate']?></td>
<?php
        }
?>
                        </tr>
                    </table>
                </div>
            </td>
            <td style="background-color:<?php echo $color?>;"><?php echo $type?></td>
            <td style="background-color:<?php echo $color?>;">
                <div>
                    <?php echo isset($templateArray['services'])?count($templateArray['services']):'0'?>
                </div>
<?php
        if ((isset($templateArray['services'])) && (!empty($templateArray['services']))) {
?>
                <div class="divHide parent-desc-<?php echo $template?>" align="center">
                    <table id="ignore">
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Active Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['services'])?></td>
                        </tr>
<?php
            if ((isset($templateArray['nservices'])) && (!empty($templateArray['nservices']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Negate Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['nservices'])?></td>
                        </tr>
<?php
            }
?>
                    </table>
                </div>
<?php
        }
?>
            </td>
            <td style="background-color:<?php echo $color?>;">
            </td>
        </tr>
<?php
    }
}
?>
</table>
</div>
<div class="divCacGroup"><!-- 5 Pixel Spacer--></div>
<div class="divCacGroup"><!-- 5 Pixel Spacer--></div>
<a href="action.php?controller=ngnt&action=add_standard_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Standard Template</a>
<a href="action.php?controller=ngnt&action=add_dynamic_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Dynamic Template</a>
<a href="action.php?controller=ngnt&action=add_static_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Static Template</a>
<div class="divCacGroup"></div>
</div>
<?php

require HTML_FOOTER;
