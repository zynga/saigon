<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

?>
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html>
    <head>
        <title>Error 404: Not Found</title>
        <style type="text/css">
          html {background-color: #eee; font-family: sans;}
          body {background-color: #fff; border: 1px solid #ddd;
                padding: 15px; margin: 15px;}
          pre {background-color: #eee; border: 1px solid #ddd; padding: 5px;}
        </style>
    </head>
    <body>
        <h1>Error 404: Not Found</h1>
        <p>Sorry, the requested URL caused an error:</p>
        <pre><?php echo $viewData->error?></pre>
    </body>
</html>
