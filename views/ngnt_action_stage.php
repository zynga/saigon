<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
$deployment = $viewData->deployment;
$action = $viewData->action;
$type = $viewData->ngnttype;

if ($action == 'modify_write') {
    $modifyFlag = true;
}

$nodeTemp = isset($viewData->nodeInfo['name'])?$viewData->nodeInfo['name']:'';
$nodeRegex = isset($viewData->nodeInfo['regex'])?$viewData->nodeInfo['regex']:'';
$nodeNegateRegex = isset($viewData->nodeInfo['nregex'])?$viewData->nodeInfo['nregex']:'';
$nodeServices = isset($viewData->nodeInfo['services'])?$viewData->nodeInfo['services']:array();
$nodeNServices = isset($viewData->nodeInfo['nservices'])?$viewData->nodeInfo['nservices']:array();
$nodeHostGroup = isset($viewData->nodeInfo['hostgroup'])?$viewData->nodeInfo['hostgroup']:'';
$nodeHostTemplate = isset($viewData->nodeInfo['hosttemplate'])?$viewData->nodeInfo['hosttemplate']:'';
$nodeStdHostTemplate = isset($viewData->nodeInfo['stdtemplate'])?$viewData->nodeInfo['stdtemplate']:'';
$nodeSubDeployment = isset($viewData->nodeInfo['subdeployment'])?$viewData->nodeInfo['subdeployment']:'N/A';
if (empty($viewData->services)) {
    $viewData->error = 'Unable to detect available services to apply to nodes';
}

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
$(function() {
$("#hostgroup")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Hostgroup",
        multiple: false,
    }).multiselectfilter(),
$("#stdtemplate")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Standard Template",
        multiple: false,
    }).multiselectfilter(),
$("#hosttemplate")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Host Template",
        multiple: false,
    }).multiselectfilter(),
$("#subdeployment")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select a Sub Deployment",
        multiple: false,
    }).multiselectfilter(),
$("#services")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Services",
    }).multiselectfilter(),
$("#nservices")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Negate Services",
    }).multiselectfilter();
});
</script>
<script type="text/javascript">
function checkHostRegex() {
    $('#regexbutton').attr('disabled','disabled');
    $('#regexbutton').addClass('grey');
    $('#results').empty();
    var sData = {};
    sData['controller'] = "ngnt";
    sData['action'] = "view_dynamic_matches";
    sData['deployment'] = "<?php echo $deployment?>";
    sData['regex'] = $('#nodeRegex').val();
    sData['nregex'] = $('#nodeNegateRegex').val();
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: sData,
        dataType: 'html',
        success: function( data ) {
            $('#results').html( data );
            $('#regexbutton').removeClass('grey');
            $('#regexbutton').removeAttr('disabled');
        }
    });
}
</script>
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
<?php
if ((isset($viewData->error)) && (!empty($viewData->error))) {
?>
<div class="divCacGroup"></div>
<div id="error" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;background-color:red;border-width:2px">
    <div class="divCacGroup" style="text-align:center;">
        <b>Error Detected: <?php echo $viewData->error?></b>
    </div>
</div>
<div class="divCacGroup"></div>
<?php
} else {
?>
<div class="divCacGroup"></div>
<div id="error" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;background-color:red;border-width:2px">
    <div class="divCacGroup" style="text-align:center;">
        <b>
            Please be sure to apply at least one of the following optional settings, not applying any will generate an error...
            <div class="divCacGroup"></div>
            Saigon Standard Template or Nagios Host Template or Host Group or Service Checks
        </b>
    </div>
</div>
<div class="divCacGroup"></div>
<?php
}
?>
<div id="container" class="divCacGroup admin_box admin_box_blue admin_border_black" style="width:98%;border-width:2px;">
<form method="POST" action="action.php" name="ngnt_form">
<input type="hidden" value="ngnt" id="controller" name="controller" />
<input type="hidden" value="<?php echo $action?>" id="action" name="action" />
<input type="hidden" value="<?php echo $deployment?>" id="deployment" name="deployment" />
<input type="hidden" value="<?php echo $type?>" id="ngnttype" name="ngnttype" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Create Node Template for <?php echo $deployment?></th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
} else if ($action == 'copy_write') {
?>
        <th colspan="2">Copy Node Template <?php echo $nodeTemp?> in <?php echo $deployment?></th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:<br /><font size="2">Ex: redis-host-template</font></th>
        <td style="text-align:left;"><input type="text" value="<?php echo $nodeTemp?>" size="64" maxlength="128" id="nodeTemp" name="nodeTemp" <?php echo isset($modifyFlag)?'readonly':''?> /></td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Regex:</th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $nodeRegex?>" size="64" maxlength="512" id="nodeRegex" name="nodeRegex" />
            <input type="button" id="regexbutton" value="View Matches" onClick="checkHostRegex()" />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Negate Host Regex:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $nodeNegateRegex?>" size="64" maxlength="512" id="nodeNegateRegex" name="nodeNegateRegex" />
        </td>
    </tr><tr>
        <td colspan="2">
            <div id="regex-examples" class="parentClass divCacResponse" style="text-align:left;">
                <img src="static/imgs/plusSign.gif">
                Regex Examples
            </div>
            <div class="parent-desc-regex-examples divHide admin_box admin_box_blue admin_border_black divCacGroup" style="text-align:left;border-width:2px;">
                <table class="noderesults">
                    <tr><th colspan="1" style="text-align:right;">host.*</th><td colspan="3" style="text-align:left;">Matches all hosts that start with s2hb-mw-mc</td></tr>
                    <tr><th colspan="1" style="text-align:right;">host\d{1,2}\.domain\.com</th><td colspan="3" style="text-align:left;">Matches mc machines that have at least 1 number, but a max of 2 specified on the host</td></tr>
                    <tr><th colspan="1" style="text-align:right;">hos(t|e)-sub-grp\d{1,3}\.domain\.com</th><td colspan="3" style="text-align:left;">Matches admin machines that start with two different prefixes have at least 1 number, but a max of 3 specified on the host</td></tr>
                    <tr><th colspan="1" style="text-align:right;">host-sub-grp(42|43|1|19)\.domain\.com</th><td colspan="3" style="text-align:left;">Matches only the 4 hosts specified between the (  ) markers</td></tr>
                    <tr><th colspan="4">Quick Reference for Regex Creation</th></tr>
                    <tr><th style="text-align:center;width:25%">[abc]</th><td style="text-align:left;width:25%">A single character: a, b or c</td><th style="text-align:center;width:25%">[^abc]</th><td style="text-align:left;width:25%">Any single character but a, b, or c</td></tr>
                    <tr><th style="text-align:center;width:25%">[a-z]</th><td style="text-align:left;width:25%">Any single character in the range a-z</td><th style="text-align:center;width:25%">[a-zA-Z]</th><td style="text-align:left;width:25%">Any single character in the range a-z or A-Z</td></tr>
                    <tr><th style="text-align:center;width:25%">^</th><td style="text-align:left;width:25%">Start of line</td><th style="text-align:center;width:25%">$</th><td style="text-align:left;width:25%">End of line</td></tr>
                    <tr><th style="text-align:center;width:25%">\A</th><td style="text-align:left;width:25%">Start of string</td><th style="text-align:center;width:25%">\z</th><td style="text-align:left;width:25%">End of string</td></tr>
                    <tr><th style="text-align:center;width:25%">.</th><td style="text-align:left;width:25%">Any single character</td><th style="text-align:center;width:25%">\b</th><td style="text-align:left;width:25%">Any word boundary character</td></tr>
                    <tr><th style="text-align:center;width:25%">\s</th><td style="text-align:left;width:25%">Any whitespace character</td><th style="text-align:center;width:25%">\S</th><td style="text-align:left;width:25%">Any non-whitespace character</td></tr>
                    <tr><th style="text-align:center;width:25%">\d</th><td style="text-align:left;width:25%">Any digit</td><th style="text-align:center;width:25%">\D</th><td style="text-align:left;width:25%">Any non-digit</td></tr>
                    <tr><th style="text-align:center;width:25%">\w</th><td style="text-align:left;width:25%">Any word character (letter, number, underscore)</td><th style="text-align:center;width:25%">\W</th><td style="text-align:left;width:25%">Any non-word character</td></tr>
                    <tr><th style="text-align:center;width:25%">(...)</th><td style="text-align:left;width:25%">Capture everything enclosed</td><th style="text-align:center;width:25%">(a|b)</th><td style="text-align:left;width:25%">a or b</td></tr>
                    <tr><th style="text-align:center;width:25%">a?</th><td style="text-align:left;width:25%">Zero or one of a</td><th style="text-align:center;width:25%">a*</th><td style="text-align:left;width:25%">Zero or more of a</td></tr>
                    <tr><th style="text-align:center;width:25%">a+</th><td style="text-align:left;width:25%">One or more of a</td><th style="text-align:center;width:25%">a{3}</th><td style="text-align:left;width:25%">Exactly 3 of a</td></tr>
                    <tr><th style="text-align:center;width:25%">a{3,}</th><td style="text-align:left;width:25%">3 or more of a</td><th style="text-align:center;width:25%">a{3,6}</th><td style="text-align:left;width:25%">Between 3 and 6 of a</td></tr>
                </table>
            </div>
        </td>
    </tr>
<?php
if (!empty($viewData->stdtemplates)) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Saigon Standard Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="stdtemplate" name="stdtemplate" multiple="multiple">
                <option value="">Null / No Standard Template</option>
<?php
    asort($viewData->stdtemplates);
    foreach ($viewData->stdtemplates as $key => $stdtemplate) {
        if ($stdtemplate == $nodeStdHostTemplate) {
?>
                <option value="<?php echo $stdtemplate?>" selected><?php echo $stdtemplate?></option>
<?php
        } else {
?>
                <option value="<?php echo $stdtemplate?>"><?php echo $stdtemplate?></option>
<?php
        }
    }
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Negate Saigon Standard Template<br />Service Checks:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="nservices" name="nservices[]" multiple="multiple">
<?php
    foreach ($viewData->services as $svcCmd => $svcArray) {
        if ((is_array($nodeNServices)) && (in_array($svcCmd,$nodeNServices))) {
?>
                <option value="<?php echo $svcCmd?>" selected><?php echo $svcCmd?></option>
<?php
        } else {
?>
                <option value="<?php echo $svcCmd?>"><?php echo $svcCmd?></option>
<?php
        }
    }
?>
            </select>
        </td>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Nagios Host Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hosttemplate" name="hosttemplate" multiple="multiple">
                <option value="">Null / No Host Template</option>
<?php
asort($viewData->hosttemplates);
foreach ($viewData->hosttemplates as $hosttemplate => $htArray) {
    if ($hosttemplate == $nodeHostTemplate) {
?>
                <option value="<?php echo $hosttemplate?>" selected><?php echo $hosttemplate?></option>
<?php
    } else {
?>
                <option value="<?php echo $hosttemplate?>"><?php echo $hosttemplate?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Host Group:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hostgroup" name="hostgroup" multiple="multiple">
                <option value="">Null / No Host Group</option>
<?php
asort($viewData->hostgroups);
foreach ($viewData->hostgroups as $hostgroup => $hgArray) {
    if ($hostgroup == $nodeHostGroup) {
?>
                <option value="<?php echo $hostgroup?>" selected><?php echo $hostgroup?></option>
<?php
    } else {
?>
                <option value="<?php echo $hostgroup?>"><?php echo $hostgroup?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Service Checks:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="services" name="services[]" multiple="multiple">
<?php
foreach ($viewData->services as $svcCmd => $svcArray) {
    if ((is_array($nodeServices)) && (in_array($svcCmd,$nodeServices))) {
?>
                <option value="<?php echo $svcCmd?>" selected><?php echo $svcCmd?></option>
<?php
    } else {
?>
                <option value="<?php echo $svcCmd?>"><?php echo $svcCmd?></option>
<?php
    }
}
?>
            </select>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Sub-Deployment Association:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="subdeployment" name="subdeployment" multiple="multiple">
<?php
if ($nodeSubDeployment == 'N/A') {
?>
                <option value="N/A" selected>Not Applicable</option>
<?php
} else {
?>
                <option value="N/A">Not Applicable</option>
<?php
}
$subdeployments = preg_split('/\s?,\s?/', SUBDEPLOYMENT_TYPES);
foreach ($subdeployments as $subdeployment) {
    if ($nodeSubDeployment == $subdeployment) {
?>
                <option value="<?php echo $subdeployment?>" selected><?php echo $subdeployment?></option>
<?php
    } else {
?>
                <option value="<?php echo $subdeployment?>"><?php echo $subdeployment?></option>
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
<div class="divCacGroup"></div>
<div id="results"></div>
</div>

<?php

require HTML_FOOTER;
