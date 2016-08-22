<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$showUnclassified = true;
$deployment = $viewData->deployment;
$color = "#d5eaf0";
$revcolor = "#bcd9e1";
?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
    $('.parentClass').click(function() {
        $('.parent-desc-' + $(this).attr("id")).slideToggle("fast");
        if ($('.parent-desc-' + $(this).attr("id")).hasClass('open')) {
            $(this).find("img").attr("src", "static/imgs/plusSign.gif");
            $('.parent-desc-' + $(this).attr("id")).addClass('closed');
            $('.parent-desc-' + $(this).attr("id")).removeClass('open');
        } else {
            $(this).find("img").attr("src", "static/imgs/minusSign.gif");
            $('.parent-desc-' + $(this).attr("id")).addClass('open');
            $('.parent-desc-' + $(this).attr("id")).removeClass('closed');
        }
    });
});
</script>
<script type="text/javascript">
$(function() {
    $('.expandcollapse').click(function() {
        var all = $('.ec_trigger'),
            active = all.filter('.open'),
            inactive = all.not('.open');
        if (all.length && all.length === active.length) {
            // All open, close them
            $.each( all, function( i, val ) {
                if (all[i].previousElementSibling !== null) {
                    var id = all[i].previousElementSibling.id;
                    if (id !== null) {
                        $('#' + id).find("img").attr("src", "static/imgs/plusSign.gif");
                    }
                }
            });
            all.slideToggle("fast").addClass('closed').removeClass('open').next();
        }
        else {
            // At least some are closed, open them all
            $.each( inactive, function( i, val ) {
                if (inactive[i].previousElementSibling !== null) {
                    var id = inactive[i].previousElementSibling.id;
                    if (id !== null) {
                        $('#' + id).find("img").attr("src", "static/imgs/minusSign.gif");
                    }
                }
            });
            all.not('.open').slideToggle("fast").addClass('open').removeClass('closed').next();
        }
        return false;
    });
});
</script>
<body>
<div id="avail-ngnts" style="border-width:2px;width:98%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<h2>Available Node Templates</h2>
<button class="expandcollapse">Expand / Collapse All Templates</button>
<div class="divCacGroup"><!-- 5 Pixel Spacer--></div>
<div class="dataTables_wrapper">
<table style="padding:5px;" id="table_ngntResults" class="noderesults">
    <thead>
        <tr>
            <th style="width:40%;max-width:40%;">Name<font size="2">  (Priority)</font></th>
            <th style="width:8%;max-width:8%;">Type</th>
            <th style="width:30%;max-width:30%;">Misc Info</th>
            <th style="width:20%;max-width:20%;">Actions</th>
        </tr>
    </thead>
<?php
if ((isset($viewData->nodeInfo)) && (!empty($viewData->nodeInfo))) {
    foreach ($viewData->nodeInfo as $template => $templateArray) {
    $nodePriority = isset($templateArray['priority'])?$templateArray['priority']:1;
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
<?php
    if ($type == "standard") {
?>
            <td style="text-align:left;background-color:#EDE245;vertical-align:text-top;">
<?php
    }
    elseif ($type == "unclassified") {
?>
            <td style="text-align:left;background-color:#018F06;vertical-align:text-top;">
<?php
    }
    else {
?>
            <td style="text-align:left;background-color:<?php echo $color?>;vertical-align:text-top;">
<?php
    }
?>
                <div class="parentClass divCacGroup" id="<?php echo $template?>">
                    <img src="static/imgs/plusSign.gif">
<?php
    if ( ($type == "standard") || ($type == "unclassified") ) {
?>
                    <?php echo $template?>
<?php
    }
    else {
?>
                    <?php echo $template?><font size="2">   (Priority: <?php echo $nodePriority?>)</font>
<?php
    }
?>
                </div>
                <div class="ec_trigger divHide parent-desc-<?php echo $template?>" id="<?php echo $template?>-main">
                    <table style="width:100%;table-layout:fixed;" id="ignore">
                        <tr>
<?php
        if ( ($type == 'dynamic') || ($type == 'unclassified') ) {
            $i = 0;
            $keys = array(
                'deployment' => 'Deployment', 'regex' => 'Regex',
                'nregex' => 'Negate Regex', 'hosttemplate' => 'Host Template', 'hostgroup' => 'HostGroup',
                'svctemplate' => 'Service Template', 'stdtemplate' => 'Saigon Standard Template',
            );
            foreach ($keys as $key => $value) {
                if ((isset($templateArray[$key])) && (!empty($templateArray[$key]))) {
                    ++$i;
?>
                            <th style="width:25%;max-width:25%;text-align:right;font-size:85%;"><?php echo $value?>:</th>
                            <td style="word-wrap:break-word;width:25%;max-width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray[$key]?></td>
<?php
                }
                if ($i == 2) {
                    $i = 0;
?>
                        </tr><tr>
<?php
                }
            }
            if ($type == 'unclassified') {
                $showUnclassified = false;
            }
        }
        elseif ($type == "standard") {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Deployment:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $viewData->deployment?></td>
<?php
            if ((isset($templateArray['hosttemplate'])) && (!empty($templateArray['hosttemplate']))) {
?>
                            <th style="width:25%;text-align:right;font-size:85%;">Host Template:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['hosttemplate']?></td>
<?php
            }
        }
?>
                        </tr>
                    </table>
                </div>
            </td>
<?php
    if ($type == "standard") {
?>
            <td style="background-color:#EDE245;"><?php echo $type?></td>
            <td style="background-color:#EDE245;">
<?php
    }
    elseif ($type == "unclassified") {
?>
            <td style="background-color:#018F06;"><?php echo $type?></td>
            <td style="background-color:#018F06;">
<?php
    }
    else {
?>
            <td style="background-color:<?php echo $color?>;"><?php echo $type?></td>
            <td style="background-color:<?php echo $color?>;">
<?php
    }
?>
                <div class="ec_trigger divHide parent-desc-<?php echo $template?>" align="center">
                    <table id="ignore">
<?php
        if ((isset($templateArray['services'])) && (!empty($templateArray['services']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Service Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['services'])?></td>
                        </tr>
<?php
        }
        if ((isset($templateArray['svcescs'])) && (!empty($templateArray['svcescs']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Service Escalations:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['svcescs'])?></td>
                        </tr>
<?php
        }
        if ((isset($templateArray['nservices'])) && (!empty($templateArray['nservices']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Negate Service Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['nservices'])?></td>
                        </tr>
<?php
        }
        if ((isset($templateArray['contacts'])) && (!empty($templateArray['contacts']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Contacts:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['contacts'])?></td>
                        </tr>
<?php
        }
        if ((isset($templateArray['contactgroups'])) && (!empty($templateArray['contactgroups']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Contact Groups:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['contactgroups'])?></td>
                        </tr>
<?php
        }
?>
                    </table>
                </div>
            </td>
<?php
    if ($type == "standard") {
?>
            <td style="background-color:#EDE245;">
<?php
    }
    elseif ($type == "unclassified") {
?>
            <td style="background-color:#018F06;">
<?php
    }
    else {
?>
            <td style="background-color:<?php echo $color?>;">
<?php
    }
?>
                <a class="deployBtn" title="Modify" href="action.php?controller=ngnt&action=modify_<?php echo $type?>_stage&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $template?>">Modify</a>
<?php
    if ($type != "unclassified") {
?>
                <a class="deployBtn" title="Copy" href="action.php?controller=ngnt&action=copy_<?php echo $type?>_stage&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $template?>">Copy</a>
<?php
    }
?>
                <a class="deployBtn" title="Delete" href="action.php?controller=ngnt&action=del_<?php echo $type?>_stage&deployment=<?php echo $deployment?>&nodeTemp=<?php echo $template?>">Delete</a>
            </td>
        </tr>
<?php
    }
}
if ((isset($viewData->cstdTemplates)) && (!empty($viewData->cstdTemplates))) {
    foreach ($viewData->cstdTemplates as $template => $templateArray) {
        $type = isset($templateArray['type'])?$templateArray['type']:'dynamic';
        if ($type != "standard") continue;
        if ($color == "#d5eaf0") {
            $color = "#bcd9e1";
            $revcolor = "#d5eaf0";
        } else {
            $color = "#d5eaf0";
            $revcolor = "#bcd9e1";
        }
?>
        <tr>
            <td style="text-align:left;background-color:#EDE245;vertical-align:text-top;">
                <div class="parentClass divCacGroup" id="<?php echo $template?>">
                    <img src="static/imgs/plusSign.gif">
                    <?php echo $template?>
                </div>
                <div class="ec_trigger divHide parent-desc-<?php echo $template?>">
                    <table style="width:99%;" id="ignore">
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Deployment:</th>
                            <td style="width:25%;text-align:left;font-size:75%;background-color:<?php echo $revcolor?>;"><?php echo $templateArray['deployment']?></td>
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
            <td style="background-color:#EDE245;"><?php echo $type?></td>
            <td style="background-color:#EDE245;">
                <div class="ec_trigger divHide parent-desc-<?php echo $template?>" align="center">
                    <table id="ignore">
<?php
        if ((isset($templateArray['services'])) && (!empty($templateArray['services']))) {
?>
                        <tr>
                            <th style="width:25%;text-align:right;font-size:85%;">Active Checks:</th>
                            <td style="font-size:75%;text-align:center;background-color:<?php echo $revcolor?>;"><?php echo implode(', ', $templateArray['services'])?></td>
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
            <td style="background-color:#EDE245;">
            </td>
        </tr>
<?php
    }
}
?>
</table>
</div>
<div class="divCacGroup"><!-- 5 Pixel Spacer--></div>
<button class="expandcollapse">Expand / Collapse All Templates</button>
<div class="divCacGroup"><!-- 5 Pixel Spacer--></div>
<a href="action.php?controller=ngnt&action=add_standard_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Standard Template</a>
<a href="action.php?controller=ngnt&action=add_dynamic_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Dynamic Template</a>
<?php
if ($showUnclassified === true) {
?>
<a href="action.php?controller=ngnt&action=add_unclassified_stage&deployment=<?php echo $deployment?>" class="deployBtn">Add Unclassified Template</a>
<?php
}
?>
<div class="divCacGroup"></div>
</div>
<?php

require HTML_FOOTER;
