<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;

function is_checked($var, $check) {
    if ($var == $check) return "checked";
    return "";
}

function genKey() {
    return substr(hash("sha1", file_get_contents("/dev/urandom", 0, null, -1, 16)), 0, 16);
}

$action = isset($viewData->action)?$viewData->action:'';
if ($action == 'modify_write') {
    $modifyFlag = true;
}

if (isset($viewData->deployInfo['name'])) {
    $deployment = $viewData->deployInfo['name'];
    $builddeployment = false;
} else {
    $deployment = '';
    $builddeployment = $_SESSION['deployment'];
}
$deployDesc = isset($viewData->deployInfo['desc'])?$viewData->deployInfo['desc']:'';
$deployAuthGrps = isset($viewData->deployInfo['authgroups'])?preg_replace('/,\s?/', ', ', $viewData->deployInfo['authgroups']):'';
$deployAuthGrpTitle = isset($viewData->authtitle)?$viewData->authtitle:'Unknown Group Authorization:';
$deployNagiosHead = isset($viewData->deployInfo['nagioshead'])?$viewData->deployInfo['nagioshead']:'';
$deployNagiosNegate = isset($viewData->deployInfo['deploynegate'])?$viewData->deployInfo['deploynegate']:'';
$deployType = (isset($viewData->deployInfo['type']) && !empty($viewData->deployInfo['type']))?$viewData->deployInfo['type']:false;
$deployRev = (isset($viewData->deployInfo['revision']) && !empty($viewData->deployInfo['revision']))?$viewData->deployInfo['revision']:false;
$deployNextRev = (isset($viewData->deployInfo['nextrevision']) && !empty($viewData->deployInfo['nextrevision']))?$viewData->deployInfo['nextrevision']:false;
$deployAliasTemp = isset($viewData->deployInfo['aliastemplate'])?$viewData->deployInfo['aliastemplate']:'host-dc';
$deployEnSharding = isset($viewData->deployInfo['ensharding'])?$viewData->deployInfo['ensharding']:'off';
$deployShardKey = isset($viewData->deployInfo['shardkey'])?$viewData->deployInfo['shardkey']:genKey();
$deployShardCount = isset($viewData->deployInfo['shardcount'])?$viewData->deployInfo['shardcount']:'1';
$deployStyle = isset($viewData->deployInfo['deploystyle'])?$viewData->deployInfo['deploystyle']:'both';
$deployCRepo = isset($viewData->deployInfo['commonrepo'])?$viewData->deployInfo['commonrepo']:'common';

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<script type="text/javascript">
var searchList = new Array(<?php echo count($viewData->locations)?>)
searchList["empty"] = ["Select a Parameter"];
<?php
foreach ($viewData->locations as $srchLoc => $slArray) {
    $keysArray = array_values($slArray);
?>
searchList["<?php echo $srchLoc?>"] = ["Select a Parameter", "<?php echo implode('", "', $keysArray)?>"];
<?php
}
?>
function srchParamChange(selectObj) {
    var idx = selectObj.selectedIndex;
    var loc = selectObj.options[idx].value;
    sList = searchList[loc];
    var lSelect = document.getElementById("srchparam");
    while (lSelect.options.length > 0) {
        lSelect.remove(0);
    }
    for (var i=0; i<sList.length; i++) {
        newOption = document.createElement("option");
        newOption.value = sList[i];
        newOption.text = sList[i];
        try {
            lSelect.add(newOption);
        } catch (e) {
            lSelect.appendChild(newOption);
        }
    }
}
</script>
<script type="text/javascript">
function insertSearchDefinition() {
    var loc = $('#location').val();
    var param = $('#srchparam').val();
    var subdeploy = $('#subdeployment').val();
    var note = $('#srchnote').val();
<?php
if ($builddeployment !== false) {
?>
    var deployment = '<?php echo $builddeployment?>';
<?php
} else {
?>
    var deployment = $('#deployment').val();
<?php
}
?>
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=deployment&action=add_hostSearch&loc=' + encodeURIComponent(loc) 
            + '&param=' + encodeURIComponent(param) + '&subdeploy=' + encodeURIComponent(subdeploy)
            + '&note=' + encodeURIComponent(note) + '&deployment=' + encodeURIComponent(deployment),
        dataType: 'html',
        success: function( data ) {
            $('#searchDefinitions').attr('src', $('#searchDefinitions').attr('src'));
            $('#srchnote').val("");
            $('#location').val("empty").change().multiselect('refresh');
        }
    });
}
</script>
<script type="text/javascript">
function insertGPRDefinition() {
    var loc = $('#gpr').val();
    var param = $('#gprparam').val();
    var subdeploy = $('#subdeployment').val();
    var note = $('#gprnote').val();
<?php
if ($builddeployment !== false) {
?>
    var deployment = '<?php echo $builddeployment?>';
<?php
} else {
?>
    var deployment = $('#deployment').val();
<?php
}
?>
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=deployment&action=add_hostSearch&loc=' + encodeURIComponent(loc) 
            + '&param=' + encodeURIComponent(param) + '&subdeploy=' + encodeURIComponent(subdeploy)
            + '&note=' + encodeURIComponent(note) + '&deployment=' + encodeURIComponent(deployment),
        dataType: 'html',
        success: function( data ) {
            $('#searchDefinitions').attr('src', $('#searchDefinitions').attr('src'));
            $('#gprnote').val("");
            $('#gprparam').val("");
            $('#gpr').val("empty").change().multiselect('refresh');
        }
    });
}
</script>
<script type="text/javascript">
function insertHostDefinition() {
    var host = $('#statichost').val();
    var ip = $('#staticip').val();
    var subdeploy = $('#subdeployment').val();
<?php
if ($builddeployment !== false) {
?>
    var deployment = '<?php echo $builddeployment?>';
<?php
} else {
?>
    var deployment = $('#deployment').val();
<?php
}
?>
    $.ajax({
        url: 'action.php',
        type: 'POST',
        data: 'controller=deployment&action=add_static_host&host=' + encodeURIComponent(host)
            + '&ip=' + encodeURIComponent(ip) + '&subdeploy=' + encodeURIComponent(subdeploy)
            + '&deployment=' + encodeURIComponent(deployment),
        dataType: 'html',
        success: function( data ) {
            $('#staticsearchDefinitions').attr('src', $('#staticsearchDefinitions').attr('src'));
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
<script type="text/javascript">
$(function() {
$("#commonrepo")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Common Repo",
        multiple: false,
    }).multiselectfilter(),
$("#gpr")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select a Location",
        multiple: false,
    }).multiselectfilter(),
$("#location")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select a Location",
        multiple: false,
    }).multiselectfilter(),
$("#subdeployment")
    .multiselect({
        selectedList: 1,
        noneSelectedText: "Select Optional Subdeployment",
        multiple: false,
    }).multiselectfilter();
});
</script>
<script type="text/javascript">
function checkHostRegex() {
    $('#regexbutton').attr('disabled','disabled');
    $('#regexbutton').addClass('grey');
    $('#results').empty();
    var sData = {};
    sData['controller'] = "deployment";
    sData['action'] = "view_dynamic_matches";
    sData['deployment'] = $('#deployment').val();
    sData['nregex'] = $('#deploynegate').val();
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
<div id="action-deployment" style="border-width:2px;width:97%;left:5;top:45;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
} else {
?>
<div id="action-deployment" style="border-width:2px;width:97%;left:5;top:5;position:absolute" class="admin_box_blue divCacGroup admin_border_black">
<?php
}
?>
<form method="post" action="action.php" name="deployment_action_write">
<input type="hidden" id="controller" name="controller" value="deployment" />
<input type="hidden" id="action" name="action" value="<?php echo $action?>" />
<table class="noderesults">
    <thead>
<?php
if ($action == 'add_write') {
?>
        <th colspan="2">Add Deployment</th>
<?php
} else if ($action == 'modify_write') {
?>
        <th colspan="2">Modify Deployment</th>
<?php
}
?>
    </thead>
    <tr>
        <th style="width:30%;text-align:right;">Name:</th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $deployment?>" size="32" maxlength="128" id="deployment" name="deployment" <?php echo isset($modifyFlag)?'readonly':''?> />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Description:</th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $deployDesc?>" size="64" maxlength="512" id="deploydesc" name="deploydesc" <?php echo isset($viewData->notsupermen)?'readonly':''?> />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;"><?php echo $deployAuthGrpTitle?></th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $deployAuthGrps?>" size="64" maxlength="512" id="deployauthgroups" name="deployauthgroups" <?php echo isset($viewData->notsupermen)?'readonly':''?> />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Nagios Head <font size="2">(eth0 ip)</font>:</th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $deployNagiosHead?>" size="16" maxlength="15" id="deployhead" name="deployhead" <?php echo isset($viewData->notsupermen)?'readonly':''?> />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Global Negate Regex:</th>
        <td style="text-align:left;">
            <input type="text" value="<?php echo $deployNagiosNegate?>" size="64" maxlength="512" id="deploynegate" name="deploynegate" />
            <input type="button" id="regexbutton" value="View Matches" onClick="checkHostRegex()" />
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Type:</th>
        <td style="text-align:left;"><input type="hidden" id="deploytype" name="deploytype" value="<?php echo $deployType?>" />Revision Based</td>
    </tr>
<?php
if ($deployRev !== false) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Current Revision:</th>
        <td style="text-align:left;"><?php echo $deployRev?></td>
    </tr>
<?php
}
if ($deployNextRev !== false) {
?>
    <tr>
        <th style="width:30%;text-align:right;">Next Revision:</th>
        <td style="text-align:left;"><?php echo $deployNextRev?></td>
    </tr>
<?php
}
?>
    <tr>
        <th style="width:30%;text-align:right;">Deployment Style:</th>
        <td style="text-align:left;">
            <input type="radio" name="deploystyle" value="both" <?php echo is_checked($deployStyle, 'both')?>>Both (Nagios/NRPE)</input>
            <input type="radio" name="deploystyle" value="nagios" <?php echo is_checked($deployStyle, 'nagios')?>>Nagios Only</input>
            <input type="radio" name="deploystyle" value="nrpe" <?php echo is_checked($deployStyle, 'nrpe')?>>NRPE Only</input>
            <input type="radio" name="deploystyle" value="commonrepo" <?php echo is_checked($deployStyle, 'commonrepo')?>>Common Repo</input>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Deployment Common Repo:</th>
        <td style="text-align:left;">
            <select id="commonrepo" name="commonrepo" multiple="multiple">
<?php
foreach ($viewData->crepos as $crepo) {
    if ($crepo == $deployment) continue;
    if ($crepo == $deployCRepo) {
?>
                <option value="<?php echo $crepo?>" selected><?php echo $crepo?></option>
<?php
    } else {
?>
                <option value="<?php echo $crepo?>"><?php echo $crepo?></option>
<?php
    }
}
?>
        </td>
    </tr><tr>
        <th style="width:30%;text-align:right;">Alias Template Style:</th>
        <td style="text-align:left;">
            <input type="radio" name="aliastemplate" value="host-dc" <?php echo is_checked($deployAliasTemp, 'host-dc')?>>host-dc</input>
            <input type="radio" name="aliastemplate" value="host" <?php echo is_checked($deployAliasTemp, 'host')?>>host</input>
        </td>
    </tr><tr>
        <td colspan="2">
            <div class="parentClass divCacGroup" id="shard" style="text-align:left;text-indent:25px;background-color:#91C5D4;border-radius:4px;">
                <img src="static/imgs/plusSign.gif">
                Deployment Cluster Sharding:
            </div>
            <div class="divHide parent-desc-shard">
                <table style="width:99%;">
                    <tr>
                        <th style="width:30%;text-align:right;">Enable Cluster Sharding:</th>
                        <td style="text-align:left;">
                            <input type="radio" name="ensharding" value="off" <?php echo is_checked($deployEnSharding, 'off')?>>Off</input>
                            <input type="radio" name="ensharding" value="on" <?php echo is_checked($deployEnSharding, 'on')?>>On</input>
                        </td>
                    </tr><tr>
                        <th style="width:30%;text-align:right;">Cluster Sharding Salt:</th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $deployShardKey?>" size="20" maxlength="20" id="shardkey" name="shardkey" readonly />
                        </td>
                    </tr><tr>
                        <th style="width:30%;text-align:right;">
                            Cluster Sharding Count:<br />
                            <font size="2">(Number of Clusters Participating in Deployment)</font>
                        </th>
                        <td style="text-align:left;">
                            <input type="text" value="<?php echo $deployShardCount?>" size="2" maxlength="2" id="shardcount" name="shardcount" <?php echo isset($viewData->notsupermen)?'readonly':''?> />
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
<!--<div class="divCacGroup"></div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
<div class="divCacGroup"></div>-->
<div class="divCacGroup admin_box_blue" id="hosts-define-encap">
    <table class="noderesults">
        <thead>
            <tr><th colspan="2">Add Host Information for Deployment:</th></tr>
        </thead>
        <tr>
            <th style="text-align:right;width:20%;">SubDeployment:<br /><font size="2">(Optional)</font></th>
            <td style="text-align:left;">
                <select id="subdeployment" name="subdeployment" multiple="multiple">
                    <option value="N/A" selected>Select Optional SubDeployment</option>
<?php
$subdeployments = preg_split('/\s?,\s?/', SUBDEPLOYMENT_TYPES);
foreach ($subdeployments as $subdeploy) {
?>
                    <option value="<?php echo $subdeploy?>"><?php echo $subdeploy?></option>
<?php
}
?>
                </select>
            </td>
        </tr>
    </table>
    <div class="divCacGroup"></div>
    <div id="dynamic-hosts" class="parentClass">
        <img src="static/imgs/plusSign.gif">
        Dynamic Host Source
    </div>
    <div class="parent-desc-dynamic-hosts divHide">
    <table class="noderesults">
    <thead>
        <tr><th colspan="4">Dynamic Host Input Locations:</th></tr>
    </thead>
    <tbody>
<?php
if (!empty($viewData->locations)) {
?>
        <tr>
            <th>
                Custom Input Location:
                <select id="location" name="location" onChange="srchParamChange(this);" multiple="multiple">
                    <option value="empty">Select a Location</option>
<?php
    foreach ($viewData->locations as $loc => $locArray) {
?>
                    <option value="<?php echo $loc?>"><?php echo $loc?></option>
<?php
    }
?>
                </select>
            </th><th>
                Search Param:
                <select id="srchparam" name="srchparam">
                    <label for="srchparam">Select a Parameter</label>
                    <option value="empty">Select a Parameter</option>
                </select>
            </th><th>
                Note:
                <input type="text" id="srchnote" name="srchnote" size="32" maxsize="64">
            </th><td>
                <input type="submit" value="Insert" style="font-size:14px;" onClick="insertSearchDefinition(); return false;">
            </td>
        </tr>
<?php
}
if (!empty($viewData->inputs)) {
?>
        <tr>
            <th>
                Glob/Prefix/Etc Input Location:
                <select id="gpr" name="gpr" multiple="multiple">
                    <option value="empty">Select a Location</option>
<?php
    foreach ($viewData->inputs as $key => $value) {
?>
                    <option value="<?php echo $key?>"><?php echo $value?></option>
<?php
    }
?>
                </select>
            </th><th>
                Glob/Prefix/Etc Param:
                <input type="text" id="gprparam" name="gprparam" size="32">
            </th><th>
                Note:
                <input type="text" id="gprnote" name="gprnote" size="32" maxsize="64">
            </th><td>
                <input type="submit" value="Insert" style="font-size:14px;" onClick="insertGPRDefinition(); return false;">
            </td>
        </tr>
<?php
}
?>
    </tbody>
    </table>
<?php
if ($builddeployment !== false) {
?>
    <iframe
        height="200px" name="searchDefinitions" id="searchDefinitions" style="min-height:100px;width:99%;left:5px;"
        src="action.php?controller=deployment&action=view_hostSearch&deployment=<?php echo $builddeployment?>" >
<?php
} else {
?>
    <iframe
        height="200px" name="searchDefinitions" id="searchDefinitions" style="min-height:100px;width:99%;left:5px;"
        src="action.php?controller=deployment&action=view_hostSearch&deployment=<?php echo $deployment?>" >
<?php
}
?>
    </iframe>
    </div>
    <div class="divCacGroup"></div>
    <div id="static-hosts" class="parentClass">
        <img src="static/imgs/plusSign.gif">
        Static Host Source
    </div>
    <div class="divHide parent-desc-static-hosts">
    <table class="noderesults">
    <thead>
        <tr><th colspan="3">Static Host Input:</th></tr>
    </thead>
    <tbody>
        <tr>
            <th>
                Input Host:
                <input type="text" id="statichost" name="statichost" value="" size="48" maxlength="128" />
            </th><th>
                Input IP:
                <input type="text" id="staticip" name="staticip" value="" size="32" maxlength="32" />
            </th><td>
                <input type="submit" value="Insert" style="font-size:14px;" onClick="insertHostDefinition(); return false;" />
            </td>
        </tr>
        <!--<tr>
            <form method="POST" action="action.php" enctype="multipart/form-data" target="staticsearchDefinitions">
            <input type="hidden" id="controller" name="controller" value="deployment" />
            <input type="hidden" id="action" name="action" value="add_static_host_csv" />
            <th colspan="2">
                Input CSV File:
                <input type="file" id="staticcsvfile" name="staticcsvfile" />
            </th><td>
                <input type="submit" value="Insert" style="font-size:14px;" />
            </td>
            </form>
        </tr>-->
    </tbody>
    </table>
<?php
if ($builddeployment !== false) {
?>
    <iframe
        height="200px" name="staticsearchDefinitions" id="staticsearchDefinitions" style="min-height:100px;width:99%;left:5px;"
        src="action.php?controller=deployment&action=view_static_hostSearch&deployment=<?php echo $builddeployment?>">
<?php
} else {
?>
    <iframe
        height="200px" name="staticsearchDefinitions" id="staticsearchDefinitions" style="min-height:100px;width:99%;left:5px;"
        src="action.php?controller=deployment&action=view_static_hostSearch&deployment=<?php echo $deployment?>">
<?php
}
?>
    </iframe>
    </div>
</div>
<div class="divCacGroup admin_box_blue" style="width:6%;">
    <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
</div>
</form>
<div class="divCacGroup"></div>
<div class="divCacGroup">
    <div id="results"></div>
</div>
</div>
<?php

require HTML_FOOTER;
