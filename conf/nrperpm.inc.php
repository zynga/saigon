<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

define('SPECFILE',"%define os_dir /opt/saigon
%define debug_package %{nil}
%define deployment <deploymentname>

Name:       %{deployment}-saigon-nrpe-builder-config
Version:    1.0
Release:    1
Summary:    Saigon NRPE Configuration System
Group:      Applications/Systems
License:    Proprietary
Buildroot:  %{_tmppath}/%{name}-%{version}-root
BuildArch:  noarch
Requires:   saigon-nrpe-consumer
Requires:   nrpe, nagios-plugins

%description
Saigon is a product designed in house at Zynga to help
configure / deploy configurations to Nagios master nodes, so their
configs can be managed centrally and built with best practices.

This is the config file for the nrpeconsumer for the %{deployment} deployment.

%package supplemental
Summary:    Saigon NRPE Configuration System
Group:      Applications/Systems

%description supplemental
Saigon is a product designed in house at Zynga to help
configure / deploy configurations to Nagios master nodes, so their
configs can be managed centrally and built with best practices.

This is the supplemental config file for the nrpeconsumer for the %{deployment} deployment.

%prep

%{__cat} <<'EOF' >%{deployment}-core.ini
[Main]
deployment = %{deployment}
core = 1
core_location = /usr/local/nagios/etc/nrpe.cfg
core_posthook = /etc/init.d/nrpe restart
core_url = https://127.0.0.1/api
supplemental = 0
supp_location = /usr/local/nagios/etc/nrpe.d/%{deployment}.cfg
supp_posthook = /etc/init.d/nrpe restart
supp_url = https://127.0.0.1/api
EOF

%{__cat} <<'EOF' >%{deployment}-supplemental.ini
[Main]
deployment = %{deployment}
core = 0
core_location = /usr/local/nagios/etc/nrpe.cfg
core_posthook = /etc/init.d/nrpe restart
core_url = https://127.0.0.1/api
supplemental = 1
supp_location = /usr/local/nagios/etc/nrpe.d/%{deployment}.cfg
supp_posthook = /etc/init.d/nrpe restart
supp_url = https://127.0.0.1/api
EOF

%build

%install
rm -rf %{buildroot}
mkdir -p %{buildroot}%{os_dir}/consumer/saigon-nrpe-builder.d

install -Dp -m0644 %{deployment}-core.ini %{buildroot}%{os_dir}/consumer/saigon-nrpe-builder.d/%{deployment}-core.ini
install -Dp -m0644 %{deployment}-supplemental.ini %{buildroot}%{os_dir}/consumer/saigon-nrpe-builder.d/%{deployment}-supplemental.ini

%post
/sbin/chkconfig --add nrpe ||:

%files
%defattr(-,root,root,-)
%{os_dir}/consumer/saigon-nrpe-builder.d/%{deployment}-core.ini

%files supplemental
%defattr(-,root,root,-)
%{os_dir}/consumer/saigon-nrpe-builder.d/%{deployment}-supplemental.ini

%changelog
* <dateholder> Saigon Automation <saigon-automation@invalid-host.com> - 1.0-1
- Initial Packaging");

