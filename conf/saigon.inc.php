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
/* Global Information */
if (strtolower(MODE) == 'prod') {
    /* Redis Cluster Information */
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
    /* Saigon request url for API access */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    /* Audit trail log file for log4php library */
    define('AUDIT_LOG', BASE_PATH.'/audit/saigon-access.log');
    /* Debug flag */
    define('DEBUG', false);
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
    define('GFS_ACCESS', 'group1,group2');
    define('GFS_PLUGLOC', '/some/path/');
    define('BUILD_NRPERPM', true);
} else if (strtolower(MODE) == 'secure') {
    /* Redis Cluster Information */
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
    /* Saigon request url for API access */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    /* Audit trail log file for log4php library */
    define('AUDIT_LOG', BASE_PATH.'/audit/saigon-access.log');
    /* Debug flag */
    define('DEBUG', false);
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
    define('GFS_ACCESS', 'group1,group2');
    define('GFS_PLUGLOC', '/some/path/');
    define('BUILD_NRPERPM', true);
} else {
    /* Redis Cluster Information */
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
    /* Saigon request url for API access */
    define('SAIGONAPI_URL', 'https://127.0.0.1/api');
    /* Audit trail log file for log4php library */
    define('AUDIT_LOG', '/tmp/saigon-access.log');
    /* Debug flag */
    define('DEBUG', true);
    define('BEANSTALKD_SERVER', '127.0.0.1');
    define('BEANSTALKD_TUBE', 'saigon-build');
    define('GFS_ACCESS', '*'); // Every deployment
    define('GFS_PLUGLOC', '/some/path/');
    define('BUILD_NRPERPM', false);
}
/* Import log4php Library */
require_once BASE_PATH.'/modules/log4php/Logger.php';
/* MVC Definitions */
define('VIEW_DIRECTORY', BASE_PATH.'/views/');
define('HTML_HEADER', VIEW_DIRECTORY."header.php");
define('HTML_FOOTER', VIEW_DIRECTORY."footer.php");
define('MINIFIED_HTML_HEADER', VIEW_DIRECTORY."minified_header.php");
define('MINIFIED_REFRESH_HTML_HEADER', VIEW_DIRECTORY."minified_refresh_header.php");
define('AUTH_MODULE', 'LDAPAuth'); // Currently has: LDAPAuth and NoAuth
define('SUPERMEN', 'control-group');
/* Define Host API Interface Modules */
define('DEPLOYMENT_MODULES', 'CMDBDatacenters,CMDBDeployments,CDCRouterVMs,AWSEC2');
define('INPUT_MODULES', 'CMDBDNGlobs,CMDBGlobs,CMDBRSDeployments,CMDBRSServerArrays,RightScale,AWSEC2');
define('SUBDEPLOYMENT_TYPES', 'staging,production');
/* Import Host Module Auth Info */
require_once BASE_PATH.'/conf/hostmodules.inc.php';

