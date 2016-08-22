<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//
 
require HTML_HEADER;
?>
<script type="text/javascript">
$(function() {
    $("#fromrev")
        .multiselect({
            beforeoptgrouptoggle: function(event, ui){
                return false;
            },
            optgrouptoggle: function(event, ui) {
                return false;
            },
            selectedList: 1,
            noneSelectedText: "Select From Revision",
            multiple: false,
        }).multiselectfilter(),
    $("#torev")
        .multiselect({
            beforeoptgrouptoggle: function(event, ui){
                return false;
            },
            optgrouptoggle: function(event, ui) {
                return false;
            },
            selectedList: 1,
            noneSelectedText: "Select To Revision",
            multiple: false,
        }).multiselectfilter(),
    $("#shard")
        .multiselect({
            selectedList: 1,
            multiple: false,
        }).multiselectfilter();
});
</script>
<body>
<div id="encapsulate" style="position:absolute;top:5;left:5;width:98%;">
    <div class="divCacGroup admin_box admin_box_blue admin_border_black">
        <b>Deployment:</b> <?php echo $viewData->deployment?><br />
        <div class="divCacGroup"></div>
        <div class="divCacResponse">
            Please choose the Revisions you would like to diff...<br />
            <form method="post" action="action.php" name="deployment_diff_configs">
                <input type="hidden" id="controller" name="controller" value="deployment" />
                <input type="hidden" id="action" name="action" value="diff_configs" />
                <input type="hidden" id="deployment" name="deployment" value="<?php echo $viewData->deployment?>" />
                <select id="fromrev" name="fromrev" multiple="multiple">
                    <optgroup label="Latest Revisions">
<?php
print '<option value="'.$viewData->revs['currrev'].'">Current Revision: '.$viewData->revs['currrev'].'</option>'."\n";
if ((isset($viewData->revs['prevrev'])) && (!empty($viewData->revs['prevrev']))
    && ($viewData->revs['currrev'] != $viewData->revs['prevrev']) && ($viewData->revs['prevrev'] != $viewData->revs['nextrev'])) {
    print '<option value="'.$viewData->revs['prevrev'].'">Previous Revision: '.$viewData->revs['prevrev'].'</option>'."\n";
}
?>
                    </optgroup>
<?php
$allrevs = array_reverse($viewData->allrevs);
if (!empty($allrevs)) {
?>
                    <optgroup label="Older Revisions">
<?php
    foreach ($allrevs as $rev) {
        if (($rev != $viewData->revs['prevrev']) && ($rev != $viewData->revs['nextrev']) &&
            ($rev != $viewData->revs['currrev'])) {
            print '<option value="'.$rev.'">Revision: '.$rev.'</option>'."\n";
        }
    }
?>
                    </optgroup>    
<?php
}
?>
                </select>
                <select id="torev" name="torev" multiple="multiple">
                    <optgroup label="Latest Revisions">
<?php
if ((isset($viewData->revs['nextrev'])) && (!empty($viewData->revs['nextrev'])) && ($viewData->revs['currrev'] != $viewData->revs['nextrev'])) {
    print '<option value="'.$viewData->revs['nextrev'].'">Next Revision: '.$viewData->revs['nextrev'].'</option>'."\n";
}
print '<option value="'.$viewData->revs['currrev'].'">Current Revision: '.$viewData->revs['currrev'].'</option>'."\n";
if ((isset($viewData->revs['prevrev'])) && (!empty($viewData->revs['prevrev']))
    && ($viewData->revs['currrev'] != $viewData->revs['prevrev']) && ($viewData->revs['prevrev'] != $viewData->revs['nextrev'])) {
    print '<option value="'.$viewData->revs['prevrev'].'">Previous Revision: '.$viewData->revs['prevrev'].'</option>'."\n";
}
?>
                    </optgroup>
<?php
$allrevs = array_reverse($viewData->allrevs);
if (!empty($allrevs)) {
?>
                    <optgroup label="Older Revisions">
<?php
    foreach ($allrevs as $rev) {
        if (($rev != $viewData->revs['prevrev']) && ($rev != $viewData->revs['nextrev']) &&
            ($rev != $viewData->revs['currrev'])) {
            print '<option value="'.$rev.'">Revision: '.$rev.'</option>'."\n";
        }
    }
?>
                    </optgroup>    
<?php
}
?>
                </select>
<?php
if ((isset($viewData->deploymentinfo['ensharding'])) && ($viewData->deploymentinfo['ensharding'] == 'on')) {
?>
                <div class="divCacGroup"></div>
                Please choose a Shard you would like to diff...<br />
                <select id="shard" name="shard" multiple="multiple">
                    <option value="" selected>Select Optional Shard</option>
<?php
    for ($i=1;$i<=$viewData->deploymentinfo['shardcount'];$i++) {
?>
                    <option value="<?php echo $i?>">Shard <?php echo $i?></option>
<?php
    }
?>
                </select>
<?php
}
?>
                <div class="divCacGroup"></div>
                <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
            </form>
        </div>
    </div>
</div>

<?php

require HTML_FOOTER;

