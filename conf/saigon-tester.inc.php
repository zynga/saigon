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
/* Global Information */
if (strtolower(MODE) == 'prod') {
    /* Redis Cluster Information */
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
    /* Define Debug Flag */
    define('DEBUG', false);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-tester.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
} else if (strtolower(MODE) == 'secure') {
    /* Redis Cluster Information */
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
    /* Define Debug Flag */
    define('DEBUG', false);
    /* Audit trail log file */
    define('AUDIT_LOG', '/var/log/saigon/saigon-tester.log');
    /* Saigon Data Fetch URL */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
} else {
    /* Redis Cluster Information */
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
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
/* Import Host Module Auth Info */
require_once BASE_PATH.'/conf/hostmodules.inc.php';

