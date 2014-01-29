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
<body>
<div id="container">
<table class="noderesults">
    <thead>
        <th colspan="6">Matched Results</th>
    </thead>
    <tr>
<?php
$i = 0;
foreach ($viewData as $host) {
?>
        <td><?php echo $host?></td>  
<?php
    $i++;
    if ($i>5) {
        $i = 0;
?>
    </tr><tr>
<?php
    }
}
?>
    </tr>
</table>
</div>

<?php

require HTML_FOOTER;
