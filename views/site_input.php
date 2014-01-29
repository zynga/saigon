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
    $("#deployment")
        .multiselect({
            selectedList: 1,
            noneSelectedText: "Select a Deployment",
            multiple: false,
        }).multiselectfilter();
});
</script>
<script type="text/javascript">
function getDeploymentMenu() {
    var deployment = $('#deployment').val();

    $.ajax({
        url: 'action.php',
        data: 'controller=core&action=menu&deployment=' + deployment,
        dataType: 'html',
        success: function( data ) {
            $('#encapsulate').html( data );
        }
    });
}
</script>
<body>
<div id="deployment-encapsulate" style="width:98%;height:45px;overflow:auto;position:absolute;top:5;left:5;">
    <div class="divCacGroup admin_box admin_box_blue admin_border_black">
    Deployment: <select id="deployment" name="deployment" onChange="getDeploymentMenu()">
    <option value="----" selected="selected">Select a Deployment</option>
<?php
if ((isset($viewData->deployments)) && (!empty($viewData->deployments))) {
    foreach ($viewData->deployments as $deployment) {
        if (($deployment == 'common') && ($viewData->superuser === true)) {
            print '<option value="'.$deployment.'">'.$deployment.'</option>'."\n";
        } else {
            print '<option value="'.$deployment.'">'.$deployment.'</option>'."\n";
        }
    }
}
?>
    </select>
    </div>
</div>
<!-- Break Point -->
<div id="encapsulate" style="width:98%;overflow:auto;position:absolute;top:49;left:5;">

</div>

<div>
<pre>
</pre>
</div>

<?php

require HTML_FOOTER;

