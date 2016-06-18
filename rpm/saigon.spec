Name:          saigon
Version:       1.1
Release:       0%{?dist}
Summary:       Saigon : Centralized Nagios Configuration System
Group:         Applications/Systems
License:       BSD
Source:        %{name}-%{version}.tar.gz
Buildroot:     %{_tmppath}/%{name}-%{version}-root
BuildArch:     noarch

Requires: php => 5.3, php-cli, php-process

%define os_dir /opt/saigon
%define debug_package %{nil}

%description
Saigon is a product designed in house at Zynga to help configure
and deploy configurations to Nagios master nodes, and NRPE Clients
so configs can be managed centrally and built with best practices.

%package webconfig
Summary:    Saigon UI / API Configuration Files
Group:      Applications/Systems/Interfaces
Requires:   %{name} = %{version}-%{release}, pkgconfig

%description webconfig
This provides the common web configuration files for the UI and / or API

%package ui
Summary:    Saigon Configuration User Interface
Group:      Applications/Systems/Interfaces
Requires:   %{name} = %{version}-%{release}, pkgconfig
Requires:   httpd, nagios, php-redis, php-ldap, nginx
Requires:   %{name}-webconfig = %{version}-%{release}

%description ui
This provides the central ui for users to use to configure nagios installations
using their own, or common created plugins and checks. Also leveraged by API
based machines, however they have a flag enabled to prevent UI interactions.

Currently requires a Redis Data store to connect to.

%package api
Summary:    Saigon Configuration User Interface
Group:      Applications/Systems/Interfaces
Requires:   %{name} = %{version}-%{release}, pkgconfig
Requires:   httpd, nagios, php-redis, php-ldap, nginx
Requires:   php => 5.3
Requires:   %{name}-webconfig = %{version}-%{release}

%description api
This provides the updated Slim Framework based API, which provides a
RESTful interface for manipulating data in Saigon.

Currently requires a Redis Data store to connect to.

%package nagios-consumer
Summary:    Saigon Nagios Configuration Consumer
Group:      Applications/Systems/Consumers
Requires:   %{name} = %{version}-%{release}, pkgconfig

%description nagios-consumer
This provides the consumer which fetches the nagios configuration information
from the central ui / api, before making the  appropriate calls into the 
specified host store locations for the specified host information.

%package nrpe-consumer
AutoReq:    no
Summary:    Saigon NRPE Configuration Consumer
Group:      Applications/Systems/Consumers
Requires:   pkgconfig
Requires:   perl(Config::Auto), perl(Digest::MD5), perl(File::Copy), perl(IO::Interface::Simple)
Requires:   perl(JSON), perl(LWP::UserAgent), perl(MIME::Base64), perl(strict), perl(warnings)
Requires:   perl(LWP::Protocol::https)

%description nrpe-consumer
This provides the consumer which fetches the nagios nrpe config files for the
deployments which have placed a .cfg file in the include directory.

%package modgearman-consumer
AutoReq:    no
Summary:    Saigon Mod-Gearman Configuration Consumer
Group:      Application/Systems/Consumers
Requires:   pkgconfig
Requires:   perl(Config::Auto), perl(Digest::MD5), perl(File::Copy), perl(IO::Interface::Simple)
Requires:   perl(JSON), perl(LWP::UserAgent), perl(MIME::Base64), perl(strict), perl(warnings)
Requires:   perl(LWP::Protocol::https)

%description modgearman-consumer
This provides the consumer which fetches the nagios Mod-Gearman configuration
file and ensures that the service is restarted properly.

%package nagiosplugin-consumer
AutoReq:    no
Summary:    Saigon Nagios Plugin Consumer
Group:      Applications/Systems/Consumers
Requires:   pkgconfig
Requires:   perl(Config::Auto), perl(Digest::MD5), perl(File::Copy), perl(IO::Interface::Simple)
Requires:   perl(JSON), perl(LWP::UserAgent), perl(MIME::Base64), perl(strict), perl(warnings)
Requires:   perl(LWP::Protocol::https)

%description nagiosplugin-consumer
This provides the consumer which fetches the nagios custom hosted plugins
for the deployments which have placed a .ini file in the include directory.

%package nagiostest-consumer
Summary:    Saigon Nagios Configuration Test Consumer
Group:      Applications/Systems/Consumers
Requires:   %{name} = %{version}-%{release}, pkgconfig
Requires:   php-redis, nagios, monit

%description nagiostest-consumer
This provides the consumer used for building and testing Nagios configurations
for the various deployments. This consumer leverages beanstalkd as
a job queue mechanism, and can be run on multiple machines to help spread the load.

%package nrpe-rpm-consumer
AutoReq:    no
Summary:    Saigon NRPE RPM Creation Consumer
Group:      Applications/Systems/Consumers
Requires:   pkgconfig
Requires:   perl(File::Copy), perl(Beanstalk::Client), perl(Getopt::Long)
Requires:   perl(JSON), perl(MIME::Base64), perl(strict), perl(warnings)

%description nrpe-rpm-consumer
This provides the consumer used for building nrpe rpm configuration packages
for the various deployments. This consumer leverages beanstalkd as a job queue.

%package events-submitter
AutoReq:    no
Summary:    Saigon Events Submitter
Group:      Applications/Systems/Consumers
Requires:   pkgconfig
Requires:   perl(Beanstalk::Client), perl(MCE), perl(Daemon::Control)
Requires:   perl(JSON), perl(strict), perl(warnings)

%description events-submitter
Simple script for submitting passive results to specified nagios servers in an asynchronous manner.
Main idea of this consumer is to offload event submission work to a pool of workers, rather than being done
in a synchronous manner from the apache child, this way the API can accept more events in less time.

%prep
%setup -q

%build

%install
%{__rm} -rf %{buildroot}
%{__mkdir} -p %{buildroot}%{_sysconfdir}/logrotate.d
%{__mkdir} -p %{buildroot}/var/log/saigon
%{__mkdir} -p %{buildroot}%{os_dir}/{api,audit,conf,controllers,include,log,lib,misc,renderers,routes,views,vendor}
%{__mkdir} -p %{buildroot}%{os_dir}/var/run
%{__mkdir} -p %{buildroot}%{os_dir}/consumer/{saigon-nrpe-builder.d,saigon-nagiosplugin-builder.d}
%{__mkdir} -p %{buildroot}%{os_dir}/modules/AuthInterfaces/
%{__mkdir} -p %{buildroot}%{os_dir}/modules/HostInterfaces/CDC/
%{__mkdir} -p %{buildroot}%{os_dir}/modules/HostInterfaces/ec2/{executors,include,locations}
%{__mkdir} -p %{buildroot}%{os_dir}/modules/HostInterfaces/CMDB/{executors,include,locations}
%{__mkdir} -p %{buildroot}%{os_dir}/modules/HostInterfaces/zsb/{executors,include,locations}
%{__mkdir} -p %{buildroot}%{os_dir}/modules/HostInterfaces/rs/{executors,include,locations}
%{__mkdir} -p %{buildroot}%{os_dir}/modules/log4php/{xml,layouts,appenders,helpers,configurators,renderers,filters}
%{__mkdir} -p %{buildroot}%{os_dir}/modules/phpdiff/Diff/Renderer/{Html,Text}
%{__mkdir} -p %{buildroot}%{os_dir}/www/static/css
%{__mkdir} -p %{buildroot}%{os_dir}/www/static/imgs/jquery-ui
%{__mkdir} -p %{buildroot}%{os_dir}/www/static/js/brushes

%{__install} -Dp -m0644 conf/*.inc.php %{buildroot}%{os_dir}/conf/
%{__install} -Dp -m0644 conf/saigon-nrpe-builder.ini %{buildroot}%{os_dir}/conf/saigon-nrpe-builder.ini
%{__install} -Dp -m0644 conf/saigon-nagiosplugin-builder.ini %{buildroot}%{os_dir}/conf/saigon-nagiosplugin-builder.ini
%{__install} -Dp -m0644 conf/saigon-modgearman-builder.ini %{buildroot}%{os_dir}/conf/saigon-modgearman-builder.ini
%{__install} -Dp -m0644 conf/cdc_creds.ini %{buildroot}%{os_dir}/conf/cdc_creds.ini

%{__install} -Dp -m0755 consumer/saigon-nagios-builder %{buildroot}%{os_dir}/consumer/saigon-nagios-builder
%{__install} -Dp -m0755 consumer/saigon-nrpe-builder %{buildroot}%{os_dir}/consumer/saigon-nrpe-builder
%{__install} -Dp -m0755 consumer/saigon-modgearman-builder %{buildroot}%{os_dir}/consumer/saigon-modgearman-builder
%{__install} -Dp -m0755 consumer/saigon-nagiosplugin-builder %{buildroot}%{os_dir}/consumer/saigon-nagiosplugin-builder
%{__install} -Dp -m0755 consumer/saigon-tester %{buildroot}%{os_dir}/consumer/saigon-tester
%{__install} -Dp -m0755 consumer/saigon-fetch-cdc-routervms %{buildroot}%{os_dir}/consumer/saigon-fetch-cdc-routervms
%{__install} -Dp -m0755 consumer/saigon-nrpe-rpm-builder %{buildroot}%{os_dir}/consumer/saigon-nrpe-rpm-builder
%{__install} -Dp -m0755 consumer/saigon-events-submitter %{buildroot}%{os_dir}/consumer/saigon-events-submitter
%{__install} -Dp -m0755 consumer/saigon-hostaudit %{buildroot}%{os_dir}/consumer/saigon-hostaudit

%{__install} -Dp -m0644 controllers/* %{buildroot}%{os_dir}/controllers/
%{__install} -Dp -m0644 include/*.php %{buildroot}%{os_dir}/include/

%{__install} -Dp -m0644 modules/*.php %{buildroot}%{os_dir}/modules/
%{__install} -Dp -m0644 modules/HostInterfaces/*.php %{buildroot}%{os_dir}/modules/HostInterfaces/
%{__install} -Dp -m0644 modules/HostInterfaces/CDC/*.php %{buildroot}%{os_dir}/modules/HostInterfaces/CDC/
%{__install} -Dp -m0644 modules/HostInterfaces/CMDB/executors/* %{buildroot}%{os_dir}/modules/HostInterfaces/CMDB/executors/
%{__install} -Dp -m0644 modules/HostInterfaces/CMDB/include/* %{buildroot}%{os_dir}/modules/HostInterfaces/CMDB/include/
%{__install} -Dp -m0644 modules/HostInterfaces/CMDB/locations/* %{buildroot}%{os_dir}/modules/HostInterfaces/CMDB/locations/
%{__install} -Dp -m0644 modules/HostInterfaces/zsb/executors/* %{buildroot}%{os_dir}/modules/HostInterfaces/zsb/executors/
%{__install} -Dp -m0644 modules/HostInterfaces/zsb/include/* %{buildroot}%{os_dir}/modules/HostInterfaces/zsb/include/
%{__install} -Dp -m0644 modules/HostInterfaces/zsb/locations/* %{buildroot}%{os_dir}/modules/HostInterfaces/zsb/locations/
%{__install} -Dp -m0644 modules/HostInterfaces/rs/executors/* %{buildroot}%{os_dir}/modules/HostInterfaces/rs/executors/
%{__install} -Dp -m0644 modules/HostInterfaces/rs/include/* %{buildroot}%{os_dir}/modules/HostInterfaces/rs/include/
%{__install} -Dp -m0644 modules/HostInterfaces/rs/locations/* %{buildroot}%{os_dir}/modules/HostInterfaces/rs/locations/
%{__install} -Dp -m0644 modules/HostInterfaces/ec2/*.php %{buildroot}%{os_dir}/modules/HostInterfaces/ec2/
%{__install} -Dp -m0644 modules/HostInterfaces/ec2/executors/* %{buildroot}%{os_dir}/modules/HostInterfaces/ec2/executors/
%{__install} -Dp -m0644 modules/HostInterfaces/ec2/include/* %{buildroot}%{os_dir}/modules/HostInterfaces/ec2/include/
%{__install} -Dp -m0644 modules/HostInterfaces/ec2/locations/* %{buildroot}%{os_dir}/modules/HostInterfaces/ec2/locations/
%{__install} -Dp -m0644 modules/log4php/*.php %{buildroot}%{os_dir}/modules/log4php/
%{__install} -Dp -m0644 modules/log4php/appenders/*.php %{buildroot}%{os_dir}/modules/log4php/appenders/
%{__install} -Dp -m0644 modules/log4php/configurators/*.php %{buildroot}%{os_dir}/modules/log4php/configurators/
%{__install} -Dp -m0644 modules/log4php/filters/*.php %{buildroot}%{os_dir}/modules/log4php/filters/
%{__install} -Dp -m0644 modules/log4php/helpers/*.php %{buildroot}%{os_dir}/modules/log4php/helpers/
%{__install} -Dp -m0644 modules/log4php/layouts/*.php %{buildroot}%{os_dir}/modules/log4php/layouts/
%{__install} -Dp -m0644 modules/log4php/renderers/*.php %{buildroot}%{os_dir}/modules/log4php/renderers/
%{__install} -Dp -m0644 modules/log4php/xml/* %{buildroot}%{os_dir}/modules/log4php/xml/
%{__install} -Dp -m0644 modules/phpdiff/*.php %{buildroot}%{os_dir}/modules/phpdiff/
%{__install} -Dp -m0644 modules/phpdiff/Diff/Renderer/*.php %{buildroot}%{os_dir}/modules/phpdiff/Diff/Renderer/
%{__install} -Dp -m0644 modules/phpdiff/Diff/Renderer/Html/*.php %{buildroot}%{os_dir}/modules/phpdiff/Diff/Renderer/Html/
%{__install} -Dp -m0644 modules/phpdiff/Diff/Renderer/Text/*.php %{buildroot}%{os_dir}/modules/phpdiff/Diff/Renderer/Text/
%{__install} -Dp -m0644 modules/phpdiff/Diff/*.php %{buildroot}%{os_dir}/modules/phpdiff/Diff/
%{__install} -Dp -m0644 modules/AuthInterfaces/*.php %{buildroot}%{os_dir}/modules/AuthInterfaces/

%{__install} -Dp -m0644 lib/* %{buildroot}%{os_dir}/lib/

%{__install} -Dp -m0755 misc/cronjobs/create-saigon-modgearman-builder-crontab %{buildroot}%{os_dir}/misc/cronjobs/create-saigon-modgearman-builder-crontab
%{__install} -Dp -m0755 misc/cronjobs/create-saigon-nagiosplugin-builder-crontab %{buildroot}%{os_dir}/misc/cronjobs/create-saigon-nagiosplugin-builder-crontab
%{__install} -Dp -m0755 misc/cronjobs/create-saigon-nrpe-builder-crontab %{buildroot}%{os_dir}/misc/cronjobs/create-saigon-nrpe-builder-crontab
%{__install} -Dp -m0755 misc/cronjobs/create-saigon-builder-crontab %{buildroot}%{os_dir}/misc/cronjobs/create-saigon-builder-crontab
%{__install} -Dp -m0644 misc/cronjobs/saigon-hostaudit %{buildroot}%{os_dir}/misc/cronjobs/saigon-hostaudit
%{__install} -Dp -m0644 misc/*.cfg %{buildroot}%{os_dir}/misc/
%{__install} -Dp -m0644 misc/logrotate/* %{buildroot}%{_sysconfdir}/logrotate.d
%{__install} -Dp -m0644 misc/saigon-tester.monitrc %{buildroot}%{os_dir}/misc/saigon-tester.monitrc
%{__install} -Dp -m0755 misc/saigon-tester.init %{buildroot}%{os_dir}/misc/saigon-tester.init
%{__install} -Dp -m0644 misc/saigon-web-apache.conf %{buildroot}%{os_dir}/misc/saigon-web-apache.conf
%{__install} -Dp -m0644 misc/saigon-web-nginx.conf %{buildroot}%{os_dir}/misc/saigon-web-nginx.conf
%{__install} -Dp -m0644 misc/saigon-api-apache.conf %{buildroot}%{os_dir}/misc/saigon-api-apache.conf
%{__install} -Dp -m0644 misc/saigon-api-nginx.conf %{buildroot}%{os_dir}/misc/saigon-api-nginx.conf
%{__install} -Dp -m0644 misc/saigon-events-submitter.monitrc %{buildroot}%{os_dir}/misc/saigon-events-submitter.monitrc
%{__install} -Dp -m0755 misc/saigon-events-submitter.init %{buildroot}%{os_dir}/misc/saigon-events-submitter.init

%{__install} -Dp -m0644 renderers/* %{buildroot}%{os_dir}/renderers/

%{__install} -Dp -m0644 views/* %{buildroot}%{os_dir}/views/

%{__install} -Dp -m0644 www/*.php %{buildroot}%{os_dir}/www/
%{__install} -Dp -m0644 www/favicon.ico %{buildroot}%{os_dir}/www/favicon.ico
%{__install} -Dp -m0644 www/static/css/* %{buildroot}%{os_dir}/www/static/css/
%{__install} -Dp -m0644 www/static/imgs/*.* %{buildroot}%{os_dir}/www/static/imgs/
%{__install} -Dp -m0644 www/static/imgs/jquery-ui/* %{buildroot}%{os_dir}/www/static/imgs/jquery-ui/
%{__install} -Dp -m0644 www/static/js/*.js %{buildroot}%{os_dir}/www/static/js/
%{__install} -Dp -m0644 www/static/js/brushes/*.js %{buildroot}%{os_dir}/www/static/js/brushes/

%{__install} -Dp -m0644 routes/* %{buildroot}%{os_dir}/routes/
%{__install} -Dp -m0644 api/index.php %{buildroot}%{os_dir}/api/index.php
%{__install} -Dp -m0644 api/.htaccess %{buildroot}%{os_dir}/api/.htaccess
%{__install} -Dp -m0644 composer.lock %{buildroot}%{os_dir}/composer.lock
%{__install} -Dp -m0644 composer.json %{buildroot}%{os_dir}/composer.json

%post

%post nagios-consumer
%{os_dir}/misc/cronjobs/create-saigon-builder-crontab &>/dev/null ||:

%post nrpe-consumer
%{os_dir}/misc/cronjobs/create-saigon-nrpe-builder-crontab &>/dev/null ||:

%post modgearman-consumer
%{os_dir}/misc/cronjobs/create-saigon-modgearman-builder-crontab &>/dev/null ||:

%post nagiosplugin-consumer
%{os_dir}/misc/cronjobs/create-saigon-nagiosplugin-builder-crontab &>/dev/null ||:

%post nagiostest-consumer
ln -s %{os_dir}/misc/saigon-tester.monitrc /etc/monit.d/saigon-tester ||:

%clean
[ "%{buildroot}" != "/" ] && %{__rm} -rf %{buildroot}

%files
%defattr(-,root,root,-)
%dir /var/log/saigon
%dir %attr(-,apache,apache) %{os_dir}/audit
%dir %{os_dir}/misc
%dir %{os_dir}/log
%dir %{os_dir}/api
%dir %{os_dir}/www
%{os_dir}/composer.lock
%{os_dir}/composer.json
%{os_dir}/include
%{os_dir}/lib
%{os_dir}/modules
%{os_dir}/renderers
%{os_dir}/misc/testing-nagios.cfg
%{os_dir}/conf/hostmodules.inc.php
%config(noreplace) %{os_dir}/conf/role.inc.php
%{os_dir}/conf/version.inc.php
%{os_dir}/conf/cdc_creds.ini
%{os_dir}/conf/nrperpm.inc.php
%{os_dir}/conf/thirdpartymodules.inc.php
%{os_dir}/vendor

%files ui
%{os_dir}/consumer/saigon-fetch-cdc-routervms
%{os_dir}/consumer/saigon-hostaudit
%{os_dir}/misc/cronjobs/saigon-hostaudit
%{os_dir}/controllers
%{os_dir}/views
%{os_dir}/www

%files api
%{os_dir}/misc/saigon-api-apache.conf
%{os_dir}/misc/saigon-api-nginx.conf
%{os_dir}/api
%{os_dir}/routes

%files webconfig
%config(noreplace) %{os_dir}/conf/saigon.inc.php
%{os_dir}/misc/saigon-web-apache.conf
%{os_dir}/misc/saigon-web-nginx.conf

%files nagios-consumer
%dir /var/log/saigon
%config %{os_dir}/conf/saigon-nagios-builder.inc.php
%config(noreplace) %{os_dir}/conf/sharding.inc.php
%config(noreplace) %{os_dir}/conf/deployment.inc.php
%attr(755, root, root) %{os_dir}/misc/cronjobs/create-saigon-builder-crontab
%{_sysconfdir}/logrotate.d/saigon-nagios-builder
%{os_dir}/consumer/saigon-nagios-builder

%files nrpe-consumer
%dir /var/log/saigon
%config %{os_dir}/conf/saigon-nrpe-builder.ini
%attr(755, root, root) %{os_dir}/misc/cronjobs/create-saigon-nrpe-builder-crontab
%dir %{os_dir}/log
%{_sysconfdir}/logrotate.d/saigon-nrpe-builder
%{os_dir}/consumer/saigon-nrpe-builder.d
%{os_dir}/consumer/saigon-nrpe-builder

%files modgearman-consumer
%dir /var/log/saigon
%config %{os_dir}/conf/saigon-modgearman-builder.ini
%attr(755, root, root) %{os_dir}/misc/cronjobs/create-saigon-modgearman-builder-crontab
%dir %{os_dir}/log
%{_sysconfdir}/logrotate.d/saigon-modgearman-builder
%{os_dir}/consumer/saigon-modgearman-builder

%files nagiosplugin-consumer
%dir /var/log/saigon
%config %{os_dir}/conf/saigon-nagiosplugin-builder.ini
%attr(755, root, root) %{os_dir}/misc/cronjobs/create-saigon-nagiosplugin-builder-crontab
%dir %{os_dir}/log
%{_sysconfdir}/logrotate.d/saigon-nagiosplugin-builder
%{os_dir}/consumer/saigon-nagiosplugin-builder.d
%{os_dir}/consumer/saigon-nagiosplugin-builder

%files nagiostest-consumer
%dir /var/log/saigon
%dir %attr(-,apache,apache) %{os_dir}/var/run
%config %{os_dir}/conf/saigon-tester.inc.php
%dir %{os_dir}/log
%{os_dir}/consumer/saigon-tester
%{os_dir}/misc/saigon-tester.init
%{os_dir}/misc/saigon-tester.monitrc

%files nrpe-rpm-consumer
%{os_dir}/consumer/saigon-nrpe-rpm-builder

%files events-submitter
%dir %{os_dir}/var/run
%{os_dir}/consumer/saigon-events-submitter
%{os_dir}/misc/saigon-events-submitter.init
%{os_dir}/misc/saigon-events-submitter.monitrc

%changelog
* Tue May 20 2014 Matt West <mwest@zynga.com> - 1.1-0
- Bugfixes
- Feature Addition: Varnish Cache
- Feature Addition: Event Submission API w/ Consumer
- Additional Minor Features

* Sat Feb 15 2014 Matt West <mwest@zynga.com> - 1.0-2
- Bugfixes

* Tue Nov 12 2013 Matt West <mwest@zynga.com> - 1.0-1
- Initial Public Release
