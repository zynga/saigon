<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/* Header file
 *  Reasons for being a php file, is in case we wanted to do something in the header.
 */

?>
<html>
<head>
<link type='text/css' rel='stylesheet' href='static/css/admin.css'>
<style>
ul { list-style-type:none;margin:0;padding:0;overflow:hidden; }
li { float:left;padding:1; }
a:link,a:visited { display:block;width:145px;font-weight:bold;color:#000000;background-color:#66a9bd;text-align:center;padding:4px;text-decoration:none;border-radius:6px;border-width:2px;border-style:solid;border-color:#000000; }
a:hover,a:active { background-color:#719ba7; }
</style>
</head>
<script type="text/javascript" src="static/js/jquery.js"></script>
<div class="divCacGroup admin_box_blue admin_border_black" style="height:62px;padding-left:50px;">
<font size="5"><b>Saigon</b></font>
<div class="divCacGroup" style="padding-left:50px;">
Centralized Nagios Configuration Management : v<b><?php echo VERSION ?></b>
</div>
</div>
</html>

<?php

