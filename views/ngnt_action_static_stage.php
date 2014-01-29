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
$nodes = isset($viewData->nodeInfo['selhosts'])?explode(',', $viewData->nodeInfo['selhosts']):array();
$nodeServices = isset($viewData->nodeInfo['services'])?$viewData->nodeInfo['services']:array();
$nodeNServices = isset($viewData->nodeInfo['nservices'])?$viewData->nodeInfo['nservices']:array();
$nodeHostGroup = isset($viewData->nodeInfo['hostgroup'])?$viewData->nodeInfo['hostgroup']:'';
$nodeHostTemplate = isset($viewData->nodeInfo['hosttemplate'])?$viewData->nodeInfo['hosttemplate']:'';
$nodeStdHostTemplate = isset($viewData->nodeInfo['stdtemplate'])?$viewData->nodeInfo['stdtemplate']:'';
$nodeSubDeployment = isset($viewData->nodeInfo['subdeployment'])?$viewData->nodeInfo['subdeployment']:'N/A';
if (empty($viewData->services)) {
    $viewData->error = 'Unable to detect available services to apply to nodes';
}
if (count($viewData->hosts) < 5000) {
?>
<script type="text/javascript" src="static/js/jquery.dataTables.js"></script>
<?php
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
    }).multiselectfilter();
$("#nservices")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Negate Services",
    }).multiselectfilter();
});
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
<script type="text/javascript">
$(document).ready(function() {
    var oTable = $("#hosts");
    oTable.dataTable({
        "sScrollY": "400px",
        "bPaginate": false,
    } );

    $('#checkAllAuto').click( function() {
        $("INPUT[type='checkbox']").attr('checked', $('#checkAllAuto').is(':checked'));
    });

    $('#submitter').click( function() {
        var url = 'action.php?controller=ngnt';
        var sData = {};
        sData['deployment'] = "<?php echo $deployment?>";
        sData['action'] = "<?php echo $action?>";
        sData['ngnttype'] = "<?php echo $type?>";
        sData['nodeTemp'] = $('#nodeTemp').val();
        if ($('#hosttemplate').val() != null) {
            sData['hosttemplate'] = $('#hosttemplate').val()[0];
        }
        if ($('#stdtemplate').val() != null) {
            sData['stdtemplate'] = $('#stdtemplate').val()[0];
        }
        if ($('#hostgroup').val() != null) {
            sData['hostgroup'] = $('#hostgroup').val()[0];
        }
        if ($('#services').val() != null) {
            sData['services'] = $('#services').val();
        }
        var inputs = oTable.$('input:checked');
        var ipData = new Array();
        for (var i in inputs) {
            if (inputs[i].value == 1) {
                ipData.push(inputs[i].id);
            }
        }
        sData['selhosts'] = ipData.toString();
        $.ajax({
            url: url,
            type: "POST",
            data: sData,
            dataType: 'html',
            success: function( data ) {
                $('#error').hide(),
                $('#container').hide(),
                $('#results').html( data );
            }
        });

    } );

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
        <th style="width:30%;text-align:right;">Host Template:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hosttemplate" name="hosttemplate" multiple="multiple">
                <option value="">Null / No HostTemplate</option>
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
        <th style="width:30%;text-align:right;">HostGroup:<br /><font size="2">(Optional)</font></th>
        <td style="text-align:left;">
            <select id="hostgroup" name="hostgroup" multiple="multiple">
                <option value="">Null / No HostGroup</option>
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
    if (in_array($svcCmd,$nodeServices)) {
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
<div id="host_results" style="padding:10px;">
    <div id="header">
        <div style="text-align:left;font-size:1.1em;background-color:#719BA7;border-color:#719BA7;" class="admin_box divCacGroup admin_border_black">
            Hosts:
            <div style="text-align:right">
                <input type="checkbox" name="checkAllAuto" id="checkAllAuto"> Select All / Deselect All
            </div>
        </div>
    </div>
    <div id="hosts_table">
    <table id="hosts" class="noderesults">
        <thead>
            <th>Hostname</th>
            <th>Address</th>
            <th>Active</th>
        </thead>
        <tbody>
<?php
if ((isset($viewData->hosts)) && (!empty($viewData->hosts))) {
    foreach ($viewData->hosts as $host => $hostArray) {
        if (in_array($hostArray['address'], $nodes)) {
            $checked = "checked";
        } else {
            $checked = "";
        }
?>
            <tr>
                <td><?php echo $hostArray['host_name']?></td>
                <td><?php echo $hostArray['address']?></td>
                <td><input type="checkbox" name="<?php echo $host?>" id="<?php echo $hostArray['address']?>" value="1" <?php echo $checked?> /></td>
            </tr>
<?php
    }
}
?>
        </tbody>
    </table>
    </div>
</div>
<div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <button type="submit" id="submitter">Submit Data</button>
</div>
</div>
<div id="results"></div>

<?php

require HTML_FOOTER;
