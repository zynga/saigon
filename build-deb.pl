#!/usr/bin/perl
#

use strict;
use warnings;
use Getopt::Long;
use File::Copy::Recursive qw( fcopy dircopy pathmk pathrmdir );

my @modes = (
    qw(
        all core web nagiosbuilder nagiospluginbuilder nrpebuilder modgearmanbuilder testbuilder
    )
);

my ($o_mode, $o_root);

GetOptions(
    "mode:s"    => \$o_mode,
    "root:s"    => \$o_root,
);

if (!$o_mode) {
    $o_mode = "all";
}
else {
    $o_mode = lc($o_mode);
}

if (!$o_root) {
    $o_root = "/opt/saigon";
}
elsif ($o_root =~ /^\/$/) {
    print "Unable to use / as install location, expecting like /opt/saigon\n";
    exit(1);
}
elsif ($o_root =~ /\/$/) {
    $o_root =~ s/\/$//;
}

if (!grep $o_mode eq $_, @modes) {
    print "Argument passed doesn't match an expected value: $o_mode :: " . join(", ", @modes) . "\n";
    exit(1);
}

open FILE, '<', "debian/version" or die "Couldn't open file: debian/version";
my $version = <FILE>;
close FILE;
chomp($version);

if ($o_mode eq "all") {
    print "Building All Packages...\n";
    build_core($version);
    build_web($version);
    build_nagiosbuilder($version);
    build_nagiospluginbuilder($version);
    build_nrpebuilder($version);
    build_modgearmanbuilder($version);
    build_testbuilder($version);
}
elsif ($o_mode eq "core") {
    build_core($version);
}
elsif ($o_mode eq 'web') {
    build_web($version);
}
elsif ($o_mode eq "nagiosbuilder") {
    build_nagiosbuilder($version);
}
elsif ($o_mode eq "nagiospluginbuilder") {
    build_nagiospluginbuilder($version);
}
elsif ($o_mode eq "nrpebuilder") {
    build_nrpebuilder($version);
}
elsif ($o_mode eq "modgearmanbuilder") {
    build_modgearmanbuilder($version);
}
elsif ($o_mode eq "testbuilder") {
    build_testbuilder($version);
}
else {
    print "Hey, how'd you get here???\n";
    exit(1);
}

sub build_core {
    print "Building Core Package...\n";
    my $version = shift;
    pathmk("_debian/var/log/saigon");
    pathmk("_debian/$o_root/audit","_debian/$o_root/log","_debian/$o_root/misc");
    pathmk("_debian/$o_root/include","_debian/$o_root/lib","_debian/$o_root/modules");
    pathmk("_debian/$o_root/renderers","_debian/$o_root/conf");
    pathmk("_debian/$o_root/consumer");
    fcopy("composer.lock", "_debian/$o_root/composer.lock");
    fcopy("composer.json", "_debian/$o_root/composer.json");
    fcopy("misc/testing-nagios.cfg", "_debian/$o_root/misc/testing-nagios.cfg");
    fcopy("conf/hostmodules.inc.php", "_debian/$o_root/conf/hostmodules.inc.php");
    open(FILE, "<", "conf/role.inc.php");
    my @lines = <FILE>;
    close(FILE);
    my @newlines;
    foreach my $line (@lines) {
        $line =~ s/'Dev'/'Prod'/;
        push(@newlines, $line);
    }
    open(FILE, ">", "_debian/$o_root/conf/role.inc.php");
    print FILE @newlines;
    close(FILE);
    fcopy("conf/version.inc.php", "_debian/$o_root/conf/version.inc.php");
    fcopy("conf/cdc_creds.ini", "_debian/$o_root/conf/cdc_creds.ini");
    fcopy("conf/nrperpm.inc.php", "_debian/$o_root/conf/nrperpm.inc.php");
    fcopy("conf/dist.inc.php", "_debian/$o_root/conf/dist.inc.php");
    fcopy("conf/thirdpartymodules.inc.php", "_debian/$o_root/conf/thirdpartymodules.inc.php");
    fcopy("conf/datastoremodules.inc.php", "_debian/$o_root/conf/datastoremodules.inc.php");
    fcopy("consumer/saigon-backup-script", "_debian/$o_root/consumer/saigon-backup-script");
    fcopy("conf/saigon-data-migrator.inc.php", "_debian/$o_root/conf/saigon-data-migrator.inc.php");
    fcopy("consumer/saigon-data-migrator", "_debian/$o_root/consumer/saigon-data-migrator");
    dircopy("lib/*", "_debian/$o_root/lib/");
    dircopy("modules/*", "_debian/$o_root/modules/");
    dircopy("renderers/*", "_debian/$o_root/renderers/");
    dircopy("include/*", "_debian/$o_root/include/");
    createdebianfiles($version, 'saigon');
    qx{fakeroot dpkg -b _debian saigon-$version.deb};
    pathrmdir("_debian");
}

sub build_web {
    print "Building Web Package...\n";
    my $version = shift;
    pathmk("_debian/$o_root/conf","_debian/$o_root/misc");
    pathmk("_debian/$o_root/controllers","_debian/$o_root/views","_debian/$o_root/www");
    pathmk("_debian/$o_root/api","_debian/$o_root/routes");
    fcopy("conf/saigon.inc.php", "_debian/$o_root/conf/saigon.inc.php");
    fcopy("misc/saigon-ldap.conf", "_debian/$o_root/misc/saigon-ldap.conf");
    fcopy("misc/saigon-web-apache.conf", "_debian/$o_root/misc/saigon-web-apache.conf");
    fcopy("misc/saigon-web-nginx.conf", "_debian/$o_root/misc/saigon-web-nginx.conf");
    fcopy("misc/saigon-api-apache.conf", "_debian/$o_root/misc/saigon-api-apache.conf");
    fcopy("misc/saigon-api-nginx.conf", "_debian/$o_root/misc/saigon-api-nginx.conf");
    dircopy("controllers/*", "_debian/$o_root/controllers/");
    dircopy("views/*", "_debian/$o_root/views/");
    dircopy("www/*", "_debian/$o_root/www/");
    dircopy("api/*", "_debian/$o_root/api/");
    dircopy("routes/*", "_debian/$o_root/routes/");
    createdebianfiles($version, 'saigon-web');
    qx{fakeroot dpkg -b _debian saigon-web-$version.deb};
    pathrmdir("_debian");
}

sub build_nagiosbuilder {
    print "Building Nagios Builder Consumer Package...\n";
    my $version = shift;
    pathmk("_debian/etc/logrotate.d");
    pathmk("_debian/var/log/saigon");
    pathmk("_debian/$o_root/conf", "_debian/$o_root/misc/cronjobs", "_debian/$o_root/consumer");
    fcopy("conf/saigon-nagios-builder.inc.php", "_debian/$o_root/conf/saigon-nagios-builder.inc.php");
    fcopy("conf/sharding.inc.php", "_debian/$o_root/conf/sharding.inc.php");
    fcopy("conf/deployment.inc.php", "_debian/$o_root/conf/deployment.inc.php");
    fcopy("misc/logrotate/saigon-nagios-builder", "_debian/etc/logrotate.d/saigon-nagios-builder");
    fcopy("misc/cronjobs/create-saigon-builder-crontab", "_debian/$o_root/misc/cronjobs/create-saigon-builder-crontab");
    chmod oct("0755"), "_debian/$o_root/misc/cronjobs/create-saigon-builder-crontab";
    fcopy("consumer/saigon-nagios-builder", "_debian/$o_root/consumer/saigon-nagios-builder");
    chmod oct("0755"), "_debian/$o_root/consumer/saigon-nagios-builder";
    createdebianfiles($version, 'saigon-nagios-builder');
    qx{fakeroot dpkg -b _debian saigon-nagios-builder-$version.deb};
    pathrmdir("_debian");
}

sub build_nagiospluginbuilder {
    print "Building Nagios Plugin Builder Consumer Package...\n";
    my $version = shift;
    pathmk("_debian/etc/logrotate.d");
    pathmk("_debian/var/log/saigon");
    pathmk("_debian/$o_root/conf");
    pathmk("_debian/$o_root/consumer/saigon-nagiosplugin-builder.d");
    pathmk("_debian/$o_root/misc/cronjobs");
    fcopy("misc/logrotate/saigon-nagiosplugin-builder", "_debian/etc/logrotate.d/saigon-nagiosplugin-builder");
    fcopy("conf/saigon-nagiosplugin-builder.ini", "_debian/$o_root/conf/saigon-nagiosplugin-builder.ini");
    fcopy("misc/cronjobs/create-saigon-nagiosplugin-builder-crontab", "_debian/$o_root/misc/cronjobs/create-saigon-nagiosplugin-builder-crontab");
    chmod oct("0755"), "_debian/$o_root/misc/cronjobs/create-saigon-nagiosplugin-builder-crontab";
    fcopy("consumer/saigon-nagiosplugin-builder", "_debian/$o_root/consumer/saigon-nagiosplugin-builder");
    chmod oct("0755"), "_debian/$o_root/consumer/saigon-nagiosplugin-builder";
    createdebianfiles($version, 'saigon-nagiosplugin-builder');
    qx{fakeroot dpkg -b _debian saigon-nagiosplugin-builder-$version.deb};
    pathrmdir("_debian");
}

sub build_nrpebuilder {
    my $version = shift;
    print "Building NRPE Builder Consumer Package...\n";
    pathmk("_debian/etc/logrotate.d");
    pathmk("_debian/var/log/saigon");
    pathmk("_debian/$o_root/conf");
    pathmk("_debian/$o_root/consumer/saigon-nrpe-builder.d");
    pathmk("_debian/$o_root/misc/cronjobs");
    fcopy("misc/logrotate/saigon-nrpe-builder", "_debian/etc/logrotate.d/saigon-nrpe-builder");
    fcopy("conf/saigon-nrpe-builder.ini", "_debian/$o_root/conf/saigon-nrpe-builder.ini");
    fcopy("misc/cronjobs/create-saigon-nrpe-builder-crontab", "_debian/$o_root/misc/cronjobs/create-saigon-nrpe-builder-crontab");
    chmod oct("0755"), "_debian/$o_root/misc/cronjobs/create-saigon-nrpe-builder-crontab";
    fcopy("consumer/saigon-nrpe-builder", "_debian/$o_root/consumer/saigon-nrpe-builder");
    chmod oct("0755"), "_debian/$o_root/consumer/saigon-nrpe-builder";
    createdebianfiles($version, 'saigon-nrpe-builder');
    qx{fakeroot dpkg -b _debian saigon-nrpe-builder-$version.deb};
    pathrmdir("_debian");
}

sub build_modgearmanbuilder {
    print "Building Mod-Gearman Builder Consumer Package...\n";
    my $version = shift;
    pathmk("_debian/etc/logrotate.d");
    pathmk("_debian/var/log/saigon");
    pathmk("_debian/$o_root/conf");
    pathmk("_debian/$o_root/misc/cronjobs");
    fcopy("misc/logrotate/saigon-modgearman-builder", "_debian/etc/logrotate.d/saigon-modgearman-builder");
    fcopy("conf/saigon-modgearman-builder.ini", "_debian/$o_root/conf/saigon-modgearman-builder.ini");
    fcopy("misc/cronjobs/create-saigon-modgearman-builder-crontab", "_debian/$o_root/misc/cronjobs/create-saigon-modgearman-builder-crontab");
    chmod oct("0755"), "_debian/$o_root/misc/cronjobs/create-saigon-modgearman-builder-crontab";
    fcopy("consumer/saigon-modgearman-builder", "_debian/$o_root/consumer/saigon-modgearman-builder");
    chmod oct("0755"), "_debian/$o_root/consumer/saigon-modgearman-builder";
    createdebianfiles($version, 'saigon-modgearman-builder');
    qx{fakeroot dpkg -b _debian saigon-modgearman-builder-$version.deb};
    pathrmdir("_debian");
}

sub build_testbuilder {
    print "Building Test Builder Consumer Package...\n";
    my $version = shift;
    pathmk("_debian/var/log/saigon");
    pathmk("_debian/$o_root/var/run", "_debian/$o_root/conf");
    pathmk("_debian/$o_root/consumer", "_debian/$o_root/misc");
    fcopy("conf/saigon-tester.inc.php", "_debian/$o_root/conf/saigon-tester.inc.php");
    fcopy("misc/saigon-tester.monitrc", "_debian/$o_root/misc/saigon-tester.monitrc");
    fcopy("misc/saigon-tester.init", "_debian/$o_root/misc/saigon-tester.init");
    chmod oct("0755"), "_debian/$o_root/misc/saigon-tester.init";
    fcopy("consumer/saigon-tester", "_debian/$o_root/consumer/saigon-tester");
    chmod oct("0755"), "_debian/$o_root/consumer/saigon-tester";
    createdebianfiles($version, 'saigon-test-builder');
    qx{fakeroot dpkg -b _debian saigon-test-builder-$version.deb};
    pathrmdir("_debian");
}

sub createdebianfiles {
    my ($version, $package) = (@_);
    pathmk("_debian/DEBIAN");
    createcontrol($version, $package);
    createpreandpost($package);
    pathmk("_debian/usr/share/doc/$package");
    fcopy("LICENSE", "_debian/usr/share/doc/$package/copyright");
    createmd5s();
}

sub createcontrol {
    my ($version, $package) = (@_);
    open my $fh, ">", "_debian/DEBIAN/control" or
        die "Unable to open file: _debian/DEBIAN/control :: $!";
    print $fh "Package: $package\n";
    print $fh "Version: $version\n";
    print $fh "Section: unknown\n";
    print $fh "Priority: optional\n";
    print $fh "Architecture: all\n";
    print $fh "Maintainer: Matt West <mwest\@pinterest.com>\n";
    if ($package eq 'saigon') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This is the core package required by web and some builder processes\n";
        print $fh "Depends: php5, php5-common, php5-curl, phpredis\n";
    }
    elsif ($package eq 'saigon-web') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This is the web package, which contains the api and ui\n";
        print $fh "Depends: saigon (>= $version), php5-ldap, libapache2-mod-php5\n";
    }
    elsif ($package eq 'saigon-nagios-builder') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This is the Nagios configuration file builder package, which builds the\n";
        print $fh " Nagios configuration files on your Nagios master(s)\n";
        print $fh "Depends: saigon (>= $version), php5-cli, nagios3\n";
    }
    elsif ($package eq 'saigon-nagiosplugin-builder') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This provides the Saigon NRPE builder which fetches the nagios plugins for\n";
        print $fh " the tenants which have placed a .ini file in the include directory.\n";
        print $fh "Depends: libconfig-auto-perl, libconfig-inifiles-perl, libdigest-md5-file-perl, libjson-perl, libio-interface-perl\n";
    }
    elsif ($package eq 'saigon-nrpe-builder') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This provides the builder which fetches the nrpe configuration files and\n";
        print $fh " plugins for the tenants which have placed a .ini file in the include directory\n";
        print $fh "Depends: libconfig-auto-perl, libconfig-inifiles-perl, libdigest-md5-file-perl, libjson-perl, libio-interface-perl\n";
    }
    elsif ($package eq 'saigon-modgearman-builder') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This provides the builder which fetches the modgearman configuration files\n";
        print $fh " for the nagios machines participating in a cluster\n";
        print $fh "Depends: libconfig-auto-perl, libconfig-inifiles-perl, libdigest-md5-file-perl, libjson-perl, libio-interface-perl\n";
    }
    elsif ($package eq 'saigon-test-builder') {
        print $fh "Description: Saigon is a product designed in house at Zynga to help configure and\n";
        print $fh " deploy configurations to Nagios master nodes, and NRPE Clients so configs can\n";
        print $fh " be managed centrally and built with best practices.\n";
        print $fh " This is the test builder package, which contains the asynchronous consumer\n";
        print $fh " for testing, showing, and diffing versions of Nagios configuration files\n";
        print $fh "Depends: saigon (>= $version), php5-cli, monit, nagios3-core\n";
    }
    close $fh;
}

sub createpreandpost {
    my $package = shift;
    if ($package eq 'saigon') {
    }
    elsif ($package eq 'saigon-web') {
    }
    elsif ($package eq 'saigon-nagios-builder') {
        open my $fh, ">", "_debian/DEBIAN/postinst" or
            die "Unable to open file _debian/DEBIAN/postinst :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"configure\" ]; then\n";
        print $fh " if [ ! -f /etc/cron.d/saigon-nagios-builder ]; then\n";
        print $fh "  /$o_root/misc/cronjobs/create-saigon-builder-crontab\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
        open $fh, ">", "_debian/DEBIAN/prerm" or
            die "Unable to open file _debian/DEBIAN/prerm :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"remove\" ]; then\n";
        print $fh " if [ -f /etc/cron.d/saigon-nagios-builder ]; then\n";
        print $fh "  rm -f /etc/cron.d/saigon-nagios-builder\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
    }
    elsif ($package eq 'saigon-nagiosplugin-builder') {
        open my $fh, ">", "_debian/DEBIAN/postinst" or
            die "Unable to open file _debian/DEBIAN/postinst :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"configure\" ]; then\n";
        print $fh " if [ ! -f /etc/cron.d/saigon-nagiosplugin-builder ]; then\n";
        print $fh "  /$o_root/misc/cronjobs/create-saigon-nagiosplugin-builder-crontab\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
        open $fh, ">", "_debian/DEBIAN/prerm" or
            die "Unable to open file _debian/DEBIAN/prerm :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"remove\" ]; then\n";
        print $fh " if [ -f /etc/cron.d/saigon-nagiosplugin-builder ]; then\n";
        print $fh "  rm -f /etc/cron.d/saigon-nagiosplugin-builder\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
    }
    elsif ($package eq 'saigon-nrpe-builder') {
        open my $fh, ">", "_debian/DEBIAN/postinst" or
            die "Unable to open file _debian/DEBIAN/postinst :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"configure\" ]; then\n";
        print $fh " if [ ! -f /etc/cron.d/saigon-nrpe-builder ]; then \n";
        print $fh "  /$o_root/misc/cronjobs/create-saigon-nrpe-builder-crontab\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
        open $fh, ">", "_debian/DEBIAN/prerm" or
            die "Unable to open file _debian/DEBIAN/prerm :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"remove\" ]; then\n";
        print $fh " if [ -f /etc/cron.d/saigon-nrpe-builder ]; then\n";
        print $fh "  rm -f /etc/cron.d/saigon-nrpe-builder\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
    }
    elsif ($package eq 'saigon-modgearman-builder') {
        open my $fh, ">", "_debian/DEBIAN/postinst" or
            die "Unable to open file _debian/DEBIAN/postinst :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"configure\" ]; then\n";
        print $fh " if [ ! -f /etc/cron.d/saigon-modgearman-builder ]; then\n";
        print $fh "  /$o_root/misc/cronjobs/create-saigon-modgearman-builder-crontab\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
        open $fh, ">", "_debian/DEBIAN/prerm" or
            die "Unable to open file _debian/DEBIAN/prerm :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"remove\" ]; then\n";
        print $fh " if [ -f /etc/cron.d/saigon-modgearman-builder ]; then\n";
        print $fh "  rm -f /etc/cron.d/saigon-modgearman-builder\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
    }
    elsif ($package eq 'saigon-test-builder') {
        open my $fh, ">", "_debian/DEBIAN/postinst" or
            die "Unable to open file _debian/DEBIAN/postinst :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        close $fh;
        open $fh, ">", "_debian/DEBIAN/prerm" or
            die "Unable to open file _debian/DEBIAN/prerm :: $!";
        print $fh "#!/bin/sh\n#\n\n";
        print $fh "set -e\n";
        print $fh "if [ \"\$1\" = \"remove\" ]; then\n";
        print $fh " if [ -f /etc/monit/conf.d/saigon-tester ]; then\n";
        print $fh "  rm -f /etc/monit/conf.d/saigon-tester\n";
        print $fh " fi\n";
        print $fh "fi\n";
        close $fh;
    }
    chmod oct("0755"), "_debian/DEBIAN/postinst" unless (!-e "_debian/DEBIAN/postinst");
    chmod oct("0755"), "_debian/DEBIAN/prerm" unless (!-e "_debian/DEBIAN/prerm");
}

sub createmd5s {
    open my $fh, ">", "_debian/DEBIAN/md5sums" or
        die "Unable to open file: _debian/DEBIAN/md5sums :: $!";
    open CMD, "find _debian/ -not -path \"*/DEBIAN/*\" -type f -exec md5sum {} \\; |" or
        die "Failed: $!";
    while (my $line = <CMD>) {
        $line =~ s/_debian\///;
        print $fh $line;
    }
    close($fh);
}

