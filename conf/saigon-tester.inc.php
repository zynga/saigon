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
require_once(BASE_PATH.'/conf/version.inc.php');
/* Import Mode Info */
require_once(BASE_PATH.'/conf/role.inc.php');
/* Import Distribution Info */
require_once BASE_PATH.'/conf/dist.inc.php';
/* Global Information */
if (strtolower(MODE) == 'prod') {
    /* Define Debug Flag */
    define('DEBUG', false);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-tester.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
} else if (strtolower(MODE) == 'secure') {
    /* Define Debug Flag */
    define('DEBUG', false);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-tester.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
} else {
    /* Define Debug Flag */
    define('DEBUG', true);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-tester.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
}
/* Import log4php Library */
require_once(BASE_PATH.'/modules/log4php/Logger.php');
/* Define PID File */
define('PIDFILE', BASE_PATH.'/var/run/saigon-tester.pid');
/* Disable sharding capabilities */
define('SHARDING', false);
/* Code flag for consumer tripwires */
define('CONSUMER', true);
/* Remove hosts from configs which aren't classified by the node matrix mapper*/
define('SKIP_UNCLASSIFED_HOSTS', true);
/* Import Datastore Module Info */
require_once BASE_PATH.'/conf/datastoremodules.inc.php';
/* Import Host Module Auth Info */
require_once BASE_PATH.'/conf/hostmodules.inc.php';
/* Import Third Party Module Info */
require_once BASE_PATH.'/conf/thirdpartymodules.inc.php';

