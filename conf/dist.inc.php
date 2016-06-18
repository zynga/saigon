<?php
//
// Copyright (c) 2014, Pinterest
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/* Distribution Type and Directories */
define('DIST_TYPE', 'debian'); // rhel or debian
if (strtolower(DIST_TYPE) == 'debian') {
    define('NAGIOS_DIR', '/etc/nagios3');
    define('NAGIOS_MGDIR', '/etc/mod-gearman');
    define('NAGIOS_INCDIR', '/conf.d'); // appended to NAGIOS_DIR
    define('NAGIOS_BIN', '/usr/sbin/nagios3');
    define('NAGIOS_INIT', '/etc/init.d/nagios3');
}
else {
    define('NAGIOS_DIR', '/usr/local/nagios/etc');
    define('NAGIOS_MGDIR', '/usr/local/etc');
    define('NAGIOS_INCDIR', '/objects'); // appended to NAGIOS_DIR
    define('NAGIOS_BIN', '/usr/local/nagios/bin/nagios');
    define('NAGIOS_INIT', '/etc/init.d/nagios');
}
