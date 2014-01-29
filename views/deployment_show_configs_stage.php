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
    $("#revision")
        .multiselect({
            beforeoptgrouptoggle: function(event, ui){
                return false;
            },
            optgrouptoggle: function(event, ui) {
                return false;
            },
            selectedList: 1,
            noneSelectedText: "Select a Revision",
            multiple: false,
        }).multiselectfilter(),
    $("#subdeployment")
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
            Please choose the Revision you would like to show...<br />
            <form method="post" action="action.php" name="deployment_show_configs">
                <input type="hidden" id="controller" name="controller" value="deployment" />
                <input type="hidden" id="action" name="action" value="show_configs" />
                <input type="hidden" id="deployment" name="deployment" value="<?php echo $viewData->deployment?>" />
                <select id="revision" name="revision" multiple="multiple">
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
                <div class="divCacGroup"></div>
                Please choose a Sub Deployment you would like to show...<br />
                <select id="subdeployment" name="subdeployment" multiple="multiple">
                    <option value="" selected>Select Optional SubDeployment</option>
<?php
$subdeployments = preg_split('/\s?,\s?/', SUBDEPLOYMENT_TYPES);
foreach ($subdeployments as $subdeploy) {
?>
                    <option value="<?php echo $subdeploy?>"><?php echo $subdeploy?></option>
<?php
}
?>
                </select>
                <div></div>
                <input type="submit" value="Submit" style="font-size:14px;padding:5px;" />
            </form>
        </div>
    </div>
</div>

<?php

require HTML_FOOTER;

