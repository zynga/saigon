<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RevDeploy {

    protected static $init = false;
    protected static $dsmodule;
    protected static $ds;
    protected static $log;
    protected static $user = '';

    public static function init($dsmodule = false, $override = false)
	{
        /* Initial Redis Information */
        if ( (self::$init === false) || ($override === true) ) {
            if ($dsmodule === false) {
                $dsmodule = DSMODULE;
                self::$dsmodule = DSMODULE;
            }
            else {
                self::$dsmodule = $dsmodule;
            }
            if ((!isset(self::$ds[self::$dsmodule])) || (empty(self::$ds[self::$dsmodule]))) {
                self::$ds[self::$dsmodule] = new $dsmodule();
            }
            self::$log = new NagLogger();
            self::$init = true;
            if (CONSUMER === false) {
                $amodule = AUTH_MODULE;
                self::$user = $amodule::getUser();
            }
        }
        return;
    }

    private static function addAuditUserLog($deployment, $revision)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->addAuditUserLog($deployment, $revision, self::$user);
    }

    public static function getAuditLog($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getAuditLog($deployment);
    }

    public static function setAuditLog($deployment, $revision, array $revisionData)
    {
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->setAuditLog($deployment, $revision, $revisionData);
    }

    public static function getCommonRepos()
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getCommonRepos();
        if (empty($results)) {
            return array('common');
        } else {
            if (is_array($results)) {
                if (!in_array('common', $results)) {
                    array_push($results, 'common');
                }
            }
            else {
                $results = array($results);
                if (!in_array('common', $results)) {
                    array_push($results, 'common');
                }
            }
            sort($results);
            return $results;
        }
    }

    public static function addCommonRepo($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->addCommonRepo($deployment);
    }

    public static function delCommonRepo($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->delCommonRepo($deployment);
    }

    public static function getDeploymentRev($deployment)
	{
        if (self::$init === false) self::init();
        $revision = self::$ds[self::$dsmodule]->getDeploymentRev($deployment);
        if ((empty($revision)) || ($revision === false)) {
            return false;
        } else {
            return $revision;
        }
    }

    public static function getDeploymentNextRev($deployment)
	{
        if (self::$init === false) self::init();
        $revision = self::$ds[self::$dsmodule]->getDeploymentNextRev($deployment);
        if ((empty($revision)) || ($revision === false)) {
            return false;
        } else {
            return $revision;
        }
    }

    public static function getDeploymentPrevRev($deployment)
	{
        if (self::$init === false) self::init();
        $revision = self::$ds[self::$dsmodule]->getDeploymentPrevRev($deployment);
        if ((empty($revision)) || ($revision === false)) { 
            return false;
        } else {
            return $revision;
        }
    }

    public static function getDeploymentRevs($deployment)
	{
        if (self::$init === false) self::init();
        $results = array();
        $results['currrev'] = self::getDeploymentRev($deployment);
        $results['nextrev'] = self::getDeploymentNextRev($deployment);
        $results['prevrev'] = self::getDeploymentPrevRev($deployment);
        return $results;
    }

    public static function getDeploymentAllRevs($deployment)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentAllRevs($deployment);
        natsort($results);
        return $results;
    }

    public static function setDeploymentRevs($deployment, $from, $to, $note)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->setDeploymentRevs($deployment, $from, $to, $note);
        self::addAuditUserLog($deployment, $to);
        self::$log->addToLog(self::$user.' '.NagMisc::getIP().' deployment='.$deployment.' action=change_deployment_revision fromrevision='.$from.' torevision='.$to. ' note='.$note);
    }

    public static function setDeploymentAllRevs($deployment, $prev, $curr, $next)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->setDeploymentAllRevs($deployment, $prev, $curr, $next);
        self::$log->addToLog(self::$user.' '.NagMisc::getIP().' deployment='.$deployment.' action=set_deployment_revisions prevrevision='.$prev.' revision='.$curr. ' nextrevision='.$next);
    }

    public static function deleteDeploymentRev($deployment, $revision)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->deleteDeploymentRev($deployment, $revision);
        if (is_array($revision)) {
            foreach ($revision as $subrevision) {
                self::$log->addToLog(self::$user.' '.NagMisc::getIP().' revision='.$subrevision.' deployment='.$deployment.' action=deployment_revision_delete Bulk Removal of Revision Issued');
            }
        } else {
            self::$log->addToLog(self::$user.' '.NagMisc::getIP().' revision='.$revision.' deployment='.$deployment.' action=deployment_revision_delete Bulk Removal of Revision Issued');
        }
    }

    public static function deleteDeployment($deployment)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->deleteDeployment($deployment);
        self::$log->addToLog(self::$user.' '.NagMisc::getIP().' deployment='.$deployment.' action=deployment_delete Bulk Removal of Deployment Issued');
    }

    public static function incrDeploymentNextRev($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->incrDeploymentNextRev($deployment);
    }

    public static function existsDeploymentRev($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $keys = self::$ds[self::$dsmodule]->existsDeploymentRev($deployment, $revision);
        if (($keys === false) || (empty($keys))) {
            return false;
        }
        return true;
    }

    public static function createCommonDeployment()
	{
        if (self::$init === false) self::init();
        if (($return = self::$ds[self::$dsmodule]->existsDeployment('common')) === false) {
            $commonInfo = array(
                'name' => 'common',
                'deploystyle' => 'commonrepo',
                'desc' => 'Global Configuration Information',
                'authgroups' => 'Overridden by Conf on Server',
                'aliastemplate' => 'host-dc'
            );
            self::createDeployment('common', $commonInfo, array(), array());
            $timeperiodInfo = array();
            $timeperiodInfo['24x7'] = array(
                "timeperiod_name" => "24x7",
				"alias" => "24x7 Monitoring",
				"use" => "",
				"times" => array(
                    array("directive" => "friday","range" => "00:00-24:00"),
                    array("directive" => "monday","range" => "00:00-24:00"),
                    array("directive" => "saturday","range" => "00:00-24:00"),
                    array("directive" => "sunday","range" => "00:00-24:00"),
                    array("directive" => "thursday","range" => "00:00-24:00"),
                    array("directive" => "tuesday","range" => "00:00-24:00"),
                    array("directive" => "wednesday","range" => "00:00-24:00")
                )
            );
            foreach ($timeperiodInfo as $timeperiod => $tpInfo) {
                $timesInt = $tpInfo['times'];
                $times = array();
                foreach($timesInt as $timeArray) {
                    $md5 = md5($timeArray['directive']);
                    $times[$md5] = $timeArray;
                }
                unset($tpInfo['times']);
                self::createDeploymentTimeperiod('common', $timeperiod, $tpInfo, $times, 1);
            }
            $commandInfo = array();
            $commandInfo['check-fast-host-alive'] = array(
                "command_name" => "check-fast-host-alive",
                "command_desc" => "Check Host is Alive",
                "command_line" => "JFVTRVIxJC9jaGVja19mcGluZyAkSE9TVEFERFJFU1Mk"
            );
            $commandInfo['check-host-alive'] = array(
                "command_name" => "check-host-alive",
                "command_desc" => "Checks to see if a host is alive by pinging it with 5packets",
                "command_line" => "JFVTRVIxJC9jaGVja19waW5nIC1IICRIT1NUQUREUkVTUyQgLXcgMzAwMC4wLDgwJSAtYyA1MDAwLjAsMTAwJSAtcCA1"
            );
            $commandInfo['check_nrpe_avail'] = array(
                "command_name" => "check_nrpe_avail",
                "command_desc" => "Make sure NRPE is available",
                "command_line" => "L3Vzci9sb2NhbC9uYWdpb3MvbGliZXhlYy9jaGVja19ucnBlIC10IDQ1IC1IICRIT1NUQUREUkVTUyQ="
            );
            $commandInfo['check_nrpe_cmd'] = array(
                "command_name" => "check_nrpe_cmd",
				"command_desc" => "Run Command on Remote System using NRPE",
				"command_line" => "L3Vzci9sb2NhbC9uYWdpb3MvbGliZXhlYy9jaGVja19ucnBlIC10IDQ1IC1IICRIT1NUQUREUkVTUyQgLWMgJEFSRzEk"
            );
            $commandInfo['check_nrpe_cmd_wargs'] = array(
                "command_name" => "check_nrpe_cmd_wargs",
				"command_desc" => "Run Command on Remote System using NRPE with Arguments",
				"command_line" => "L3Vzci9sb2NhbC9uYWdpb3MvbGliZXhlYy9jaGVja19ucnBlIC10IDQ1IC1IICRIT1NUQUREUkVTUyQgLWMgJEFSRzEkIC1hICRBUkcyJCAkQVJHMyQgJEFSRzQkICRBUkc1JCAkQVJHNiQgJEFSRzckICRBUkc4JA=="
            );
            $commandInfo['notify-host-by-email'] = array(
                "command_name" => "notify-host-by-email",
				"command_desc" => "Sends a host generated alert notification to an email address",
				"command_line" => "L3Vzci9iaW4vcHJpbnRmICIlYiIgIioqKioqIERpc3RyaWJ1dGVkIE5hZ2lvcyAqKioqKlxuXG5Ob3RpZmljYXRpb24gVHlwZTogJE5PVElGSUNBVElPTlRZUEUkXG5Ib3N0OiAkSE9TVE5BTUUkXG5TdGF0ZTogJEhPU1RTVEFURSRcbkFkZHJlc3M6ICRIT1NUQUREUkVTUyRcbkluZm86ICRIT1NUT1VUUFVUJFxuXG5EYXRlL1RpbWU6ICRMT05HREFURVRJTUUkXG4iIHwgL2Jpbi9tYWlsIC1zICIqKiAkTk9USUZJQ0FUSU9OVFlQRSQgSG9zdCBBbGVydDogJEhPU1ROQU1FJCBpcyAkSE9TVFNUQVRFJCAqKiIgJENPTlRBQ1RFTUFJTCQ="
            );
            $commandInfo['notify-host-by-email-sms'] = array(
                "command_name" => "notify-host-by-email-sms",
				"command_desc" => "Sends a sms sized host generated alert notification to an email address",
				"command_line" => "L3Vzci9iaW4vcHJpbnRmICIlYiIgIkhvc3Q6ICRIT1NUTkFNRSRcblN0YXRlOiAkSE9TVFNUQVRFJFxuQWRkcmVzczogJEhPU1RBRERSRVNTJFxuR3JvdXA6ICRIT1NUR1JPVVBOQU1FUyQiIHwgL2Jpbi9tYWlsIC1zICIqKiAkTk9USUZJQ0FUSU9OVFlQRSQgSG9zdCBBbGVydDogJEhPU1ROQU1FJCBpcyAkSE9TVFNUQVRFJCAqKiIgJENPTlRBQ1RFTUFJTCQ="
            );
            $commandInfo['notify-service-by-email'] = array(
                "command_name" => "notify-service-by-email",
				"command_desc" => "Sends a service generated alert notification to an email address",
				"command_line" => "L3Vzci9iaW4vcHJpbnRmICIlYiIgIioqKioqIERpc3RyaWJ1dGVkIE5hZ2lvcyAqKioqKlxuXG5Ob3RpZmljYXRpb24gVHlwZTogJE5PVElGSUNBVElPTlRZUEUkXG5cblNlcnZpY2U6ICRTRVJWSUNFREVTQyRcbkhvc3Q6ICRIT1NUQUxJQVMkXG5BZGRyZXNzOiAkSE9TVEFERFJFU1MkXG5TdGF0ZTogJFNFUlZJQ0VTVEFURSRcblxuRGF0ZS9UaW1lOiAkTE9OR0RBVEVUSU1FJFxuXG5BZGRpdGlvbmFsIEluZm86XG5cbiRTRVJWSUNFT1VUUFVUJFxuIiB8IC9iaW4vbWFpbCAtcyAiKiogJE5PVElGSUNBVElPTlRZUEUkIFNlcnZpY2UgQWxlcnQ6ICRIT1NUQUxJQVMkLyRTRVJWSUNFREVTQyQgaXMgJFNFUlZJQ0VTVEFURSQgKioiICRDT05UQUNURU1BSUwk"
            );
            $commandInfo['notify-service-by-email-sms'] = array(
                "command_name" => "notify-service-by-email-sms",
				"command_desc" => "Sends a sms sized service generated alert notification to an email address",
				"command_line" => "L3Vzci9iaW4vcHJpbnRmICIlYiIgIlNlcnZpY2U6ICRTRVJWSUNFREVTQyRcbkhvc3Q6ICRIT1NUQUxJQVMkXG5BZGRyZXNzOiAkSE9TVEFERFJFU1MkXG5Hcm91cDogJEhPU1RHUk9VUE5BTUVTJFxuU3RhdGU6ICRTRVJWSUNFU1RBVEUkIiB8IC9iaW4vbWFpbCAtcyAiKiogJE5PVElGSUNBVElPTlRZUEUkIFNlcnZpY2UgQWxlcnQ6ICRIT1NUQUxJQVMkLyRTRVJWSUNFREVTQyQgaXMgJFNFUlZJQ0VTVEFURSQgKioiICRDT05UQUNURU1BSUwk"
            );
            foreach ($commandInfo as $cmd => $cmdInfo) {
                self::createDeploymentCommand('common', $cmd, $cmdInfo, 1);
            }
            $contactTemplateInfo = array();
            $contactTemplateInfo['generic-email-contact'] = array(
                "name" => "generic-email-contact",
				"alias" => "generic email contact template",
				"use" => "",
				"host_notifications_enabled" => "1",
				"host_notification_period" => "24x7",
				"host_notification_options" => array("d","u","r","s"),
				"host_notification_commands" => "notify-host-by-email",
				"service_notifications_enabled" => "1",
				"service_notification_period" => "24x7",
				"service_notification_options" => array("w","u","c","r","s"),
				"service_notification_commands" => "notify-service-by-email",
				"contact_name" => "generic-email-contact",
				"register" => "0",
            );
            $contactTemplateInfo['generic-sms-contact'] = array(
                "name" => "generic-sms-contact",
				"alias" => "generic sms contacttemplate",
				"use" => "generic-email-contact",
				"host_notification_options" => array("d","u"),
				"host_notification_commands" => "notify-host-by-email-sms",
				"service_notification_options" => array("u","c"),
				"service_notification_commands" => "notify-service-by-email-sms",
				"contact_name" => "generic-sms-contact",
				"register" => "0",
            );
            foreach ($contactTemplateInfo as $cTemp => $cTempInfo) {
                self::createDeploymentContactTemplate('common', $cTemp, $cTempInfo, 1);
            }
            $hostTemplateInfo = array();
            $hostTemplateInfo['generic-server'] = array(
                "name" => "generic-server",
				"alias" => "generic server host template",
				"use" => "",
				"check_command" => "",
				"initial_state" => "",
				"max_check_attempts" => "3",
				"check_interval" => "5",
				"retry_interval" => "1",
				"active_checks_enabled" => "1",
				"passive_checks_enabled" => "1",
				"check_period" => "24x7",
				"process_perf_data" => "",
				"retain_status_information" => "1",
				"retain_nonstatus_information" => "1",
				"notifications_enabled" => "1",
				"notification_interval" => "60",
				"notification_period" => "24x7",
				"notification_options" => array("d","u","r","s"),
				"register" => "0"
            );
            foreach ($hostTemplateInfo as $hTemp => $hTempInfo) {
                self::createDeploymentHostTemplate('common', $hTemp, $hTempInfo, 1);
            }
            // Let the Datastore Sync
            sleep(1);
            CopyDeploy::copyDeploymentRevision('common', 1, 2);
            return true;
        }
        return false;
    }

    public static function createDeployment(
        $deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeployment(
                $deployment, $deployInfo, $deployHostSearch, $deployStaticHosts
            );
        if ($results === true) {
            if ($deployInfo['deploystyle'] == 'commonrepo') {
                self::addCommonRepo($deployment);
            }
            $deployData =
                new DeploymentData(
                    $deployment, $deployInfo, $deployHostSearch, $deployStaticHosts, 'create'
                );
            self::$log->addToLog($deployData);
            return true;
        }
        return false;
    }

    public static function modifyDeployment(
        $deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts
    ) {
        if (self::$init === false) self::init();
        $oldDeployInfo = self::getDeploymentInfo($deployment);
        $oldHostSearch = self::getDeploymentHostSearches($deployment);
        $oldDeployStaticHosts = self::getDeploymentStaticHosts($deployment);
        $return =
            self::$ds[self::$dsmodule]->modifyDeployment(
                $deployment, $deployInfo, $deployHostSearch, $deployStaticHosts
            );
        if ($return === true) {
            if ($deployInfo['deploystyle'] == 'commonrepo') {
                self::addCommonRepo($deployment);
            }
            $deployData =
                new DeploymentData(
                    $deployment, $deployInfo, $deployHostSearch, $deployStaticHosts,
                    'modify', $oldDeployInfo, $oldHostSearch, $oldDeployStaticHosts
                );
            self::$log->addToLog($deployData);
            return true;
        }
        return false;
    }

    public static function addDeploymentDynamicHost($deployment, $md5Key, array $hostInfo)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->addDeploymentDynamicHost($deployment, $md5Key, $hostInfo);
        $hostData = new DeploymentHostData($deployment, 'add', 'dynamic', $hostInfo);
        self::$log->addToLog($hostData);
    }

    public static function delDeploymentDynamicHost($deployment, $md5Key)
	{
        if (self::$init === false) self::init();
        $hostInfo = self::$ds[self::$dsmodule]->delDeploymentDynamicHost($deployment, $md5Key);
        if ((isset($hostData)) && (!empty($hostData))) {
            $hostData = new DeploymentHostData($deployment, 'del', 'dynamic', $hostInfo);
            self::$log->addToLog($hostData);
        }
        return $hostInfo;
    }

    public static function addDeploymentStaticHost($deployment, $ip, array $hostInfo)
	{
        if (self::$init === false) self::init();
        self::$ds[self::$dsmodule]->addDeploymentStaticHost($deployment, $ip, $hostInfo);
        $hostData = new DeploymentHostData($deployment, 'add', 'static', $hostInfo);
        self::$log->addToLog($hostData);
    }

    public static function delDeploymentStaticHost($deployment, $ip)
	{
        if (self::$init === false) self::init();
        $hostInfo = self::$ds[self::$dsmodule]->delDeploymentStaticHost($deployment, $ip);
        if ($hostInfo !== false) {
            $hostData = new DeploymentHostData($deployment, 'del', 'static', $hostInfo);
            self::$log->addToLog($hostData);
        }
        return $hostInfo;
    }

    public static function getDeployments()
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeployments();
    }

    public static function existsDeployment($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeployment($deployment);
    }

    public static function getDeploymentInfo($deployment)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentInfo($deployment);
        if ((isset($results['authgroups'])) && (!empty($results['authgroups']))) {
            unset($results['ldapgroups']);
        }
        else {
            $results['authgroups'] = $results['ldapgroups'];
            unset($results['ldapgroups']);
        }
        if ((!isset($results['commonrepo'])) || (empty($results['commonrepo']))) {
            if ($deployment == 'common') {
                $results['commonrepo'] = 'undefined';
            }
            else {
                $results['commonrepo'] = 'common';
            }
        }
        return $results;
    }

    public static function getDeploymentCommonRepo($deployment)
	{
        if (self::$init === false) self::init();
        if ($deployment == 'common') return 'undefined';
        $commonRepo = self::$ds[self::$dsmodule]->getDeploymentCommonRepo($deployment);
        if (($commonRepo === false) || (empty($commonRepo))) {
            return 'common';
        } else {
            return $commonRepo;
        }
    }

    public static function getDeploymentCommonRepos($deployment)
    {
        if (self::$init === false) self::init();
        $cRepos = array();
        $cRepo = self::getDeploymentCommonRepo($deployment);
        array_push($cRepos, $cRepo);
        if ($cRepo != 'common') {
            $subCommonRepos = self::getDeploymentCommonRepos($cRepo);
            if ((isset($subCommonRepos)) && (!empty($subCommonRepos))) {
                $cRepos = array_merge($cRepos, $subCommonRepos);
            }
        }
        return $cRepos;
    }

    public static function getDeploymentHostSearches($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentHostSearches($deployment);
    }

    public static function getDeploymentStaticHosts($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentStaticHosts($deployment);
    }

    public static function getDeploymentHosts($deployment)
	{
        if (self::$init === false) self::init();
        $hostSearches = self::getDeploymentHostSearches($deployment);
        $staticHosts = self::getDeploymentStaticHosts($deployment);
        $miscSettings = self::getDeploymentMiscSettings($deployment);
        $nagHelper = new NagHelpers();
        $nagHelper->setAliasTemplate($miscSettings['aliastemplate']);
        if (!empty($hostSearches)) {
            foreach ($hostSearches as $md5Key => $hsArray) {
                $hsObj = json_decode(json_encode($hsArray), false);
                $module = $hsObj->location;
                if (preg_match("/^RS-(\w+)-(\w+)$/", $module)) {
                    $modObj = new RightScale;
                }
                elseif (preg_match("/^AWSEC2-(\w+)-(\w+)$/", $module)) {
                    $modObj = new AWSEC2;
                }
                else {
                    $modObj = new $module;
                }
                $hostInfo = $modObj->getSearchResults($hsObj);
                foreach ($hostInfo as $host => $hostData) {
                    $nagHelper->importHost($host, $hostData);
                }
            }
        }
        if (!empty($staticHosts)) {
            foreach ($staticHosts as $encIp => $encArray) {
                $nagHelper->importStaticHost($encArray);
            }
        }
        $hosts = $nagHelper->returnHosts();
        return $hosts;
    }

    public static function getDeploymentAuthGroup($deployment)
	{
        if (self::$init === false) self::init();
        if ($deployment == 'common') {
            return SUPERMEN;
        }
        else {
            $results = self::$ds[self::$dsmodule]->getDeploymentAuthGroup($deployment);
            if ($results !== false) {
                return $results;
            }
            else {
                return self::getDeploymentLdapGroup($deployment);
            }
        }
    }

    private static function getDeploymentLdapGroup($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentLdapGroup($deployment);
    }

    public static function getDeploymentAliasTemplate($deployment)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentAliasTemplate($deployment);
        if ((empty($results)) || ($results === false)) {
            return 'host-dc';
        }
        return $results;
    }

    public static function getDeploymentGlobalNegate($deployment)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentGlobalNegate($deployment);
        if ((empty($results)) || ($results === false)) {
            return false;
        }
        return $results;
    }

    public static function getDeploymentStyle($deployment)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentStyle($deployment);
        if ((empty($results)) || ($results === false)) {
            return 'both';
        }
        return $results;
    }

    public static function getDeploymentMiscSettings($deployment)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentMiscSettings($deployment);
    }

    public static function getDeploymentData($deployment, $revision, $jsonEncode = false)
	{
        if (self::$init === false) self::init();
        $results = array();
        $results['timeperiods'] =
            self::getCommonMergedDeploymentTimeperiodswData($deployment, $revision);
        $results['commands'] =
            self::getCommonMergedDeploymentCommands($deployment, $revision, false);
        $results['contacttemplates'] =
            self::getCommonMergedDeploymentContactTemplates($deployment, $revision);
        $results['contacts'] = self::getCommonMergedDeploymentContacts($deployment, $revision);
        $results['contactgroups'] =
            self::getCommonMergedDeploymentContactGroups($deployment, $revision);
        $results['hosttemplates'] =
            self::getCommonMergedDeploymentHostTemplates($deployment, $revision);
        $results['hostgroups'] = self::getCommonMergedDeploymentHostGroups($deployment, $revision);
        $results['servicetemplates'] =
            self::getCommonMergedDeploymentSvcTemplates($deployment, $revision);
        $results['services'] = self::getCommonMergedDeploymentSvcs($deployment, $revision);
        $results['servicegroups'] =
            self::getCommonMergedDeploymentSvcGroups($deployment, $revision);
        $results['servicedependencies'] =
            self::getCommonMergedDeploymentSvcDependencies($deployment, $revision);
        $results['serviceescalations'] =
            self::getDeploymentSvcEscalationswInfo($deployment, $revision);
        $results['nodetemplates'] =
            self::getDeploymentNodeTemplateswInfo($deployment, $revision, true);
        $results['clustercmds'] = self::getDeploymentClusterCmdswInfo($deployment, $revision);
        $results['hostsearches'] = self::getDeploymentHostSearches($deployment);
        $results['statichosts'] = self::getDeploymentStaticHosts($deployment);
        $results['resourcecfg'] = self::getDeploymentResourceCfg($deployment, $revision);
        $results['cgicfg'] = self::getDeploymentCgiCfg($deployment, $revision);
        $results['modgearmancfg'] = self::getDeploymentModgearmanCfg($deployment, $revision);
        $results['nagioscfg'] = self::getDeploymentNagiosCfg($deployment, $revision);
        $results['miscsettings'] = self::getDeploymentMiscSettings($deployment);
        if ($jsonEncode === true) {
            return json_encode($results);
        } else {
            return $results;
        }
    }

    public static function getDeploymentCommands($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentCommands($deployment, $revision);
    }

    public static function getDeploymentCommand($deployment, $command, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentCommand($deployment, $revision, $command);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentCommandExec($deployment, $command, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentCommandExec($deployment, $revision, $command);
    }

    public static function getCommonMergedDeploymentCommand($deployment, $command, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentCommand($deployment, $command, $revision) === true) {
            return self::getDeploymentCommand($deployment, $command, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $commonRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentCommand($commonRepo, $command, $commonRev) === true) {
                        return self::getDeploymentCommand($commonRepo, $command, $commonRev);
                    }
                }
            }
        }
    }

    public static function getCommonMergedDeploymentCommandExec($deployment, $command, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentCommand($deployment, $command, $revision) === true) {
            return self::getDeploymentCommandExec($deployment, $command, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $commonRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentCommand($commonRepo, $command, $commonRev) === true) {
                        return self::getDeploymentCommandExec($commonRepo, $command, $commonRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentCommand(
        $deployment, $command, array $commandInput, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentCommand($deployment, $revision, $command, $commandInput);
        if ($return === true) {
            $deployCmdData =
                new CommandData(
                    $deployment, $revision, $command, $commandInput, 'create' 
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCmdData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentCommand(
        $deployment, $command, array $commandInput, $revision
    ) {
        if (self::$init === false) self::init();
        $oldCmdInfo = self::getDeploymentCommand($deployment, $command, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentCommand($deployment, $revision, $command, $commandInput);
        if ($return === true) {
            $deployCmdData =
                new CommandData(
                    $deployment, $revision, $command, $commandInput, 'modify', $oldCmdInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCmdData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentCommand($deployment, $command, $revision)
	{
        if (self::$init === false) self::init();
        $commandInfo = self::$ds[self::$dsmodule]->deleteDeploymentCommand($deployment, $revision, $command);
        if (!empty($commandInfo)) {
            $deployCmdData =
                new CommandData($deployment, $revision, $command, $commandInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCmdData);
        }
    }

    public static function existsDeploymentCommand($deployment, $command, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentCommand($deployment, $revision, $command);
    }

    public static function getCommonMergedDeploymentNotifyCommands($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $commands = array();
        $deployCmds = self::getDeploymentCommands($deployment, $revision);
        foreach ($deployCmds as $cmd) {
            if (preg_match('/^notify-/', $cmd)) {
                array_push($commands, $cmd);
            }
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonCmds = self::getDeploymentCommands($commonRepo, $commonRev);
                foreach ($commonCmds as $cmd) {
                    if (preg_match('/^notify-/', $cmd)) {
                        if (!in_array($cmd, $commands)) {
                            array_push($commands, $cmd);
                        }
                    }
                }
            }
        }
        asort($commands);
        return $commands;
    }

    public static function getCommonMergedDeploymentHostCheckCommands($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $commands = array();
        $deployCmds = self::getDeploymentCommands($deployment, $revision);
        foreach ($deployCmds as $cmd) {
            if (preg_match('/host-alive$/', $cmd)) {
                array_push($commands, $cmd);
            }
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonCmds = self::getDeploymentCommands($commonRepo, $commonRev);
                foreach ($commonCmds as $cmd) {
                    if (preg_match('/host-alive$/', $cmd)) {
                        if (!in_array($cmd, $commands)) {
                            array_push($commands, $cmd);
                        }
                    }
                }
            }
        }
        asort($commands);
        return $commands;
    }

    public static function getDeploymentCommandswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $commands = array();
        $deployCmds = self::getDeploymentCommands($deployment, $revision);
        if (!empty($deployCmds)) sort($deployCmds);
        foreach ($deployCmds as $cmd) {
            $cmdInfo = self::getDeploymentCommand($deployment, $cmd, $revision);
            if (empty($cmdInfo)) continue;
            $commands[$cmd] = $cmdInfo;
        }
        return $commands;
    }

    public static function getCommonMergedDeploymentCommands(
        $deployment, $revision, $skipAlertsHostChks = true
    ) {
        if (self::$init === false) self::init();
        $commands = array();
        $deployCmds = self::getDeploymentCommands($deployment, $revision);
        if (!empty($deployCmds)) sort($deployCmds);
        foreach ($deployCmds as $cmd) {
            $cmdInfo = self::getDeploymentCommand($deployment, $cmd, $revision);
            if (empty($cmdInfo)) continue;
            $cmdInfo['deployment'] = $deployment;
            $commands[$cmd] = $cmdInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonCmds = self::getDeploymentCommands($commonRepo, $commonRev);
                if (!empty($commonCmds)) sort($commonCmds);
                foreach ($commonCmds as $cmd) {
                    if ((isset($commands[$cmd])) && (!empty($commands[$cmd]))) continue;
                    if ((preg_match('/^notify-|host-alive$/', $cmd)) &&
                        ($skipAlertsHostChks === true)) continue;
                    $cmdInfo = self::getDeploymentCommand($commonRepo, $cmd, $commonRev);
                    if (empty($cmdInfo)) continue;
                    $cmdInfo['deployment'] = $commonRepo;
                    $commands[$cmd] = $cmdInfo;
                }
            }
        }
        return $commands;
    }

    public static function getDeploymentTimeperiods($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentTimeperiods($deployment, $revision);
    }

    public static function existsDeploymentTimeperiod($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentTimeperiod($deployment, $revision, $timePeriod);
    }

    public static function existsDeploymentTimeperiodData($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentTimeperiodData($deployment, $revision, $timePeriod);
    }

    public static function getDeploymentTimeperiodInfo($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentTimeperiodInfo($deployment, $revision, $timePeriod);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentTimeperiodData($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentTimeperiodData($deployment, $revision, $timePeriod);
    }

    public static function getDeploymentTimeperiod($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentTimeperiod($deployment, $revision, $timePeriod);
        if (empty($results)) return false;
        if (!empty($results['times'])) sort($results['times']);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getCommonMergedDeploymentTimeperiodData(
        $deployment, $timePeriod, $revision
    ) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentTimeperiodData($deployment, $revision, $timePeriod) === true) {
            return self::getDeploymentTimeperiodData($deployment, $timePeriod, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentTimeperiodData($commonRepo, $timePeriod, $cRev) === true) {
                        return self::getDeploymentTimeperiodData($commonRepo, $timePeriod, $cRev);
                    }
                }
            }
        }
    }

    public static function getCommonMergedDeploymentTimeperiod($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentTimeperiod($deployment, $timePeriod, $revision) === true) {
            return self::getDeploymentTimeperiod($deployment, $timePeriod, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentTimeperiod($commonRepo, $timePeriod, $cRev) === true) {
                        return self::getDeploymentTimeperiod($commonRepo, $timePeriod, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentTimeperiod(
        $deployment, $timePeriod, array $timePeriodInfo, array $timePeriodData, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentTimeperiod(
                $deployment, $revision, $timePeriod, $timePeriodInfo, $timePeriodData
            );
        if ($results === true) {
            $deployTimeperiodData =
                new TimeperiodData(
                    $deployment, $revision, $timePeriod, $timePeriodInfo, $timePeriodData, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployTimeperiodData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentTimeperiod(
        $deployment, $timePeriod, array $timePeriodInfo, array $timePeriodData, $revision
    ) {
        if (self::$init === false) self::init();
        $oldTimeperiodInfo = self::getDeploymentTimeperiodInfo($deployment, $timePeriod, $revision);
        $oldTimeperiodData = self::getDeploymentTimeperiodData($deployment, $timePeriod, $revision);
        $return = 
            self::$ds[self::$dsmodule]->modifyDeploymentTimeperiod(
                $deployment, $revision, $timePeriod, $timePeriodInfo, $timePeriodData
            );
        if ($return === true) {
            $deployTimeperiodData =
                new TimeperiodData(
                    $deployment, $revision, $timePeriod, $timePeriodInfo,
                    $timePeriodData, 'modify', $oldTimeperiodInfo, $oldTimeperiodData
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployTimeperiodData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentTimeperiod($deployment, $timePeriod, $revision)
	{
        if (self::$init === false) self::init();
        $timePeriodResults =
            self::$ds[self::$dsmodule]->deleteDeploymentTimeperiod($deployment, $revision, $timePeriod);
        if (!empty($timePeriodResults)) {
            $timePeriodInfo = $timePeriodResults;
            unset($timePeriodInfo['times']);
            $timePeriodData = $timePeriodResults['times'];
            $deployTimeperiodData =
                new TimeperiodData(
                    $deployment, $revision, $timePeriod,
                    $timePeriodInfo, $timePeriodData, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployTimeperiodData);
        }
        return $timePeriodResults;
    }

    public static function getDeploymentTimeperiodsMetaInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiodInfo($deployment, $time, $revision);
            if (empty($timePeriod)) continue;
            $timePeriods[$time] = $timePeriod;
        }
        return $timePeriods;
    }

    public static function getCommonMergedDeploymentTimeperiodsMetaInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiodInfo($deployment, $time, $revision);
            if (empty($timePeriod)) continue;
            $timePeriods[$time] = $timePeriod;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonTimes = self::getDeploymentTimeperiods($commonRepo, $commonRev);
                foreach ($commonTimes as $time) {
                    if ((isset($timePeriods[$time])) && (!empty($timePeriods[$time]))) continue;
                    $timePeriod = self::getDeploymentTimeperiodInfo($commonRepo, $time, $commonRev);
                    if (empty($timePeriod)) continue;
                    $timePeriods[$time] = $timePeriod;
                }
            }
        }
        return $timePeriods;
    }

    public static function getDeploymentTimeperiodswData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        if (!empty($deployTimes)) sort($deployTimes);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiod($deployment, $time, $revision);
            if ($timePeriod === false) continue;
            $timePeriods[$time] = $timePeriod;
        }
        return $timePeriods;
    }

    public static function getCommonMergedDeploymentTimeperiodswData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        if (!empty($deployTimes)) sort($deployTimes);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiod($deployment, $time, $revision);
            if ($timePeriod === false) continue;
            $timePeriods[$time] = $timePeriod;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonTimes = self::getDeploymentTimeperiods($commonRepo, $commonRev);
                if (!empty($commonTimes)) sort($commonTimes);
                foreach ($commonTimes as $time) {
                    if ((isset($timePeriods[$time])) && (!empty($timePeriods[$time]))) continue;
                    $timePeriod = self::getDeploymentTimeperiod($commonRepo, $time, $commonRev);
                    if ($timePeriod === false) continue;
                    $timePeriods[$time] = $timePeriod;
                }
            }
        }
        return $timePeriods;
    }

    public static function getDeploymentContacts($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentContacts($deployment, $revision);
    }

    public static function getDeploymentContact($deployment, $contact, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentContact($deployment, $revision, $contact);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function existsDeploymentContact($deployment, $contact, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentContact($deployment, $revision, $contact);
    }

    public static function getCommonMergedDeploymentContact($deployment, $contact, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentContact($deployment, $contact, $revision) === true) {
            return self::getDeploymentContact($deployment, $contact, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentContact($commonRepo, $contact, $cRev) === true) {
                        return self::getDeploymentContact($commonRepo, $contact, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentContact(
        $deployment, $contact, array $contactInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentContact($deployment, $revision, $contact, $contactInfo);
        if ($results === true) {
            $deployContactData =
                new ContactData($deployment, $revision, $contact, $contactInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentContact($deployment, $contact, $revision)
	{
        if (self::$init === false) self::init();
        $contactInfo = self::$ds[self::$dsmodule]->deleteDeploymentContact($deployment, $revision, $contact);
        if (!empty($contactInfo)) {
            $deployContactData =
                new ContactData($deployment, $revision, $contact, $contactInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactData);
        }
    }

    public static function modifyDeploymentContact(
        $deployment, $contact, array $contactInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldContactInfo = self::getDeploymentContact($deployment, $contact, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentContact($deployment, $revision, $contact, $contactInfo);
        if ($return === true) {
            $deployContactData =
                new ContactData(
                    $deployment, $revision, $contact, $contactInfo, 'modify', $oldContactInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactData);
            return true;
        }
        return false;
    }

    public static function getDeploymentContactswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $contacts = array();
        $deployContacts = self::getDeploymentContacts($deployment, $revision);
        if (!empty($deployContacts)) sort($deployContacts);
        foreach ($deployContacts as $contact) {
            $contactArray = self::getDeploymentContact($deployment, $contact, $revision);
            if (empty($contact)) continue;
            $contacts[$contact] = $contactArray;
        }
        return $contacts;
    }

    public static function getCommonMergedDeploymentContacts($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $contacts = array();
        $deployContacts = self::getDeploymentContacts($deployment, $revision);
        if (!empty($deployContacts)) sort($deployContacts);
        foreach ($deployContacts as $contact) {
            $contactArray = self::getDeploymentContact($deployment, $contact, $revision);
            if (empty($contact)) continue;
            $contacts[$contact] = $contactArray;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonContacts = self::getDeploymentContacts($commonRepo, $commonRev);
                if (!empty($commonContacts)) sort($commonContacts);
                foreach ($commonContacts as $contact) {
                    if ((isset($contacts[$contact])) && (!empty($contacts[$contact]))) continue;
                    $contactArray = self::getDeploymentContact($commonRepo, $contact, $commonRev);
                    if (empty($contactArray)) continue;
                    $contacts[$contact] = $contactArray;
                }
            }
        }
        return $contacts;
    }

    public static function getDeploymentContactGroups($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentContactGroups($deployment, $revision);
    }

    public static function getDeploymentContactGroup($deployment, $contactGroup, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentContactGroup($deployment, $revision, $contactGroup);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function existsDeploymentContactGroup($deployment, $contactGroup, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentContactGroup($deployment, $revision, $contactGroup);
    }

    public static function getDeploymentContactGroupswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $cGrpInfo = array();
        $cGrps = self::getDeploymentContactGroups($deployment, $revision);
        foreach ($cGrps as $cGrp) {
            $cGrpInfo[$cGrp] = self::getDeploymentContactGroup($deployment, $cGrp, $revision);
        }
        return $cGrpInfo;
    }

    public static function getCommonMergedDeploymentContactGroup(
        $deployment, $contactGroup, $revision
    ) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentContactGroup($deployment, $contactGroup, $revision) === true) {
            return self::getDeploymentContactGroup($deployment, $contactGroup, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentContactGroup($commonRepo, $contactGroup, $cRev) === true) {
                        return self::getDeploymentContactGroup($commonRepo, $contactGroup, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentContactGroup(
        $deployment, $contactGroup, array $contactGroupInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentContactGroup(
                $deployment, $revision, $contactGroup, $contactGroupInfo
            );
        if ($results === true) {
            $deployContactGroupData =
                new ContactGroupData(
                    $deployment, $revision, $contactGroup, $contactGroupInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactGroupData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentContactGroup(
        $deployment, $contactGroup, array $contactGroupInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldContactGroupInfo =
            self::getDeploymentContactGroup($deployment, $contactGroup, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentContactGroup(
                $deployment, $revision, $contactGroup, $contactGroupInfo
            );
        if ($return === true) {
            $deployContactGroupData =
                new ContactGroupData(
                    $deployment, $revision, $contactGroup,
                    $contactGroupInfo, 'modify', $oldContactGroupInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactGroupData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentContactGroup($deployment, $contactGroup, $revision)
	{
        if (self::$init === false) self::init();
        $contactGroupInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentContactGroup($deployment, $revision, $contactGroup);
        if (!empty($contactGroupInfo)) {
            $deployContactGroupData =
                new ContactGroupData(
                    $deployment, $revision, $contactGroup, $contactGroupInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactGroupData);
        }
    }

    public static function getCommonMergedDeploymentContactGroups($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $contactGroups = array();
        $deployContactGroups = self::getDeploymentContactGroups($deployment, $revision);
        if (!empty($deployContactGroups)) sort($deployContactGroups);
        foreach ($deployContactGroups as $ctemplate) {
            $contactGroup = self::getDeploymentContactGroup($deployment, $ctemplate, $revision);
            if (empty($contactGroup)) continue;
            $contactGroups[$ctemplate] = $contactGroup;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonContactGroups = self::getDeploymentContactGroups($commonRepo, $commonRev);
                if (!empty($commonContactGroups)) sort($commonContactGroups);
                foreach ($commonContactGroups as $ctemplate) {
                    if ((isset($contactGroups[$ctemplate])) &&
                        (!empty($contactGroups[$ctemplate]))) continue;
                    $contactGroup =
                        self::getDeploymentContactGroup($commonRepo, $ctemplate, $commonRev);
                    if (empty($contactGroup)) continue;
                    $contactGroups[$ctemplate] = $contactGroup;
                }
            }
        }
        return $contactGroups;
    }

    public static function getDeploymentContactTemplates($deployment, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentContactTemplates($deployment, $revision);
    }

    public static function existsDeploymentContactTemplate($deployment, $contactTemplate, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentContactTemplate($deployment, $revision, $contactTemplate);
    }

    public static function getDeploymentContactTemplate($deployment, $contactTemplate, $revision)
    {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->getDeploymentContactTemplate($deployment, $revision, $contactTemplate);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getCommonMergedDeploymentContactTemplate(
        $deployment, $contactTemplate, $revision
    ) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentContactTemplate($deployment, $contactTemplate, $revision) === true) {
            return self::getDeploymentContactTemplate($deployment, $contactTemplate, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentContactTemplate($commonRepo, $contactTemplate, $cRev) === true) {
                        return self::getDeploymentContactTemplate($commonRepo, $contactTemplate, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentContactTemplate(
        $deployment, $contactTemplate, array $contactInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentContactTemplate(
                $deployment, $revision, $contactTemplate, $contactInfo
            );
        if ($results === true) {
            $contactTemplateData =
                new ContactTemplateData(
                    $deployment, $revision, $contactTemplate, $contactInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($contactTemplateData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentContactTemplate(
        $deployment, $contactTemplate, array $contactTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldContactInfo =
            self::getDeploymentContactTemplate($deployment, $contactTemplate, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentContactTemplate(
                $deployment, $revision, $contactTemplate, $contactTemplateInfo
            );
        if ($return === true) {
            $contactTemplateData =
                new ContactTemplateData(
                    $deployment, $revision, $contactTemplate,
                    $contactTemplateInfo, 'modify', $oldContactInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($contactTemplateData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentContactTemplate($deployment, $contactTemplate, $revision)
    {
        if (self::$init === false) self::init();
        $contactInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentContactTemplate($deployment, $revision, $contactTemplate);
        if (!empty($contactInfo)) {
            $contactTemplateData =
                new ContactTemplateData(
                    $deployment, $revision, $contactTemplate, $contactInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($contactTemplateData);
        }
        return $contactInfo;
    }

    public static function getDeploymentContactTemplateswInfo($deployment, $revision)
    {
        if (self::$init === false) self::init();
        $contactTemplates = array();
        $deployContactTemplates = self::getDeploymentContactTemplates($deployment, $revision);
        if (!empty($deployContactTemplates)) sort($deployContactTemplates);
        foreach ($deployContactTemplates as $ctemplate) {
            $contactTemplate =
                self::getDeploymentContactTemplate($deployment, $ctemplate, $revision);
            if (empty($contactTemplate)) continue;
            $contactTemplates[$ctemplate] = $contactTemplate;
        }
        return $contactTemplates;
    }

    public static function getCommonMergedDeploymentContactTemplates($deployment, $revision)
    {
        if (self::$init === false) self::init();
        $contactTemplates = array();
        $deployContactTemplates = self::getDeploymentContactTemplates($deployment, $revision);
        if (!empty($deployContactTemplates)) sort($deployContactTemplates);
        foreach ($deployContactTemplates as $ctemplate) {
            $contactTemplate =
                self::getDeploymentContactTemplate($deployment, $ctemplate, $revision);
            if (empty($contactTemplate)) continue;
            $contactTemplates[$ctemplate] = $contactTemplate;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonContactTemplates = self::getDeploymentContactTemplates($commonRepo, $commonRev);
                if (!empty($commonContactTemplates)) sort($commonContactTemplates);
                foreach ($commonContactTemplates as $ctemplate) {
                    if ((isset($contactTemplates[$ctemplate])) &&
                        (!empty($contactTemplates[$ctemplate]))) continue;
                    $contactTemplate =
                        self::getDeploymentContactTemplate($commonRepo, $ctemplate, $commonRev);
                    if (empty($contactTemplate)) continue;
                    $contactTemplates[$ctemplate] = $contactTemplate;
                }
            }
        }
        return $contactTemplates;
    }

    public static function getDeploymentHostTemplates($deployment, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentHostTemplates($deployment, $revision);
    }

    public static function getDeploymentHostTemplate($deployment, $hostTemplate, $revision)
    {
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentHostTemplate($deployment, $revision, $hostTemplate);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function existsDeploymentHostTemplate($deployment, $hostTemplate, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentHostTemplate($deployment, $revision, $hostTemplate);
    }

    public static function getCommonMergedDeploymentHostTemplate(
        $deployment, $hostTemplate, $revision
    ) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentHostTemplate($deployment, $hostTemplate, $revision) === true) {
            return self::getDeploymentHostTemplate($deployment, $hostTemplate, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentHostTemplate($commonRepo, $hostTemplate, $cRev) === true) {
                        return self::getDeploymentHostTemplate($commonRepo, $hostTemplate, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentHostTemplate(
        $deployment, $hostTemplate, array $hostTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentHostTemplate(
                $deployment, $revision, $hostTemplate, $hostTemplateInfo
            );
        if ($results === true) {
            $hostTemplateData =
                new HostTemplateData(
                    $deployment, $revision, $hostTemplate, $hostTemplateInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostTemplateData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentHostTemplate(
        $deployment, $hostTemplate, array $hostTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldHostTemplateInfo =
            self::getDeploymentHostTemplate($deployment, $hostTemplate, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentHostTemplate(
                $deployment, $revision, $hostTemplate, $hostTemplateInfo
            );
        if ($return === true) {
            $hostTemplateData =
                new HostTemplateData(
                    $deployment, $revision, $hostTemplate,
                    $hostTemplateInfo, 'modify', $oldHostTemplateInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostTemplateData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentHostTemplate($deployment, $hostTemplate, $revision)
    {
        if (self::$init === false) self::init();
        $hostTemplateInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentHostTemplate($deployment, $revision, $hostTemplate);
        if (!empty($hostTemplateInfo)) {
            $hostTemplateData =
                new HostTemplateData(
                    $deployment, $revision, $hostTemplate, $hostTemplateInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostTemplateData);
        }
    }

    public static function getDeploymentHostTemplateswInfo($deployment, $revision)
    {
        if (self::$init === false) self::init();
        $hostTemplates = array();
        $deployHostTemplates = self::getDeploymentHostTemplates($deployment, $revision);
        if (!empty($deployHostTemplates)) sort($deployHostTemplates);
        foreach ($deployHostTemplates as $ctemplate) {
            $hostTemplate = self::getDeploymentHostTemplate($deployment, $ctemplate, $revision);
            if (empty($hostTemplate)) continue;
            $hostTemplates[$ctemplate] = $hostTemplate;
        }
        return $hostTemplates;
    }

    public static function getCommonMergedDeploymentHostTemplates($deployment, $revision)
    {
        if (self::$init === false) self::init();
        $hostTemplates = array();
        $deployHostTemplates = self::getDeploymentHostTemplates($deployment, $revision);
        if (!empty($deployHostTemplates)) sort($deployHostTemplates);
        foreach ($deployHostTemplates as $ctemplate) {
            $hostTemplate = self::getDeploymentHostTemplate($deployment, $ctemplate, $revision);
            if (empty($hostTemplate)) continue;
            $hostTemplates[$ctemplate] = $hostTemplate;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonHostTemplates = self::getDeploymentHostTemplates($commonRepo, $commonRev);
                if (!empty($commonHostTemplates)) sort($commonHostTemplates);
                foreach ($commonHostTemplates as $ctemplate) {
                    if ((isset($hostTemplates[$ctemplate])) &&
                        (!empty($hostTemplates[$ctemplate]))) continue;
                    $hostTemplate =
                        self::getDeploymentHostTemplate($commonRepo, $ctemplate, $commonRev);
                    if (empty($hostTemplate)) continue;
                    $hostTemplates[$ctemplate] = $hostTemplate;
                }
            }
        }
        return $hostTemplates;
    }

    public static function getDeploymentHostGroups($deployment, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentHostGroups($deployment, $revision);
    }

    public static function existsDeploymentHostGroup($deployment, $hostGroup, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentHostGroup($deployment, $revision, $hostGroup);
    }

    public static function getDeploymentHostGroup($deployment, $hostGroup, $revision)
    {
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentHostGroup($deployment, $revision, $hostGroup);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getCommonMergedDeploymentHostGroup($deployment, $hostGroup, $revision)
    {
        if (self::$init === false) self::init();
        if (self::existsDeploymentHostGroup($deployment, $hostGroup, $revision) === true) {
            $results = self::getDeploymentHostGroup($deployment, $hostGroup, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentHostGroup($commonRepo, $hostGroup, $cRev) === true) {
                        return self::getDeploymentHostGroup($commonRepo, $hostGroup, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentHostGroup(
        $deployment, $hostGroup, array $hostGrpInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentHostGroup($deployment, $revision, $hostGroup, $hostGrpInfo);
        if ($results === true) {
            $hostGroupData =
                new HostGroupData($deployment, $revision, $hostGroup, $hostGrpInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostGroupData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentHostGroup(
        $deployment, $hostGroup, array $hostGrpInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldHostGrpInfo = self::getDeploymentHostGroup($deployment, $hostGroup, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentHostGroup($deployment, $revision, $hostGroup, $hostGrpInfo);
        if ($return === true) {
            $hostGroupData =
                new HostGroupData(
                    $deployment, $revision, $hostGroup, $hostGrpInfo, 'modify', $oldHostGrpInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostGroupData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentHostGroup($deployment, $hostGroup, $revision)
    {
        if (self::$init === false) self::init();
        $hostGroupInfo = self::$ds[self::$dsmodule]->deleteDeploymentHostGroup($deployment, $revision, $hostGroup);
        if (!empty($hostGroupInfo)) {
            $hostGroupData =
                new HostGroupData($deployment, $revision, $hostGroup, $hostGroupInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostGroupData);
        }
        return $hostGroupInfo;
    }

    public static function getDeploymentHostGroupswInfo($deployment, $revision)
    {
        if (self::$init === false) self::init();
        $hostGroups = array();
        $deployHostGroups = self::getDeploymentHostGroups($deployment, $revision);
        if (!empty($deployHostGroups)) sort($deployHostGroups);
        foreach ($deployHostGroups as $cgroup) {
            $hostGroup = self::getDeploymentHostGroup($deployment, $cgroup, $revision);
            if (empty($hostGroup)) continue;
            $hostGroups[$cgroup] = $hostGroup;
        }
        return $hostGroups;
    }

    public static function getCommonMergedDeploymentHostGroups($deployment, $revision)
    {
        if (self::$init === false) self::init();
        $hostGroups = array();
        $deployHostGroups = self::getDeploymentHostGroups($deployment, $revision);
        if (!empty($deployHostGroups)) sort($deployHostGroups);
        foreach ($deployHostGroups as $cgroup) {
            $hostGroup = self::getDeploymentHostGroup($deployment, $cgroup, $revision);
            if (empty($hostGroup)) continue;
            $hostGroups[$cgroup] = $hostGroup;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonHostGroups = self::getDeploymentHostGroups($commonRepo, $commonRev);
                if (!empty($commonHostGroups)) sort($commonHostGroups);
                foreach ($commonHostGroups as $cgroup) {
                    if ((isset($hostGroups[$cgroup])) && (!empty($hostGroups[$cgroup]))) continue;
                    $hostGroup = self::getDeploymentHostGroup($commonRepo, $cgroup, $commonRev);
                    if (empty($hostGroup)) continue;
                    $hostGroups[$cgroup] = $hostGroup;
                }
            }
        }
        return $hostGroups;
    }

    public static function getDeploymentSvcTemplates($deployment, $revision)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSvcTemplates($deployment, $revision);
    }

    public static function existsDeploymentSvcTemplate($deployment, $svcTemplate, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate);
    }

    public static function getDeploymentSvcTemplate($deployment, $svcTemplate, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentSvcTemplate($deployment, $revision, $svcTemplate);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getCommonMergedDeploymentSvcTemplate(
        $deployment, $svcTemplate, $revision
    ) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentSvcTemplate($deployment, $svcTemplate, $revision) === true) {
            return self::getDeploymentSvcTemplate($deployment, $svcTemplate, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentSvcTemplate($commonRepo, $svcTemplate, $cRev) === true) {
                        return self::getDeploymentSvcTemplate($commonRepo, $svcTemplate, $cRev);
                    }
                }
            }
        }
    }

    public static function createDeploymentSvcTemplate(
        $deployment, $svcTemplate, array $svcTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $results =
            self::$ds[self::$dsmodule]->createDeploymentSvcTemplate(
                $deployment, $revision, $svcTemplate, $svcTemplateInfo
            );
        if ($results === true) {
            $svcTemplateData =
                new ServiceTemplateData(
                    $deployment, $revision, $svcTemplate, $svcTemplateInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcTemplateData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSvcTemplate(
        $deployment, $svcTemplate, array $svcTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldSvcInfo = self::getDeploymentSvcTemplate($deployment, $svcTemplate, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentSvcTemplate(
                $deployment, $revision, $svcTemplate, $svcTemplateInfo
            );
        if ($return === true) {
            $svcTemplateData =
                new ServiceTemplateData(
                    $deployment, $revision, $svcTemplate, $svcTemplateInfo, 'modify', $oldSvcInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcTemplateData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcTemplate($deployment, $svcTemplate, $revision)
	{
        if (self::$init === false) self::init();
        $svcTemplateInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentSvcTemplate($deployment, $revision, $svcTemplate);
        if (!empty($svcTemplateInfo)) {
            $svcTemplateData =
                new ServiceTemplateData(
                    $deployment, $revision, $svcTemplate, $svcTemplateInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcTemplateData);
        }
        return $svcTemplateInfo;
    }

    public static function getDeploymentSvcTemplateswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcTemplates = array();
        $deploySvcTemplates = self::getDeploymentSvcTemplates($deployment, $revision);
        if (!empty($deploySvcTemplates)) sort($deploySvcTemplates);
        foreach ($deploySvcTemplates as $ctemplate) {
            $svcTemplate = self::getDeploymentSvcTemplate($deployment, $ctemplate, $revision);
            if (empty($svcTemplate)) continue;
            $svcTemplates[$ctemplate] = $svcTemplate;
        }
        return $svcTemplates;
    }

    public static function getCommonMergedDeploymentSvcTemplates($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcTemplates = array();
        $deploySvcTemplates = self::getDeploymentSvcTemplates($deployment, $revision);
        if (!empty($deploySvcTemplates)) sort($deploySvcTemplates);
        foreach ($deploySvcTemplates as $ctemplate) {
            $svcTemplate = self::getDeploymentSvcTemplate($deployment, $ctemplate, $revision);
            if (empty($svcTemplate)) continue;
            $svcTemplates[$ctemplate] = $svcTemplate;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonSvcTemplates = self::getDeploymentSvcTemplates($commonRepo, $commonRev);
                if (!empty($commonSvcTemplates)) sort($commonSvcTemplates);
                foreach ($commonSvcTemplates as $ctemplate) {
                    if ((isset($svcTemplates[$ctemplate])) &&
                        (!empty($svcTemplates[$ctemplate]))) continue;
                    $svcTemplate = self::getDeploymentSvcTemplate($commonRepo, $ctemplate, $commonRev);
                    if (empty($svcTemplate)) continue;
                    $svcTemplates[$ctemplate] = $svcTemplate;
                }
            }
        }
        return $svcTemplates;
    }

    public static function getDeploymentSvcGroups($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSvcGroups($deployment, $revision);
    }

    public static function existsDeploymentSvcGroup($deployment, $svcGroup, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSvcGroup($deployment, $revision, $svcGroup);
    }

    public static function getDeploymentSvcGroup($deployment, $svcGroup, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentSvcGroup($deployment, $revision, $svcGroup);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function createDeploymentSvcGroup(
        $deployment, $svcGroup, array $svcGroupInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentSvcGroup($deployment, $revision, $svcGroup, $svcGroupInfo);
        if ($return === true) {
            $svcGroupData =
                new ServiceGroupData($deployment, $revision, $svcGroup, $svcGroupInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcGroupData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSvcGroup(
        $deployment, $svcGroup, array $svcGroupInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldSvcGroupInfo = self::getDeploymentSvcGroup($deployment, $svcGroup, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentSvcGroup($deployment, $revision, $svcGroup, $svcGroupInfo);
        if ($return === true) {
            $svcGroupData =
                new ServiceGroupData(
                    $deployment, $revision, $svcGroup, $svcGroupInfo, 'modify', $oldSvcGroupInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcGroupData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcGroup($deployment, $svcGroup, $revision)
	{
        if (self::$init === false) self::init();
        $svcGroupInfo = self::$ds[self::$dsmodule]->deleteDeploymentSvcGroup($deployment, $revision, $svcGroup);
        if (!empty($svcGroupInfo)) {
            $svcGroupData =
                new ServiceGroupData($deployment, $revision, $svcGroup, $svcGroupInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcGroupData);
        }
        return $svcGroupInfo;
    }

    public static function getDeploymentSvcGroupswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcGroups = array();
        $deploySvcGroups = self::getDeploymentSvcGroups($deployment, $revision);
        if (!empty($deploySvcGroups)) sort($deploySvcGroups);
        foreach ($deploySvcGroups as $cgroup) {
            $svcGroup = self::getDeploymentSvcGroup($deployment, $cgroup, $revision);
            if (empty($svcGroup)) continue;
            $svcGroups[$cgroup] = $svcGroup;
        }
        return $svcGroups;
    }

    public static function getCommonMergedDeploymentSvcGroup($deployment, $svcGroup, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentSvcGroup($deployment, $svcGroup, $revision) === true) {
            return self::getDeploymentSvcGroup($deployment, $svcGroup, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentSvcGroup($commonRepo, $svcGroup, $cRev) === true) {
                        return self::getDeploymentSvcGroup($commonRepo, $svcGroup, $cRev);
                    }
                }
            }
        }
    }

    public static function getCommonMergedDeploymentSvcGroups($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcGroups = array();
        $deploySvcGroups = self::getDeploymentSvcGroups($deployment, $revision);
        if (!empty($deploySvcGroups)) sort($deploySvcGroups);
        foreach ($deploySvcGroups as $cgroup) {
            $svcGroup = self::getDeploymentSvcGroup($deployment, $cgroup, $revision);
            if (empty($svcGroup)) continue;
            $svcGroups[$cgroup] = $svcGroup;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonSvcGroups = self::getDeploymentSvcGroups($commonRepo, $commonRev);
                if (!empty($commonSvcGroups)) sort($commonSvcGroups);
                foreach ($commonSvcGroups as $cgroup) {
                    if ((isset($svcGroups[$cgroup])) && (!empty($svcGroups[$cgroup]))) continue;
                    $svcGroup = self::getDeploymentSvcGroup($commonRepo, $cgroup, $commonRev);
                    if (empty($svcGroup)) continue;
                    $svcGroups[$cgroup] = $svcGroup;
                }
            }
        }
        return $svcGroups;
    }

    public static function getDeploymentSvcs($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSvcs($deployment, $revision);
    }

    public static function existsDeploymentSvc($deployment, $svc, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSvc($deployment, $revision, $svc);
    }

    public static function getDeploymentSvc($deployment, $svc, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentSvc($deployment, $revision, $svc);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function createDeploymentSvc($deployment, $svc, array $svcInfo, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentSvc($deployment, $revision, $svc, $svcInfo);
        if ($return === true) {
            $svcData = new ServiceData($deployment, $revision, $svc, $svcInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSvc($deployment, $svc, array $svcInfo, $revision)
	{
        if (self::$init === false) self::init();
        $oldSvcInfo = self::getDeploymentSvc($deployment, $svc, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentSvc($deployment, $revision, $svc, $svcInfo);
        if ($return === true) {
            $svcData =
                new ServiceData($deployment, $revision, $svc, $svcInfo, 'modify', $oldSvcInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvc($deployment, $svc, $revision)
	{
        if (self::$init === false) self::init();
        $svcInfo = self::$ds[self::$dsmodule]->deleteDeploymentSvc($deployment, $revision, $svc);
        if (!empty($svcInfo)) {
            $svcData = new ServiceData($deployment, $revision, $svc, $svcInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcData);
        }
        return $svcInfo;
    }

    public static function getDeploymentSvcswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcs = array();
        $tmpSvcsInfoArray = array();
        $deploySvcs = self::getDeploymentSvcs($deployment, $revision);
        foreach ($deploySvcs as $cSvc) {
            $svc = self::getDeploymentSvc($deployment, $cSvc, $revision);
            if (empty($svc)) continue;
            $tmpSvcsInfoArray[$cSvc] = $svc;
        }
        asort($deploySvcs);
        foreach ($deploySvcs as $tmpSvc) {
            $svcs[$tmpSvc] = $tmpSvcsInfoArray[$tmpSvc];
        }
        return $svcs;
    }

    public static function getCommonMergedDeploymentSvc($deployment, $svc, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentSvc($deployment, $svc, $revision) === true) {
            return self::getDeploymentSvc($deployment, $svc, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentSvc($commonRepo, $svc, $cRev) === true) {
                        return self::getDeploymentSvc($commonRepo, $svc, $cRev);
                    }
                }
            }
        }
    }

    public static function getCommonMergedDeploymentSvcs($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcs = array();
        $tmpSvcsInfoArray = array();
        $deploySvcs = self::getDeploymentSvcs($deployment, $revision);
        $tmpSvcs = $deploySvcs;
        foreach ($deploySvcs as $cSvc) {
            $svc = self::getDeploymentSvc($deployment, $cSvc, $revision);
            if (empty($svc)) continue;
            $tmpSvcsInfoArray[$cSvc] = $svc;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonSvcs = self::getDeploymentSvcs($commonRepo, $commonRev);
                foreach ($commonSvcs as $cSvc) {
                    if ((isset($tmpSvcsInfoArray[$cSvc])) &&
                        (!empty($tmpSvcsInfoArray[$cSvc]))) continue;
                    array_push($tmpSvcs, $cSvc);
                    $svc = self::getDeploymentSvc($commonRepo, $cSvc, $commonRev);
                    if (empty($svc)) continue;
                    $tmpSvcsInfoArray[$cSvc] = $svc;
                }
            }
        }
        asort($tmpSvcs);
        foreach ($tmpSvcs as $tmpSvc) {
            $svcs[$tmpSvc] = $tmpSvcsInfoArray[$tmpSvc];
        }
        return $svcs;
    }

    public static function getCommonMergedDeploymentSvcsKeyedOnDeployment($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcs = array();
        $deploySvcs = self::getDeploymentSvcs($deployment, $revision);
        foreach ($deploySvcs as $cSvc) {
            $svc = self::getDeploymentSvc($deployment, $cSvc, $revision);
            if (empty($svc)) continue;
            $svcs[$deployment][$cSvc] = $svc;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonSvcs = self::getDeploymentSvcs($commonRepo, $commonRev);
                foreach ($commonSvcs as $cSvc) {
                    if ((isset($svcs[$cSvc])) && (!empty($svcs[$cSvc]))) continue;
                    $svc = self::getDeploymentSvc($commonRepo, $cSvc, $commonRev);
                    if (empty($svc)) continue;
                    $svcs[$commonRepo][$cSvc] = $svc;
                }
            }
        }
        return $svcs;
    }

    public static function getDeploymentSvcDependencies($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSvcDependencies($deployment, $revision);
    }

    public static function existsDeploymentSvcDependency($deployment, $svcDep, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSvcDependency($deployment, $revision, $svcDep);
    }

    public static function getDeploymentSvcDependency($deployment, $svcDep, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentSvcDependency($deployment, $revision, $svcDep);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentSvcDependencieswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $deps = array();
        $deployDeps = self::getDeploymentSvcDependencies($deployment, $revision);
        if (!empty($deployDeps)) sort($deployDeps);
        foreach ($deployDeps as $cDep) {
            $dep = self::getDeploymentSvcDependency($deployment, $cDep, $revision);
            if (empty($dep)) continue;
            $deps[$cDep] = $dep;
        }
        return $deps;
    }

    public static function getCommonMergedDeploymentSvcDependency($deployment, $svcDep, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentSvcDependency($deployment, $svcDep, $revision) === true) { 
            return self::getDeploymentSvcDependency($deployment, $svcDep, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentSvcDependency($commonRepo, $svcDep, $cRev) === true) {
                        return self::getDeploymentSvcDependency($commonRepo, $svcDep, $cRev);
                    }
                }
            }
        }
    }

    public static function getCommonMergedDeploymentSvcDependencies($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $deps = array();
        $deployDeps = self::getDeploymentSvcDependencies($deployment, $revision);
        if (!empty($deployDeps)) sort($deployDeps);
        foreach ($deployDeps as $cDep) {
            $dep = self::getDeploymentSvcDependency($deployment, $cDep, $revision);
            if (empty($dep)) continue;
            $deps[$cDep] = $dep;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonDeps = self::getDeploymentSvcDependencies($commonRepo, $commonRev);
                if (!empty($commonDeps)) sort($commonDeps);
                foreach ($commonDeps as $cDep) {
                    if ((isset($deps[$cDep])) && (!empty($deps[$cDep]))) continue;
                    $dep = self::getDeploymentSvcDependency($commonRepo, $cDep, $commonRev);
                    if (empty($dep)) continue;
                    $deps[$cDep] = $dep;
                }
            }
        }
        return $deps;
    }

    public static function createDeploymentSvcDependency(
        $deployment, $svcDep, array $svcDepInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentSvcDependency($deployment, $revision, $svcDep, $svcDepInfo);
        if ($return === true) {
            $svcDepData =
                new ServiceDependencyData($deployment, $revision, $svcDep, $svcDepInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcDepData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSvcDependency(
        $deployment, $svcDep, array $svcDepInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldSvcDepInfo = self::getDeploymentSvcDependency($deployment, $svcDep, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentSvcDependency($deployment, $revision, $svcDep, $svcDepInfo);
        if ($return === true) {
            $svcDepData =
                new ServiceDependencyData(
                    $deployment, $revision, $svcDep, $svcDepInfo, 'modify', $oldSvcDepInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcDepData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcDependency($deployment, $svcDep, $revision)
	{
        if (self::$init === false) self::init();
        $svcDepInfo = self::$ds[self::$dsmodule]->deleteDeploymentSvcDependency($deployment, $revision, $svcDep);
        if (!empty($svcDepInfo)) {
            $svcDepData =
                new ServiceDependencyData($deployment, $revision, $svcDep, $svcDepInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcDepData);
        }
        return $svcDepInfo;
    }

    public static function getDeploymentSvcEscalations($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSvcEscalations($deployment, $revision);
    }

    public static function existsDeploymentSvcEscalation($deployment, $svcEsc, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSvcEscalation($deployment, $revision, $svcEsc);
    }

    public static function getDeploymentSvcEscalation($deployment, $svcEsc, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentSvcEscalation($deployment, $revision, $svcEsc);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentSvcEscalationswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcEscalations = array();
        $escalations = self::getDeploymentSvcEscalations($deployment, $revision);
        foreach ($escalations as $escalation) {
            $svcEscalations[$escalation] =
                self::getDeploymentSvcEscalation($deployment, $escalation, $revision);
        }
        return $svcEscalations;
    }

    public static function getCommonMergedDeploymentSvcEscalation($deployment, $svcEsc, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentSvcEscalation($deployment, $svcEsc, $revision) === true) {
            return self::getDeploymentSvcEscalation($deployment, $svcEsc, $revision);
        }
        else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $cRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentSvcEscalation($commonRepo, $svcEsc, $cRev) === true) {
                        return self::getDeploymentSvcEscalation($commonRepo, $svcEsc, $cRev);
                    }
                }
            }
        }
    }

    public static function getCommonMergedDeploymentSvcEscalationswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $svcEscalations = array();
        $escalations = self::getDeploymentSvcEscalations($deployment, $revision);
        foreach ($escalations as $escalation) {
            $svcEscalations[$escalation] =
                self::getDeploymentSvcEscalation($deployment, $escalation, $revision);
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $cRev = self::getDeploymentRev($commonRepo);
                $cSvcEscalations = self::getDeploymentSvcEscalations($commonRepo, $cRev);
                foreach ($cSvcEscalations as $cSvcEscalation) {
                    if ((isset($svcEscalations[$cSvcEscalation])) &&
                        (!empty($svcEscalations[$cSvcEscalation]))) continue;
                    $svcEscalations[$cSvcEscalation] =
                        self::getDeploymentSvcEscalation($commonRepo, $cSvcEscalation, $cRev);
                }
            }
        }
        return $svcEscalations;
    }

    public static function createDeploymentSvcEscalation(
        $deployment, $svcEsc, array $svcEscInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentSvcEscalation($deployment, $revision, $svcEsc, $svcEscInfo);
        if ($return === true) {
            $svcEscData =
                new ServiceEscalationData($deployment, $revision, $svcEsc, $svcEscInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcEscData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSvcEscalation(
        $deployment, $svcEsc, array $svcEscInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldSvcEscInfo = self::getDeploymentSvcEscalation($deployment, $svcEsc, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentSvcEscalation($deployment, $revision, $svcEsc, $svcEscInfo);
        if ($return === true) {
            $svcEscData =
                new ServiceEscalationData(
                    $deployment, $revision, $svcEsc, $svcEscInfo, 'modify', $oldSvcEscInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcEscData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcEscalation($deployment, $svcEsc, $revision)
	{
        if (self::$init === false) self::init();
        $svcEscInfo = self::$ds[self::$dsmodule]->deleteDeploymentSvcEscalation($deployment, $revision, $svcEsc);
        if (!empty($svcEscInfo)) {
            $svcEscData =
                new ServiceEscalationData($deployment, $revision, $svcEsc, $svcEscInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcEscData);
        }
        return $svcEscInfo;
    }

    public static function existsDeploymentResourceCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentResourceCfg($deployment, $revision);
    }

    public static function getDeploymentResourceCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentResourceCfg($deployment, $revision);
    }

    public static function writeDeploymentResourceCfg($deployment, array $resources, $revision)
	{
        if (($return = self::existsDeploymentResourceCfg($deployment, $revision)) === true) {
            return self::modifyDeploymentResourceCfg($deployment, $resources, $revision);
        } else {
            return self::createDeploymentResourceCfg($deployment, $resources, $revision);
        }
        return false;
    }

    public static function createDeploymentResourceCfg($deployment, array $resources, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentResourceCfg($deployment, $revision, $resources);
        if ($return === true) {
            $deployResourceData =
                new ResourceConfigData($deployment, $revision, $resources, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployResourceData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentResourceCfg($deployment, array $resources, $revision)
	{
        if (self::$init === false) self::init();
        $oldResources = self::getDeploymentResourceCfg($deployment, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentResourceCfg($deployment, $revision, $resources);
        if ($return === true) {
            $deployResourceData =
                new ResourceConfigData($deployment, $revision, $resources, 'modify', $oldResources);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployResourceData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentResourceCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $oldResourceCfgInfo = self::$ds[self::$dsmodule]->deleteDeploymentResourceCfg($deployment, $revision);
        if (!empty($oldResourceCfgInfo)) {
            $deployResourceData =
                new ResourceConfigData(
                    $deployment, $revision, array(), 'delete', $oldResourceCfgInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployResourceData);
            return true;
        }
        return false;
    }

    public static function existsDeploymentModgearmanCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentModgearmanCfg($deployment, $revision);
    }

    public static function getDeploymentModgearmanCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentModgearmanCfg($deployment, $revision);
    }

    public static function writeDeploymentModgearmanCfg($deployment, array $cfgInfo, $revision)
	{
        if (self::existsDeploymentModgearmanCfg($deployment, $revision)) {
            return self::modifyDeploymentModgearmanCfg($deployment, $cfgInfo, $revision);
        } else {
            return self::createDeploymentModgearmanCfg($deployment, $cfgInfo, $revision);
        }
        return false;
    }

    public static function createDeploymentModgearmanCfg($deployment, array $cfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentModearmanCfg($deployment, $revision, $cfgInfo);
        if ($return === true) {
            $deployModgearmanData =
                new ModgearmanConfigData($deployment, $revision, $cfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployModgearmanData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentModgearmanCfg($deployment, array $cfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $oldCfgInfo = self::getDeploymentModgearmanCfg($deployment, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentModearmanCfg($deployment, $revision, $cfgInfo);
        if ($return === true) {
            $deployModgearmanData =
                new ModgearmanConfigData($deployment, $revision, $cfgInfo, 'modify', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployModgearmanData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentModgearmanCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $oldModgearmanCfgInfo = self::$ds[self::$dsmodule]->deleteDeploymentModgearmanCfg($deployment, $revision);
        if (!empty($oldModgearmanCfgInfo)) { 
            $deployModgearmanData =
                new ModgearmanConfigData(
                    $deployment, $revision, array(), 'delete', $oldModgearmanCfgInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployModgearmanData);
        }
        return $oldModgearmanCfgInfo;
    }

    public static function existsDeploymentCgiCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentCgiCfg($deployment, $revision);
    }

    public static function getDeploymentCgiCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentCgiCfg($deployment, $revision);
    }

    public static function writeDeploymentCgiCfg($deployment, $cfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentCgiCfg($deployment, $revision)) {
            return self::modifyDeploymentCgiCfg($deployment, $cfgInfo, $revision);
        } else {
            return self::createDeploymentCgiCfg($deployment, $cfgInfo, $revision);
        }
        return false;
    }

    public static function createDeploymentCgiCfg($deployment, $cfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentCgiCfg($deployment, $revision, $cfgInfo);
        if ($return === true) {
            $deployCgiData = new CgiConfigData($deployment, $revision, $cfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCgiData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentCgiCfg($deployment, $cfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $oldCfgInfo = self::getDeploymentCgiCfg($deployment, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentCgiCfg($deployment, $revision, $cfgInfo);
        if ($return === true) {
            $deployCgiData =
                new CgiConfigData($deployment, $revision, $cfgInfo, 'modify', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCgiData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentCgiCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $oldCgiCfgInfo = self::$ds[self::$dsmodule]->deleteDeploymentCgiCfg($deployment, $revision);
        if (!empty($oldCgiCfgInfo)) {
            $deployCgiData =
                new CgiConfigData($deployment, $revision, array(), 'delete', $oldCgiCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCgiData);
            return true;
        }
        return false;
    }

    public static function existsDeploymentNagiosCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentNagiosCfg($deployment, $revision);
    }

    public static function getDeploymentNagiosCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentNagiosCfg($deployment, $revision);
        ksort($results);
        return $results;
    }

    public static function writeDeploymentNagiosCfg($deployment, $nagiosInfo, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentNagiosCfg($deployment, $revision)) {
            return self::modifyDeploymentNagiosCfg($deployment, $nagiosInfo, $revision);
        } else {
            return self::createDeploymentNagiosCfg($deployment, $nagiosInfo, $revision);
        }
        return false;
    }

    public static function createDeploymentNagiosCfg($deployment, $nagiosInfo, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentNagiosCfg($deployment, $revision, $nagiosInfo);
        if ($return === true) {
            $deployNagiosData = new NagiosConfigData($deployment, $revision, $nagiosInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNagiosData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentNagiosCfg($deployment, $nagiosInfo, $revision)
	{
        if (self::$init === false) self::init();
        $oldNagiosInfo = self::getDeploymentNagiosCfg($deployment, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentNagiosCfg($deployment, $revision, $nagiosInfo);
        if ($return === true) {
            $deployNagiosData =
                new NagiosConfigData($deployment, $revision, $nagiosInfo, 'modify', $oldNagiosInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNagiosData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNagiosCfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $oldNagiosCfgInfo = self::$ds[self::$dsmodule]->deleteDeploymentNagiosCfg($deployment, $revision);
        if (!empty($oldNagiosCfgInfo)) {
            $deployNagiosData =
                new NagiosConfigData($deployment, $revision, array(), 'delete', $oldNagiosCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNagiosData);
        }
        return $oldNagiosCfgInfo;
    }

    public static function getDeploymentNagiosPlugins($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNagiosPlugins($deployment, $revision);
    }
    
    public static function existsDeploymentNagiosPlugin($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentNagiosPlugin($deployment, $revision, $plugin);
    }

    public static function getDeploymentNagiosPlugin($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentNagiosPlugin($deployment, $revision, $plugin);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentNagiosPluginFileContents($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNagiosPluginFileContents($deployment, $revision, $plugin);
    }

    public static function getCommonMergedDeploymentNagiosPlugin($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentNagiosPlugin($deployment, $plugin, $revision) === true) {
            $results = self::getDeploymentNagiosPlugin($deployment, $plugin, $revision);
            return $results;
        } else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $commonRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentNagiosPlugin($commonRepo, $plugin, $commonRev) === true) {
                        $results = self::getDeploymentNagiosPlugin($commonRepo, $plugin, $commonRev);
                        return $results;
                    }
                }
                return false;
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentNagiosPluginswData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nagiosplugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        $results = array();
        foreach ($nagiosplugins as $nagiosplugin) {
            $results[$nagiosplugin] =
                self::getDeploymentNagiosPlugin($deployment, $nagiosplugin, $revision);
        }
        return $results;
    }

    public static function getCommonMergedDeploymentNagiosPluginsMetaData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        $nagiosplugins = array();
        foreach ($deployNagiosPlugins as $nagiosplugin) {
            $nagiospluginInfo =
                self::getDeploymentNagiosPlugin($deployment, $nagiosplugin, $revision);
            if (empty($nagiospluginInfo)) continue;
            unset($nagiospluginInfo['file']);
            $nagiosplugins[$nagiosplugin] = $nagiospluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonNagiosPlugins = self::getDeploymentNagiosPlugins($commonRepo, $commonRev);
                foreach ($commonNagiosPlugins as $nagiosplugin) {
                    if ((isset($nagiosplugins[$nagiosplugin])) &&
                        (!empty($nagiosplugins[$nagiosplugin]))) continue;
                    $nagiosPluginInfo =
                        self::getDeploymentNagiosPlugin($commonRepo, $nagiosplugin, $commonRev);
                    if (empty($nagiosPluginInfo)) continue;
                    unset($nagiosPluginInfo['file']);
                    $nagiosplugins[$nagiosplugin] = $nagiosPluginInfo;
                }
            }
        }
        return $nagiosplugins;
    }

    public static function getDeploymentNagiosPluginsMetaData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        $nagiosplugins = array();
        foreach ($deployNagiosPlugins as $nagiosplugin) {
            $nagiospluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosplugin, $revision);
            if (empty($nagiospluginInfo)) continue;
            unset($nagiospluginInfo['file']);
            $nagiosplugins[$nagiosplugin] = $nagiospluginInfo;
        }
        return $nagiosplugins;
    }

    public static function getDeploymentNagiosPluginswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nagiosPlugins = array();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        foreach ($deployNagiosPlugins as $nagiosPlugin) {
            $nagiosPluginInfo =
                self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
            if (empty($nagiosPluginInfo)) continue;
            $nagiosPlugins[$nagiosPlugin] = $nagiosPluginInfo;
        }
        return $nagiosPlugins;
    }

    public static function getCommonMergedDeploymentNagiosPlugins($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nagiosPlugins = array();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        foreach ($deployNagiosPlugins as $nagiosPlugin) {
            $nagiosPluginInfo =
                self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
            if (empty($nagiosPluginInfo)) continue;
            $nagiosPlugins[$nagiosPlugin] = $nagiosPluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonNagiosPlugins = self::getDeploymentNagiosPlugins($commonRepo, $commonRev);
                foreach ($commonNagiosPlugins as $nagiosPlugin) {
                    if ((isset($nagiosPlugins[$nagiosPlugin])) &&
                        (!empty($nagiosPlugins[$nagiosPlugin]))) continue;
                    $nagiosPluginInfo =
                        self::getDeploymentNagiosPlugin($commonRepo, $nagiosPlugin, $commonRev);
                    if (empty($nagiosPluginInfo)) continue;
                    $nagiosPlugins[$nagiosPlugin] = $nagiosPluginInfo;
                }
            }
        }
        return $nagiosPlugins;
    }

    public static function createDeploymentNagiosPlugin(
        $deployment, $nagiosPlugin, array $nagiosPluginInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentNagiosPlugin(
                $deployment, $revision, $nagiosPlugin, $nagiosPluginInfo
            );
        if ($return === true) {
            $nagiosPluginData =
                new NagiosPluginData(
                    $deployment, $revision, $nagiosPlugin, $nagiosPluginInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nagiosPluginData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentNagiosPlugin(
        $deployment, $nagiosPlugin, array $nagiosPluginInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldNagiosPluginInfo =
            self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentNagiosPlugin(
                $deployment, $revision, $nagiosPlugin, $nagiosPluginInfo
            );
        if ($return === true) {
            $nagiosPluginData =
                new NagiosPluginData(
                    $deployment, $revision, $nagiosPlugin,
                    $nagiosPluginInfo, 'modify', $oldNagiosPluginInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nagiosPluginData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision)
	{
        if (self::$init === false) self::init();
        $oldNagiosPluginInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin);
        if (!empty($oldNagiosPluginInfo)) {
            $nagiosPluginData =
                new NagiosPluginData(
                    $deployment, $revision, $nagiosPlugin, $oldNagiosPluginInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nagiosPluginData);
        }
        return $oldNagiosPluginInfo;
    }

    public static function getDeploymentNRPECmds($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNRPECmds($deployment, $revision);
    }

    public static function existsDeploymentNRPECmd($deployment, $nrpeCmd, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd);
    }

    public static function getDeploymentNRPECmd($deployment, $nrpeCmd, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentNRPECmd($deployment, $revision, $nrpeCmd);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentNRPECmdLine($deployment, $nrpeCmd, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNRPECmdLine($deployment, $revision, $nrpeCmd);
    }

    public static function getDeploymentNRPECmdswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $results = array();
        $nrpeCmds = self::getDeploymentNRPECmds($deployment, $revision);
        foreach ($nrpeCmds as $nrpeCmd) {
            $results[$nrpeCmd] = self::getDeploymentNrpeCmd($deployment, $nrpeCmd, $revision);
        }
        return $results;
    }

    public static function createDeploymentNRPECmd(
        $deployment, $nrpeCmd, array $nrpeCmdInput, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentNRPECmd($deployment, $revision, $nrpeCmd, $nrpeCmdInput);
        if ($return === true) {
            $deployNRPECmdData =
                new NRPECmdData($deployment, $revision, $nrpeCmd, $nrpeCmdInput, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPECmdData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentNRPECmd(
        $deployment, $nrpeCmd, array $nrpeCmdInput, $revision
    ) {
        if (self::$init === false) self::init();
        $oldNRPECmdInfo = self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentNRPECmd($deployment, $revision, $nrpeCmd, $nrpeCmdInput);
        if ($return === true) {
            $deployNRPECmdData =
                new NRPECmdData(
                    $deployment, $revision, $nrpeCmd, $nrpeCmdInput, 'modify', $oldNRPECmdInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPECmdData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNRPECmd($deployment, $nrpeCmd, $revision)
	{
        if (self::$init === false) self::init();
        $oldNRPECmdInfo = self::$ds[self::$dsmodule]->deleteDeploymentNRPECmd($deployment, $revision, $nrpeCmd);
        if (!empty($oldNRPECmdInfo)) {
            $deployNRPECmdData =
                new NRPECmdData($deployment, $revision, $nrpeCmd, $oldNRPECmdInfo, 'delete');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPECmdData);
        }
        return $oldNRPECmdInfo;
    }

    public static function getCommonMergedDeploymentNRPECmd($deployment, $nrpeCmd, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPECmd($deployment, $nrpeCmd, $revision) === true) {
            return self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
        } else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $commonRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentNRPECmd($commonRepo, $nrpeCmd, $commonRev) === true) {
                        return self::getDeploymentNRPECmd($commonRepo, $nrpeCmd, $commonRev);
                    }
                }
                return false;
            } else {
                return false;
            }
        }
    }

    public static function getCommonMergedDeploymentNRPECmds($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nrpeCmds = array();
        $deployNRPECmds = self::getDeploymentNRPECmds($deployment, $revision);
        foreach ($deployNRPECmds as $nrpeCmd) {
            $nrpeCmdInfo = self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
            if (empty($nrpeCmdInfo)) continue;
            $nrpeCmds[$nrpeCmd] = $nrpeCmdInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonNRPECmds = self::getDeploymentNRPECmds($commonRepo, $commonRev);
                foreach ($commonNRPECmds as $nrpeCmd) {
                    if ((isset($nrpeCmds[$nrpeCmd])) && (!empty($nrpeCmds[$nrpeCmd]))) continue;
                    $nrpeCmdInfo = self::getDeploymentNRPECmd($commonRepo, $nrpeCmd, $commonRev);
                    if (empty($nrpeCmdInfo)) continue;
                    $nrpeCmds[$nrpeCmd] = $nrpeCmdInfo;
                }
            }
        }
        return $nrpeCmds;
    }

    public static function existsDeploymentNRPECfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentNRPECfg($deployment, $revision);
    }

    public static function getDeploymentNRPECfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNRPECfg($deployment, $revision);
    }

    public static function writeDeploymentNRPECfg($deployment, array $nrpeCfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPECfg($deployment, $revision)) {
            return self::modifyDeploymentNRPECfg($deployment, $nrpeCfgInfo, $revision);
        } else {
            return self::createDeploymentNRPECfg($deployment, $nrpeCfgInfo, $revision);
        }
        return false;
    }

    public static function createDeploymentNRPECfg($deployment, array $nrpeCfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentNRPECfg($deployment, $revision, $nrpeCfgInfo);
        if ($return === true) {
            $deployNRPEData = new NRPECfgData($deployment, $revision, $nrpeCfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPEData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentNRPECfg($deployment, array $nrpeCfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $oldNRPECfgInfo = self::getDeploymentNRPECfg($deployment, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentNRPECfg($deployment, $revision, $nrpeCfgInfo);
        if ($return === true) {
            $deployNRPEData =
                new NRPECfgData(
                    $deployment, $revision, $nrpeCfgInfo, 'modify', $oldNRPECfgInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPEData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNRPECfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $oldNRPECfgInfo = self::$ds[self::$dsmodule]->deleteDeploymentNRPECfg($deployment, $revision);
        if (!empty($oldNRPECfgInfo)) {
            $deployNRPEData =
                new NRPECfgData($deployment, $revision, array(), 'delete', $oldNRPECfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPEData);
        }
        return $oldNRPECfgInfo;
    }

    public static function importDeploymentNRPECfg($deployment, $revision, $location, $fileInfo)
	{
        if (self::$init === false) self::init();
        $cfgInfo = array();
        foreach ($fileInfo['cmds'] as $cmd => $cmdline) {
            $cmdInfo = array();
            $cmdInfo['cmd_name'] = $cmd;
            $cmdInfo['cmd_desc'] = $cmd;
            $cmdInfo['cmd_line'] = base64_encode($cmdline);
            if (self::existsDeploymentNRPECmd($deployment, $cmd, $revision) === false) {
                self::createDeploymentNRPECmd($deployment, $cmd, $cmdInfo, $revision);
            } else {
                self::modifyDeploymentNRPECmd($deployment, $cmd, $cmdInfo, $revision);
            }
        }
        $cfgInfo = $fileInfo['meta'];
        $cfgInfo['location'] = $location;
        self::writeDeploymentNRPECfg($deployment, $cfgInfo, $revision);
    }

    public static function existsDeploymentSupNRPECfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSupNRPECfg($deployment, $revision);
    }

    public static function getDeploymentSupNRPECfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSupNRPECfg($deployment, $revision);
    }

    public static function writeDeploymentSupNRPECfg($deployment, array $supNRPECfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentSupNRPECfg($deployment, $revision)) {
            return self::modifyDeploymentSupNRPECfg($deployment, $supNRPECfgInfo, $revision);
        } else {
            return self::createDeploymentSupNRPECfg($deployment, $supNRPECfgInfo, $revision);
        }
        return false;
    }

    public static function createDeploymentSupNRPECfg($deployment, array $supNRPECfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $return = self::$ds[self::$dsmodule]->createDeploymentSupNRPECfg($deployment, $revision, $supNRPECfgInfo);
        if ($return === true) {
            $deploySupNRPEData =
                new SupNRPECfgData($deployment, $revision, $supNRPECfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deploySupNRPEData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSupNRPECfg($deployment, array $supNRPECfgInfo, $revision)
	{
        if (self::$init === false) self::init();
        $oldsupNRPECfgInfo = self::getDeploymentSupNRPECfg($deployment, $revision);
        $return = self::$ds[self::$dsmodule]->modifyDeploymentSupNRPECfg($deployment, $revision, $supNRPECfgInfo);
        if ($return === true) {
            $deploySupNRPEData =
                new SupNRPECfgData(
                    $deployment, $revision, $supNRPECfgInfo, 'modify', $oldsupNRPECfgInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deploySupNRPEData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSupNRPECfg($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $oldSupNRPECfgInfo = self::$ds[self::$dsmodule]->deleteDeploymentSupNRPECfg($deployment, $revision);
        if (!empty($oldSupNRPECfgInfo)) {
            $deploySupNRPEData =
                new SupNRPECfgData($deployment, $revision, array(), 'delete', $oldSupNRPECfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deploySupNRPEData);
        }
        return $oldSupNRPECfgInfo;
    }

    public static function importDeploymentSupNRPECfg($deployment, $revision, $location, $fileInfo)
	{
        if (self::$init === false) self::init();
        $cfgInfo = array();
        foreach ($fileInfo as $cmd => $cmdline) {
            $cmdInfo = array();
            $cmdInfo['cmd_name'] = $cmd;
            $cmdInfo['cmd_desc'] = $cmd;
            $cmdInfo['cmd_line'] = base64_encode($cmdline);
            if (self::existsDeploymentNRPECmd($deployment, $cmd, $revision) === false) {
                self::createDeploymentNRPECmd($deployment, $cmd, $cmdInfo, $revision);
            } else {
                self::modifyDeploymentNRPECmd($deployment, $cmd, $cmdInfo, $revision);
            }
        }
        $cfgInfo['location'] = $location;
        self::writeDeploymentSupNRPECfg($deployment, $cfgInfo, $revision);
    }

    public static function getDeploymentNRPEPlugins($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNRPEPlugins($deployment, $revision);
    }
    
    public static function existsDeploymentNRPEPlugin($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentNRPEPlugin($deployment, $revision, $plugin);
    }

    public static function getDeploymentNRPEPlugin($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentNRPEPlugin($deployment, $revision, $plugin);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentNRPEPluginFileContents($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNRPEPluginFileContents($deployment, $revision, $plugin);
    }

    public static function getCommonMergedDeploymentNRPEPlugin($deployment, $plugin, $revision)
	{
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPEPlugin($deployment, $plugin, $revision) === true) {
            $results = self::getDeploymentNRPEPlugin($deployment, $plugin, $revision);
            return $results;
        } else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $commonRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentNRPEPlugin($commonRepo, $plugin, $commonRev) === true) {
                        $results = self::getDeploymentNRPEPlugin($commonRepo, $plugin, $commonRev);
                        return $results;
                    }
                }
                return false;
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentNRPEPluginswData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nrpePlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        $results = array();
        foreach ($nrpePlugins as $nrpePlugin) {
            $results[$nrpePlugin] =
                self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
        }
        return $results;
    }

    public static function getDeploymentNRPEPluginsMetaData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $deployNRPEPlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        $nrpePlugins = array();
        foreach ($deployNRPEPlugins as $nrpePlugin) {
            $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
            if (empty($nrpePluginInfo)) continue;
            unset($nrpePluginInfo['file']);
            $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
        }
        return $nrpePlugins;
    }

    public static function getCommonMergedDeploymentNRPEPluginsMetaData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $deployNRPEPlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        $nrpePlugins = array();
        foreach ($deployNRPEPlugins as $nrpePlugin) {
            $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
            if (empty($nrpePluginInfo)) continue;
            unset($nrpePluginInfo['file']);
            $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonNRPEPlugins = self::getDeploymentNRPEPlugins($commonRepo, $commonRev);
                foreach ($commonNRPEPlugins as $nrpePlugin) {
                    if ((isset($nrpePlugins[$nrpePlugin])) &&
                        (!empty($nrpePlugins[$nrpePlugin]))) continue;
                    $nrpePluginInfo =
                        self::getDeploymentNRPEPlugin($commonRepo, $nrpePlugin, $commonRev);
                    if (empty($nrpePluginInfo)) continue;
                    unset($nrpePluginInfo['file']);
                    $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
                }
            }
        }
        return $nrpePlugins;
    }

    public static function getDeploymentNRPEPluginswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nrpePlugins = array();
        $deployNRPEPlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        foreach ($deployNRPEPlugins as $nrpePlugin) {
            $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
            if (empty($nrpePluginInfo)) continue;
            $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
        }
        return $nrpePlugins;
    }

    public static function getCommonMergedDeploymentNRPEPlugins($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nrpePlugins = array();
        $deployNRPEPlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        foreach ($deployNRPEPlugins as $nrpePlugin) {
            $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
            if (empty($nrpePluginInfo)) continue;
            $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonNRPEPlugins = self::getDeploymentNRPEPlugins($commonRepo, $commonRev);
                foreach ($commonNRPEPlugins as $nrpePlugin) {
                    if ((isset($nrpePlugins[$nrpePlugin])) &&
                        (!empty($nrpePlugins[$nrpePlugin]))) continue;
                    $nrpePluginInfo =
                        self::getDeploymentNRPEPlugin($commonRepo, $nrpePlugin, $commonRev);
                    if (empty($nrpePluginInfo)) continue;
                    $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
                }
            }
        }
        return $nrpePlugins;
    }

    public static function createDeploymentNRPEPlugin(
        $deployment, $nrpePlugin, array $nrpePluginInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentNRPEPlugin(
                $deployment, $revision, $nrpePlugin, $nrpePluginInfo
            );
        if ($return === true) {
            $nrpePluginData =
                new NRPEPluginData($deployment, $revision, $nrpePlugin, $nrpePluginInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nrpePluginData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentNRPEPlugin(
        $deployment, $nrpePlugin, array $nrpePluginInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldNRPEPluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentNRPEPlugin(
                $deployment, $revision, $nrpePlugin, $nrpePluginInfo
            );
        if ($return === true) {
            $nrpePluginData =
                new NRPEPluginData(
                    $deployment, $revision, $nrpePlugin,
                    $nrpePluginInfo, 'modify', $oldNRPEPluginInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nrpePluginData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision)
	{
        if (self::$init === false) self::init();
        $oldNRPEPluginInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin);
        if (!empty($oldNRPEPluginInfo)) {
            $nrpePluginData =
                new NRPEPluginData(
                    $deployment, $revision, $nrpePlugin, $oldNRPEPluginInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nrpePluginData);
        }
        return $oldNRPEPluginInfo;
    }

    public static function getDeploymentSupNRPEPlugins($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentSupNRPEPlugins($deployment, $revision);
    }
    
    public static function existsDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin);
    }

    public static function getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentSupNRPEPluginFileContents(
        $deployment, $supNRPEPlugin, $revision
    ) {
        if (self::$init === false) self::init();
        return
            self::$ds[self::$dsmodule]->getDeploymentSupNRPEPluginFileContents(
                $deployment, $revision, $supNRPEPlugin
            );
    }

    public static function getCommonMergedDeploymentSupNRPEPlugin(
        $deployment, $supNRPEPlugin, $revision
    ) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision) === true) {
            $results = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            return $results;
        } else {
            if ($deployment != 'common') {
                $commonRepos = self::getDeploymentCommonRepos($deployment);
                foreach ($commonRepos as $commonRepo) {
                    $commonRev = self::getDeploymentRev($commonRepo);
                    if (self::existsDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev)) {
                        return self::getDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev);
                    }     
                }
                return false;
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentSupNRPEPluginswData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $nrpePlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        $results = array();
        foreach ($nrpePlugins as $nrpePlugin) {
            $results[$nrpePlugin] =
                self::getDeploymentSupNRPEPlugin($deployment, $nrpePlugin, $revision);
        }
        return $results;
    }

    public static function getDeploymentSupNRPEPluginsMetaData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo =
                self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            unset($supNRPEPluginInfo['file']);
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        return $supNRPEPlugins;
    }

    public static function getCommonMergedDeploymentSupNRPEPluginsMetaData($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo =
                self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            unset($supNRPEPluginInfo['file']);
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonSupNRPEPlugins = self::getDeploymentSupNRPEPlugins($commonRepo, $commonRev);
                foreach ($commonSupNRPEPlugins as $supNRPEPlugin) {
                    if ((isset($supNRPEPlugins[$supNRPEPlugin])) &&
                        (!empty($supNRPEPlugins[$supNRPEPlugin]))) continue;
                    $supNRPEPluginInfo =
                        self::getDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev);
                    if (empty($supNRPEPluginInfo)) continue;
                    unset($supNRPEPluginInfo['file']);
                    $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
                }
            }
        }
        return $supNRPEPlugins;
    }

    public static function getDeploymentSupNRPEPluginswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo =
                self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        return $supNRPEPlugins;
    }

    public static function getCommonMergedDeploymentSupNRPEPlugins($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo =
                self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            $supNRPEPluginInfo['deployment'] = $deployment;
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $commonRev = self::getDeploymentRev($commonRepo);
                $commonSupNRPEPlugins = self::getDeploymentSupNRPEPlugins($commonRepo, $commonRev);
                foreach ($commonSupNRPEPlugins as $supNRPEPlugin) {
                    if ((isset($supNRPEPlugins[$supNRPEPlugin])) &&
                        (!empty($supNRPEPlugins[$supNRPEPlugin]))) continue;
                    $supNRPEPluginInfo =
                        self::getDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev);
                    if (empty($supNRPEPluginInfo)) continue;
                    $supNRPEPluginInfo['deployment'] = $commonRepo;
                    $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
                }
            }
        }
        return $supNRPEPlugins;
    }

    public static function createDeploymentSupNRPEPlugin(
        $deployment, $supNRPEPlugin, array $supNRPEPluginInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentSupNRPEPlugin(
                $deployment, $revision, $supNRPEPlugin, $supNRPEPluginInfo
            );
        if ($return === true) {
            $supNRPEPluginData =
                new SupNRPEPluginData(
                    $deployment, $revision, $supNRPEPlugin, $supNRPEPluginInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($supNRPEPluginData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentSupNRPEPlugin(
        $deployment, $supNRPEPlugin, array $supNRPEPluginInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldSupNRPEPluginInfo =
            self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentSupNRPEPlugin(
                $deployment, $revision, $supNRPEPlugin, $supNRPEPluginInfo
            );
        if ($return === true) {
            $supNRPEPluginData =
                new SupNRPEPluginData(
                    $deployment, $revision, $supNRPEPlugin,
                    $supNRPEPluginInfo, 'modify', $oldSupNRPEPluginInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($supNRPEPluginData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision)
	{
        if (self::$init === false) self::init();
        $oldSupNRPEPluginInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin);
        if (!empty($oldSupNRPEPluginInfo)) {
            $supNRPEPluginData =
                new SupNRPEPluginData(
                    $deployment, $revision, $supNRPEPlugin, $oldSupNRPEPluginInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($supNRPEPluginData);
        }
        return $oldSupNRPEPluginInfo;
    }

    public static function getDeploymentClusterCmds($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentClusterCmds($deployment, $revision);
    }

    public static function existsDeploymentClusterCmd($deployment, $clusterCmd, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd);
    }

    public static function getDeploymentClusterCmd($deployment, $clusterCmd, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentClusterCmd($deployment, $revision, $clusterCmd);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentClusterCmdLine($deployment, $clusterCmd, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentClusterCmdLine($deployment, $revision, $clusterCmd);
    }

    public static function getDeploymentClusterCmdswInfo($deployment, $revision)
	{
        if (self::$init === false) self::init();
        $results = array();
        $clusterCmds = self::getDeploymentClusterCmds($deployment, $revision);
        foreach ($clusterCmds as $clusterCmd) {
            $results[$clusterCmd] =
                self::getDeploymentClusterCmd($deployment, $clusterCmd, $revision);
        }
        return $results;
    }

    public static function createDeploymentClusterCmd(
        $deployment, $clusterCmd, array $clusterCmdInput, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentClusterCmd(
                $deployment, $revision, $clusterCmd, $clusterCmdInput
            );
        if ($return === true) {
            $deployClusterCmdData =
                new ClusterCmdsData(
                    $deployment, $revision, $clusterCmd, $clusterCmdInput, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployClusterCmdData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentClusterCmd(
        $deployment, $clusterCmd, array $clusterCmdInput, $revision
    ) {
        if (self::$init === false) self::init();
        $oldClusterCmdInfo = self::getDeploymentClusterCmd($deployment, $clusterCmd, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentClusterCmd(
                $deployment, $revision, $clusterCmd, $clusterCmdInput
            );
        if ($return === true) {
            $deployClusterCmdData =
                new ClusterCmdsData(
                    $deployment, $revision, $clusterCmd,
                    $clusterCmdInput, 'modify', $oldClusterCmdInfo
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployClusterCmdData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentClusterCmd($deployment, $clusterCmd, $revision)
	{
        if (self::$init === false) self::init();
        $oldClusterCmdInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentClusterCmd($deployment, $revision, $clusterCmd);
        if (!empty($oldClusterCmdInfo)) {
            $deployClusterCmdData =
                new ClusterCmdsData(
                    $deployment, $revision, $clusterCmd, $oldClusterCmdInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployClusterCmdData);
        }
        return $oldClusterCmdInfo;
    }

    public static function getDeploymentNodeTemplates($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getDeploymentNodeTemplates($deployment, $revision);
    }

    public static function existsDeploymentNodeTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate);
    }

    public static function getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentNodeTemplate($deployment, $revision, $nodeTemplate);
        if (empty($results)) {
            return $results;
        }
        if (((!isset($results['priority'])) || (empty($results['priority']))) &&
            (($results['type'] != 'standard') && ($results['type'] != 'unclassified'))) {
                $results['priority'] = 1;
        }
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentNodeTemplatewInfo(
        $deployment, $nodeTemplate, $revision, $templatemerge = false
    ) {
        if (self::$init === false) self::init();
        $stdtemplates = self::getDeploymentStandardTemplateswInfo($deployment, $revision, true);
        $templateInfo = self::getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision);
        if (($templateInfo['type'] == 'standard') && ($templatemerge !== false)) {
            continue;
        }
        if ($templatemerge !== false) {
            if ((isset($templateInfo['stdtemplate'])) && (!empty($templateInfo['stdtemplate']))) {
                $stdtemplate = $stdtemplates[$templateInfo['stdtemplate']];
                if ((!isset($templateInfo['hosttemplate'])) ||
                    (empty($templateInfo['hosttemplate']))) {
                    if ((isset($stdtemplate['hosttemplate'])) &&
                        (!empty($stdtemplate['hosttemplate']))) {
                        $templateInfo['hosttemplate'] = $stdtemplate['hosttemplate'];
                    }
                }
                if ((isset($stdtemplate['services'])) && (!empty($stdtemplate['services']))) {
                    if (!empty($templateInfo['nservices'])) {
                        $nstdsvcs = $templateInfo['nservices'];
                        unset($templateInfo['nservices']);
                    } else {
                        $nstdsvcs = array();
                    }
                    $svcdiff = array_values(array_diff($stdtemplate['services'], $nstdsvcs));
                    if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
                        $templateInfo['services'] =
                            array_merge($templateInfo['services'], $svcdiff);
                    } else {
                        $templateInfo['services'] = $svcdiff;
                    }
                }
                unset($templateInfo['stdtemplate']);
            }
            elseif ((isset($templateInfo['stdtemplate'])) &&
                (empty($templateInfo['stdtemplate']))) {
                unset($templateInfo['stdtemplate']);
            }
        }
        return $templateInfo;
    }

    public static function getDeploymentNodeTemplateswInfo(
        $deployment, $revision, $templatemerge = false
    ) {
        if (self::$init === false) self::init();
        $results = array();
        $templates = self::getDeploymentNodeTemplates($deployment, $revision);
        $stdtemplates = self::getDeploymentStandardTemplateswInfo($deployment, $revision, true);
        if (!empty($templates)) sort($templates);
        foreach ($templates as $template) {
            $templateInfo = self::getDeploymentNodeTemplate($deployment, $template, $revision);
            if (empty($templateInfo)) {
                continue;
            }
            elseif (($templateInfo['type'] == 'standard') && ($templatemerge !== false)) {
                continue;
            }
            if ($templatemerge !== false) {
                if ((isset($templateInfo['stdtemplate'])) &&
                    (!empty($templateInfo['stdtemplate']))) {
                    $stdtemplate = $stdtemplates[$templateInfo['stdtemplate']];
                    if ((!isset($templateInfo['hosttemplate'])) ||
                        (empty($templateInfo['hosttemplate']))) {
                        if ((isset($stdtemplate['hosttemplate'])) &&
                            (!empty($stdtemplate['hosttemplate']))) {
                            $templateInfo['hosttemplate'] = $stdtemplate['hosttemplate'];
                        }
                    }
                    if ((isset($stdtemplate['services'])) && (!empty($stdtemplate['services']))) {
                        if (!empty($templateInfo['nservices'])) {
                            $nstdsvcs = $templateInfo['nservices'];
                            unset($templateInfo['nservices']);
                        } else {
                            $nstdsvcs = array();
                        }
                        $svcdiff = array_values(array_diff($stdtemplate['services'], $nstdsvcs));
                        if ((isset($templateInfo['services'])) &&
                            (!empty($templateInfo['services']))) {
                            $templateInfo['services'] =
                                array_merge($templateInfo['services'], $svcdiff);
                        } else {
                            $templateInfo['services'] = $svcdiff;
                        }
                    }
                    unset($templateInfo['stdtemplate']);
                }
                elseif ((isset($templateInfo['stdtemplate'])) &&
                    (empty($templateInfo['stdtemplate']))) {
                    unset($templateInfo['stdtemplate']);
                }
            }
            $results[$template] = $templateInfo;
        }
        return $results;
    }

    public static function createDeploymentNodeTemplate(
        $deployment, $nodeTemplate, array $nodeTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $return =
            self::$ds[self::$dsmodule]->createDeploymentNodeTemplate(
                $deployment, $revision, $nodeTemplate, $nodeTemplateInfo
            );
        if ($return === true) {
            $nodeTemplateData =
                new NodeTemplateData(
                    $deployment, $revision, $nodeTemplate, $nodeTemplateInfo, 'create'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nodeTemplateData);
            return true;
        }
        return false;
    }

    public static function modifyDeploymentNodeTemplate(
        $deployment, $nodeTemplate, array $nodeTemplateInfo, $revision
    ) {
        if (self::$init === false) self::init();
        $oldNodeTemplateData =
            self::getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision);
        $return =
            self::$ds[self::$dsmodule]->modifyDeploymentNodeTemplate(
                $deployment, $revision, $nodeTemplate, $nodeTemplateInfo
            );
        if ($return === true) {
            $nodeTemplateData =
                new NodeTemplateData(
                    $deployment, $revision, $nodeTemplate,
                    $nodeTemplateInfo, 'modify', $oldNodeTemplateData
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nodeTemplateData);
            return true;
        }
        return false;
    }
    
    public static function deleteDeploymentNodeTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        $nodeTemplateInfo =
            self::$ds[self::$dsmodule]->deleteDeploymentNodeTemplate($deployment, $revision, $nodeTemplate);
        if (!empty($nodeTemplateInfo)) {
            if ((isset($nodeTemplateInfo['type'])) && (!empty($nodeTemplateInfo['type']))) {
                if ($nodeTemplateInfo['type'] == 'standard') {
                    self::removeDeploymentStandardTemplate($deployment, $nodeTemplate, $revision);
                } elseif ($nodeTemplateInfo['type'] == 'unclassified') {
                    self::removeDeploymentUnclassifiedTemplate($deployment, $revision);
                }
            }
            $nodeTemplateData =
                new NodeTemplateData(
                    $deployment, $revision, $nodeTemplate, $nodeTemplateInfo, 'delete'
                );
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nodeTemplateData);
        }
        return $nodeTemplateInfo;
    }

    public static function checkDeploymentNodeTemplateType(
        $deployment, $nodeTemplate, $revision, $type
    ) {
        if (self::$init === false) self::init();
        $nodeTemplateType =
            self::$ds[self::$dsmodule]->getDeploymentNodeTemplateType($deployment, $revision, $nodeTemplate);
        if ((!isset($nodeTemplateType)) || (empty($nodeTemplateType))) {
            return false;
        }
        elseif ($nodeTemplateType == $type) {
            return true;
        } else {
            return false;
        }
    }

    public static function addDeploymentUnclassifiedTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->addDeploymentUnclassifiedTemplate($deployment, $revision, $nodeTemplate);
    }

    public static function removeDeploymentUnclassifiedTemplate($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->removeDeploymentUnclassifiedTemplate($deployment, $revision);
    }

    public static function existsDeploymentUnclassifiedTemplate($deployment, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentUnclassifiedTemplate($deployment, $revision);
    }

    public static function addDeploymentStandardTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->addDeploymentStandardTemplate($deployment, $revision, $nodeTemplate);
    }

    public static function removeDeploymentStandardTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->removeDeploymentStandardTemplate($deployment, $revision, $nodeTemplate);
    }

    public static function existsDeploymentStandardTemplate($deployment, $nodeTemplate, $revision)
	{
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsDeploymentStandardTemplate($deployment, $revision, $nodeTemplate);
    }

    public static function getDeploymentStandardTemplates(
        $deployment, $revision, $commonmerge = false
    ) {
        if (self::$init === false) self::init();
        $results = self::$ds[self::$dsmodule]->getDeploymentStandardTemplates($deployment, $revision);
        if ($deployment != 'common') {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $cRev = self::getDeploymentRev($commonRepo);
                $cResults = self::getDeploymentStandardTemplates($commonRepo, $cRev, true);
                foreach ($cResults as $cResult) {
                    if (!in_array($cResult, $results)) {
                        array_push($results, $cResult);
                    }
                }
            }
        }
        return $results;
    }

    public static function getDeploymentStandardTemplateswInfo(
        $deployment, $revision, $commonmerge = false
    ) {
        if (self::$init === false) self::init();
        $results = array();
        $deployResults = self::getDeploymentStandardTemplates($deployment, $revision, $commonmerge);
        foreach ($deployResults as $deploystd) {
            $results[$deploystd] =
                self::getDeploymentNodeTemplate($deployment, $deploystd, $revision);
        }
        if (($deployment != 'common') && ($commonmerge !== false)) {
            $commonRepos = self::getDeploymentCommonRepos($deployment);
            foreach ($commonRepos as $commonRepo) {
                $cRev = self::getDeploymentRev($commonRepo);
                $cResults = self::getDeploymentStandardTemplates($commonRepo, $cRev, false);
                foreach ($cResults as $cResult) {
                    if ((isset($results[$cResult])) && (!empty($results[$cResult]))) continue;
                    $results[$cResult] =
                        self::getDeploymentNodeTemplate($commonRepo, $cResult, $cRev);
                }
            }
        }
        return $results;
    }

    public static function existsConsumerDeploymentLock($deployment, $revision, $lockType)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsConsumerDeploymentLock($deployment, $revision, $lockType);
    }

    public static function createConsumerDeploymentLock(
        $deployment, $revision, $lockType, $ttl = 900
    ) {
        if (self::$init === false) self::init();
        if (self::existsConsumerDeploymentLock($deployment, $revision, $lockType) === true) {
            return false;
        }
        return self::$ds[self::$dsmodule]->createConsumerDeploymentLock($deployment, $revision, $lockType, $ttl);
    }

    public static function deleteConsumerDeploymentLock($deployment, $revision, $lockType)
    {
        if (self::$init === false) self::init();
        if (self::existsConsumerDeploymentLock($deployment, $revision, $lockType) === false) {
            return true;
        }
        return self::$ds[self::$dsmodule]->deleteConsumerDeploymentLock($deployment, $revision, $lockType);
    }

    public static function setConsumerDeploymentInfo($deployment, $revision, $infoType, $info)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->setConsumerDeploymentInfo($deployment, $revision, $infoType, $info);
    }

    public static function getConsumerDeploymentInfo($deployment, $revision, $infoType)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getConsumerDeploymentInfo($deployment, $revision, $infoType);
    }

    public static function getCDCRouterZones()
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->getCDCRouterZones();
    }

    public static function existsCDCRouterZone($zone)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->existsCDCRouterZone($zone);
    }

    public static function getCDCRouterZone($zone)
    {
        if (self::$init === false) self::init();
        if (self::existsCDCRouterZone($zone) === true) {
            return self::$ds[self::$dsmodule]->getCDCRouterZone($zone);
        }
        return false;
    }

    public static function writeCDCRouterZones(array $zoneData)
    {
        if (self::$init === false) self::init();
        return self::$ds[self::$dsmodule]->writeCDCRouterZones($zoneData);
    }

}
