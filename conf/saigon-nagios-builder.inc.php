<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
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
    /* Import Deployment Definitions */
    include_once BASE_PATH.'/conf/deployment.inc.php';
    /* Define Debug Flag */
    define('DEBUG', false);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-nagios-builder.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
} else {
    /* Deployment Definitions */
    define('DEPLOYMENT', 'put-some-deployment-name-here');
    /* Define Debug Flag */
    define('DEBUG', true);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-nagios-builder.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'http://127.0.0.1:82/api');
}
/* Import log4php Library */
require_once BASE_PATH.'/modules/log4php/Logger.php';
/* Import Sharding Information */
require_once BASE_PATH.'/conf/sharding.inc.php';
/* Misc Definitions */
define('PID_FILE', '/var/run/saigon-consumer.pid');
/* Code flag for consumer tripwires */
define('CONSUMER', true);
/* Remove hosts from configs which aren't classified by the node matrix mapper*/
define('SKIP_UNCLASSIFED_HOSTS', true);
/* Import Host Module Auth Info */
require_once BASE_PATH.'/conf/hostmodules.inc.php';
/* Import Third Party Module Info */
require_once BASE_PATH.'/conf/thirdpartymodules.inc.php';

