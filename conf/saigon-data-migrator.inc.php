<?php
//
// Copyright (c) 2015, Pinterest
// https://github.com/mhwest13/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * Definitions
 **/

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(dirname(__FILE__)));
}

/* Import Version Info */
require_once BASE_PATH.'/conf/version.inc.php';
/* Import Mode Info */
require_once BASE_PATH.'/conf/role.inc.php';
/* Import Distribution Info */
require_once BASE_PATH.'/conf/dist.inc.php';
/* Global Information */
if (strtolower(MODE) == 'prod') {
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-data-migrator.log');
} else {
    /* Audit trail log file */
    define('AUDIT_LOG', '/tmp/saigon-data-migrator.log');
}
/* Define Debug Flag */
define('DEBUG', true);
/* Import log4php Library */
require_once BASE_PATH.'/modules/log4php/Logger.php';
/* Misc Definitions */
define('PID_FILE', '/var/run/saigon-data-migrator.pid');
/* Code flag for consumer tripwires */
define('CONSUMER', true);
define('AUTH_MODULE', 'ConsumerAuth');
/* Import Datastore Module Info */
require_once BASE_PATH.'/conf/datastoremodules.inc.php';
/* Import Host Module Auth Info */
require_once BASE_PATH.'/conf/hostmodules.inc.php';
/* Import Third Party Module Info */
require_once BASE_PATH.'/conf/thirdpartymodules.inc.php';

