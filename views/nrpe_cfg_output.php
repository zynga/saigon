<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;

?>
<link type="text/css" rel="stylesheet" href="static/css/tables.css" />
<link href="static/css/shCore.css" rel="stylesheet" type="text/css" />
<link href="static/css/shThemeDefault.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="static/js/shCore.js"></script>
<script type="text/javascript" src="static/js/brushes/shBrushPhp.js"></script>
<body>
<div class="divCacGroup admin_box_bg admin_box_blue admin_border_black" style="width:98%;overflow:scroll;">
<table class="noderesults">
    <tr>
        <th style="width:20%;">File Location:</th>
        <td style="text-align:left;"><?php echo $viewData->location?></td>
    </tr><tr>
        <th style="width:20%;">MD5 Sum of Contents:</th>
        <td style="text-align:left;"><?php echo $viewData->md5?></td>
    </tr><tr>
        <th colspan="2">Contents of File:</th>
    </tr><tr>
        <td colspan="2">
        <div id="filecontents">
            <pre class="brush: php; toolbar: false;" type="syntaxhighlighter">
<?php echo $viewData->msg?>
            </pre>
        </div>
        </td>
    </tr>
</table>
<div class="divCacGroup"></div>
<a href="action.php?controller=nrpecfg&action=stage&deployment=<?php echo $viewData->deployment?>" class="deployBtn">NRPE Configuration</a>
<div class="divCacGroup"></div>
</div>

<script type="text/javascript">
    SyntaxHighlighter.all()
</script>

<?php

require HTML_FOOTER;
