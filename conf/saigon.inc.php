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
/* Saigon request url for API access */
define('SAIGONAPI_URL', 'http://127.0.0.1/api');
/* Audit trail log file for log4php library */
define('AUDIT_LOG', '/tmp/saigon-access.log');
/* Debug flag */
define('DEBUG', true);
/* Show Build NRPE RPM on Global Deployment Management Page */
define('BUILD_NRPERPM', false);
define('BEANSTALKD_SERVER', '127.0.0.1');
define('BEANSTALKD_TUBE', 'saigon-build');
define('GFS_ACCESS', '*'); // Every deployment
define('GFS_PLUGLOC', '/usr/lib/nagios/plugins/');
define('API_EVENT_SUBMISSION', 'inline');
/* Varnish Cache isn't exactly trustable yet (wip) */
define('VARNISH_CACHE_ENABLED', false);
define('VARNISH_CACHE_HOSTS', '127.0.0.1');
define('VARNISH_CACHE_HOSTNAME', 'localhost');
/* Authentication Module */
define('AUTH_MODULE', 'NoAuth'); // Currently has: LDAPAuth and NoAuth
define('SUPERMEN', 'ops'); // LDAP Group with complete control over system
/* Import log4php Library */
require_once BASE_PATH.'/modules/log4php/Logger.php';
/* MVC Definitions */
define('VIEW_DIRECTORY', BASE_PATH.'/views/');
define('HTML_HEADER', VIEW_DIRECTORY."header.php");
define('HTML_FOOTER', VIEW_DIRECTORY."footer.php");
define('MINIFIED_HTML_HEADER', VIEW_DIRECTORY."minified_header.php");
define('MINIFIED_REFRESH_HTML_HEADER', VIEW_DIRECTORY."minified_refresh_header.php");
/* Display / Activate Clustered Commands */
define('CLUSTER_COMMANDS', true);
define('CLUSTER_COMMANDS_URL', 'http://some.domain.com/lapi/rawquery');
/* Code flag for consumer tripwires */
define('CONSUMER', false);
/* Remove hosts from configs which aren't classified by the node matrix mapper*/
define('SKIP_UNCLASSIFED_HOSTS', true);
/* Import Datastore Module Info */
require_once BASE_PATH.'/conf/datastoremodules.inc.php';
/* Import Host Module Info */
require_once BASE_PATH.'/conf/hostmodules.inc.php';
/* Import Third Party Module Info */
require_once BASE_PATH.'/conf/thirdpartymodules.inc.php';
