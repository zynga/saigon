<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

if ((!isset($viewData->nagiosdata)) || (empty($viewData->nagiosdata))) {
    header('HTTP/1.0 400 No Data Returned');
} else {
    print json_encode($viewData->nagiosdata);
}
