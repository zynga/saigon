<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/** 
 * Requires for including of vars
 */
require_once dirname(dirname(__FILE__)).'/conf/saigon.inc.php';
?>
<html>
<head profile="http://www.w3.org/2005/10/profile">
<title>Saigon (<?php echo MODE ?>)</title>
</head>
<frameset rows="96,*" border='1'>

    <frame src="action.php?controller=core&action=getheader" name='head' noresize="noresize" />
    <frameset cols="375,*">
        <frame src='action.php?controller=core&action=input' name='input' noresize="noresize" />
        <frame name='output' noresize="noresize" />
    </frameset>
</frameset>
</html>
