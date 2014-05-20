<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RevDeploy {

    protected static $init = false;
    protected static $log;

    public static function init() {
        /* Initial Redis Information */
        if (self::$init === false) {
            NagRedis::init();
            self::$log = new NagLogger();
            self::$init = true;
        }
        return;
    }

    private static function addAuditUserLog($deployment, $revision) {
        if (self::$init === false) self::init();
        $amodule = AUTH_MODULE;
        $user = $amodule::getUser();
        NagRedis::sAdd(md5('deployment-audit:'.$deployment).':'.$revision.':users', $user);
    }

    public static function getAuditLog($deployment, $revisions = false) {
        if (self::$init === false) self::init();
        if ($revisions === false) $revisions = self::getDeploymentRevsAll($deployment);
        $results = array();
        if (is_array($revisions)) {
            foreach ($revisions as $revision) {
                $revnote = NagRedis::get(md5('deployment-audit:'.$deployment).':'.$revision.':revnote');
                if ($revnote === false) $results[$revision]['revnote'] = 'Not Available';
                else $results[$revision]['revnote'] = base64_decode($revnote);
                $users = NagRedis::sMembers(md5('deployment-audit:'.$deployment).':'.$revision.':users');
                if (($users === false) || (empty($users))) $results[$revision]['users'] = 'Not Available';
                else $results[$revision]['users'] = implode(", ", $users);
                $time = NagRedis::get(md5('deployment-audit:'.$deployment).':'.$revision.':revtime');
                if ($time === false) $results[$revision]['revtime'] = 'Not Available';
                else $results[$revision]['revtime'] = date("D M j G:i:s T Y", $time);
            }
        } else {
            $revnote = NagRedis::get(md5('deployment-audit:'.$deployment).':'.$revisions.':revnote');
            if ($revnote === false) $results[$revisions]['revnote'] = 'Not Available';
            else $results[$revisions]['revnote'] = base64_decode($revnote);
            $users = NagRedis::sMembers(md5('deployment-audit:'.$deployment).':'.$revisions.':users');
            if (($users === false) || (empty($users))) $results[$revisions]['users'] = 'Not Available';
            else $results[$revisions]['users'] = implode(", ", $users);
            $time = NagRedis::get(md5('deployment-audit:'.$deployment).':'.$revision.':revtime');
            if ($time === false) $results[$revision]['revtime'] = 'Not Available';
            else $results[$revision]['revtime'] = date("D M j G:i:s T Y", $time);
        }
        return $results;
    }

    public static function getCommonRepos() {
        if (self::$init === false) self::init();
        $results = NagRedis::sMembers(md5('commonrepos'));
        if (empty($results)) {
            return array('common');
        } else {
            array_push($results, 'common');
            sort($results);
            return $results;
        }
    }

    public static function addCommonRepo($deployment) {
        if (self::$init === false) self::init();
        return NagRedis::sAdd(md5('commonrepos'), $deployment);
    }

    public static function delCommonRepo($deployment) {
        if (self::$init === false) self::init();
        return NagRedis::sRem(md5('commonrepos'), $deployment);
    }

    public static function getDeploymentRev($deployment) {
        if (self::$init === false) self::init();
        $revision = NagRedis::hGet(md5('deployment:'.$deployment), 'revision');
        if ((empty($revision)) || ($revision === false)) {
            return false;
        } else {
            return $revision;
        }
    }

    public static function getDeploymentNextRev($deployment) {
        if (self::$init === false) self::init();
        $revision = NagRedis::hGet(md5('deployment:'.$deployment), 'nextrevision');
        if ((empty($revision)) || ($revision === false)) {
            return false;
        } else {
            return $revision;
        }
    }

    public static function getDeploymentPrevRev($deployment) {
        if (self::$init === false) self::init();
        $revision = NagRedis::hGet(md5('deployment:'.$deployment), 'prevrevision');
        if ((empty($revision)) || ($revision === false)) { 
            return false;
        } else {
            return $revision;
        }
    }

    public static function getDeploymentRevs($deployment) {
        if (self::$init === false) self::init();
        $results = array();
        $results['currrev'] = self::getDeploymentRev($deployment);
        $results['nextrev'] = self::getDeploymentNextRev($deployment);
        $results['prevrev'] = self::getDeploymentPrevRev($deployment);
        return $results;
    }

    public static function getDeploymentRevsAll($deployment) {
        if (self::$init === false) self::init();
        return self::getDeploymentAllRevs($deployment);
    }

    public static function getDeploymentAllRevs($deployment) {
        if (self::$init === false) self::init();
        $tmpResults = array();
        $keys = NagRedis::keys(md5('deployment:'.$deployment).':', true);
        $keys = array_shift($keys);
        foreach ($keys as $key) {
            $split = preg_split('/:/', $key);
            if ((!isset($tmpResults[$split[1]])) &&
                (preg_match("/\d+/", $split[1]))) {
                $tmpResults[$split[1]] = "enabled_".$split[1];
            }
        }
        $results = array_keys($tmpResults);
        natsort($results);
        return $results;
    }

    public static function setDeploymentRevs($deployment, $from, $to, $note) {
        if (self::$init === false) self::init();
        $revinfo = array('revision' => $to, 'prevrevision' => $from);
        NagRedis::hMSet(md5('deployment:'.$deployment), $revinfo);
        NagRedis::set(md5('deployment-audit:'.$deployment).':'.$to.':revnote', base64_encode($note));
        NagRedis::set(md5('deployment-audit:'.$deployment).':'.$to.':revtime', time());
        $amodule = AUTH_MODULE;
        self::addAuditUserLog($deployment, $to);
        self::$log->addToLog($amodule::getUser().' '.NagMisc::getIP().' deployment='.$deployment.' action=change_deployment_revision fromrevision='.$from.' torevision='.$to. ' note='.$note);
    }

    public static function deleteDeploymentRev($deployment, $revision) {
        if (self::$init === false) self::init();
        $amodule = AUTH_MODULE;
        if (is_array($revision)) {
            foreach ($revision as $subrevision) {
                $keys = NagRedis::keys(md5('deployment:'.$deployment).':'.$subrevision.':', true);
                NagRedis::del($keys);
                self::addAuditUserLog($deployment, $subrevision);
                self::$log->addToLog($amodule::getUser().' '.NagMisc::getIP().' revision='.$subrevision.' deployment='.$deployment.' action=deployment_revision_delete Bulk Removal of Revision Issued');
            }
        } else {
            $keys = NagRedis::keys(md5('deployment:'.$deployment).':'.$revision.':', true);
            NagRedis::del($keys);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($amodule::getUser().' '.NagMisc::getIP().' revision='.$revision.' deployment='.$deployment.' action=deployment_revision_delete Bulk Removal of Revision Issued');
        }
    }

    public static function deleteDeployment($deployment) {
        if (self::$init === false) self::init();
        $deploystyle = self::getDeploymentStyle($deployment);
        $keys = NagRedis::keys(md5('deployment:'.$deployment), true);
        NagRedis::del($keys);
        $auditkeys = NagRedis::keys(md5('deployment-audit:'.$deployment), true);
        NagRedis::del($auditkeys);
        if ($deploystyle == 'commonrepo') {
            self::delCommonRepo($deployment);
        }
        NagRedis::sRem(md5('deployments'), $deployment);
        $amodule = AUTH_MODULE;
        self::$log->addToLog($amodule::getUser().' '.NagMisc::getIP().' deployment='.$deployment.' action=deployment_delete Bulk Removal of Deployment Issued');
    }

    public static function incrDeploymentNextRev($deployment) {
        if (self::$init === false) self::init();
        return NagRedis::hIncrBy(md5('deployment:'.$deployment), 'nextrevision');
    }

    public static function existsDeploymentRev($deployment, $revision) {
        if (self::$init === false) self::init();
        $keys = NagRedis::keys(md5('deployment:'.$deployment).':'.$revision.':', true);
        if (($keys === false) || (empty($keys))) {
            return false;
        }
        return true;
    }

    public static function createCommonDeployment() {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployments'), 'common')) === false) {
            $commonInfo = array('name' => 'common', 'desc' => 'Global Configuration Information', 'authgroups' => 'Overridden by Conf on Server');
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
                self::createDeploymentTimeperiod('common', $timeperiod, $tpInfo, $times, 2);
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
                self::createDeploymentCommand('common', $cmd, $cmdInfo, 2);
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
                foreach ($cTempInfo as $key => $value) {
                    if (is_array($value)) $cTempInfo[$key] = implode(',', $value);
                }
                self::createDeploymentContactTemplate('common', $cTemp, $cTempInfo, 2);
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
                foreach ($hTempInfo as $key => $value) {
                    if (is_array($value)) $hTempInfo[$key] = implode(',', $value);
                }
                self::createDeploymentHostTemplate('common', $hTemp, $hTempInfo, 2);
            }
            return true;
        }
        return false;
    }

    public static function createDeployment($deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployments'), $deployment)) !== false) {
            $deployInfo['type'] = 'rev';
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment), $deployInfo)) !== false) {
                NagRedis::hIncrBy(md5('deployment:'.$deployment), 'revision');
                NagRedis::hIncrBy(md5('deployment:'.$deployment), 'nextrevision', 2);
                if (!empty($deployHostSearch)) {
                    foreach ($deployHostSearch as $md5Key => $tmpArray) {
                        NagRedis::sAdd(md5('deployment:'.$deployment).':hostsearches', $md5Key);
                        NagRedis::hMset(md5('deployment:'.$deployment).':hostsearch:'.$md5Key, $tmpArray);
                    }
                }
                if (!empty($deployStaticHosts)) {
                    NagRedis::set(md5('deployment:'.$deployment).':statichosts', json_encode($deployStaticHosts));
                }
                if (($deployment != 'common') && ($deployInfo['deploystyle'] == 'commonrepo')) {
                    self::addCommonRepo($deployment);
                }
                $deployData = new DeploymentData($deployment, $deployInfo, $deployHostSearch, $deployStaticHosts, 'create');
                self::$log->addToLog($deployData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeployment($deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployments'), $deployment)) === false) {
            NagRedis::sAdd(md5('deployments'), $deployment);
        }
        $oldDeployInfo = self::getDeploymentInfo($deployment);
        $oldHostSearch = self::getDeploymentHostSearches($deployment);
        $oldDeployStaticHosts = self::getDeploymentStaticHosts($deployment);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment), $deployInfo)) !== false) {
            NagRedis::del(md5('deployment:'.$deployment).':hostsearches');
            NagRedis::del(md5('deployment:'.$deployment).':statichosts');
            if (!empty($oldHostSearch)) {
                foreach ($oldHostSearch as $md5Key => $md5KeyArray) {
                    $oldHostSearch[$md5Key] = NagRedis::hGetAll(md5('deployment:'.$deployment).':hostsearch:'.$md5Key);
                    NagRedis::del(md5('deployment:'.$deployment).':hostsearch:'.$md5Key);
                }
            }
            if (!empty($deployHostSearch)) {
                foreach ($deployHostSearch as $md5Key => $tmpArray) {
                    NagRedis::sAdd(md5('deployment:'.$deployment).':hostsearches', $md5Key);
                    NagRedis::hMset(md5('deployment:'.$deployment).':hostsearch:'.$md5Key, $tmpArray);
                }
            }
            if (!empty($deployStaticHosts)) {
                NagRedis::set(md5('deployment:'.$deployment).':statichosts', json_encode($deployStaticHosts));
            }
            if (($deployInfo['deploystyle'] == 'commonrepo') && ($deployment != 'common')) {
                self::addCommonRepo($deployment);
            }
            $deployData = new DeploymentData($deployment, $deployInfo, $deployHostSearch, $deployStaticHosts, 'modify', $oldDeployInfo, $oldHostSearch, $oldDeployStaticHosts);
            self::$log->addToLog($deployData);
            return true;
        }
        return false;
    }

    public static function addDeploymentDynamicHost($deployment, $md5Key, array $hostInfo) {
        if (self::$init === false) self::init();
        NagRedis::sAdd(md5('deployment:'.$deployment).':hostsearches', $md5Key);
        NagRedis::hMset(md5('deployment:'.$deployment).':hostsearch:'.$md5Key, $hostInfo);
        $hostData = new DeploymentHostData($deployment, 'add', 'dynamic', $hostInfo);
        self::$log->addToLog($hostData);
        return true;
    }

    public static function addDeploymentStaticHost($deployment, $ip, array $hostInfo) {
        if (self::$init === false) self::init();
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
            $ip = NagMisc::encodeIP($ip);
        }
        $staticHosts = NagRedis::get(md5('deployment:'.$deployment).':statichosts');
        $newStaticHosts = json_decode($staticHosts, true);
        $newStaticHosts[$ip] = $hostInfo;
        NagRedis::set(md5('deployment:'.$deployment).':statichosts', json_encode($newStaticHosts));
        $hostData = new DeploymentHostData($deployment, 'add', 'static', $hostInfo);
        self::$log->addToLog($hostData);
        return true;
    }

    public static function delDeploymentDynamicHost($deployment, $md5Key) {
        if (self::$init === false) self::init();
        $hostInfo = array();
        if (NagRedis::sIsMember(md5('deployment:'.$deployment).':hostsearches', $md5Key)) {
            $hostInfo = NagRedis::hGetAll(md5('deployment:'.$deployment).':hostsearch:'.$md5Key);
            NagRedis::sRem(md5('deployment:'.$deployment).':hostsearches', $md5Key);
            NagRedis::del(md5('deployment:'.$deployment).':hostsearch:'.$md5Key);
            $hostData = new DeploymentHostData($deployment, 'del', 'dynamic', $hostInfo);
            self::$log->addToLog($hostData);
        }
        return $hostInfo;
    }

    public static function delDeploymentStaticHost($deployment, $ip) {
        if (self::$init === false) self::init();
        $hostInfo = array();
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
            $ip = NagMisc::encodeIP($ip);
        }
        $staticHosts = NagRedis::get(md5('deployment:'.$deployment).':statichosts');
        $staticHosts = json_decode($staticHosts, true);
        if ((isset($staticHosts[$ip])) && (!empty($staticHosts[$ip]))) {
            $hostInfo = $staticHosts[$ip];
            unset($staticHosts[$ip]);
            NagRedis::set(md5('deployment:'.$deployment).':statichosts', json_encode($staticHosts));
            $hostData = new DeploymentHostData($deployment, 'del', 'static', $hostInfo);
            self::$log->addToLog($hostData);
        }
        return $hostInfo;
    }

    public static function getDeployments() {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployments'));
    }

    public static function existsDeployment($deployment) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployments'), $deployment);
    }

    public static function getDeploymentGroupInfo($deployment) {
        if (self::$init === false) self::init();
        $results = NagRedis::hMGet(md5('deployment:'.$deployment), array('desc','authgroups','ldapgroups','type','revision'));
        if ((isset($results['authgroups'])) && (!empty($results['authgroups']))) {
            unset($results['ldapgroups']);
        }
        else {
            $results['authgroups'] = $results['ldapgroups'];
            unset($results['ldapgroups']);
        }
        $results['commonrepo'] = self::getDeploymentCommonRepo($deployment);
        return $results;
    }

    public static function getDeploymentInfo($deployment) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment));
        if ((isset($results['authgroups'])) && (!empty($results['authgroups']))) {
            unset($results['ldapgroups']);
        }
        else {
            $results['authgroups'] = $results['ldapgroups'];
            unset($results['ldapgroups']);
        }
        return $results;
    }

    public static function getDeploymentCommonRepo($deployment) {
        if (self::$init === false) self::init();
        if ($deployment == 'common') return 'undefined';
        $common = NagRedis::hGet(md5('deployment:'.$deployment), 'commonrepo');
        if (($common === false) || (empty($common))) {
            return 'common';
        } else {
            return $common;
        }
    }

    public static function getDeploymentHostSearches($deployment) {
        if (self::$init === false) self::init();
        $results = array();
        if (NagRedis::exists(md5('deployment:'.$deployment).':hostsearches')) {
            $members = NagRedis::sMembers(md5('deployment:'.$deployment).':hostsearches');
            foreach ($members as $member) {
                $memberInfo = NagRedis::hGetAll(md5('deployment:'.$deployment).':hostsearch:'.$member);
                if ((!isset($memberInfo['subdeployment'])) || (empty($memberInfo['subdeployment']))) {
                    $memberInfo['subdeployment'] = 'N/A';
                }
                $results[$member] = $memberInfo;
            }
        }
        return $results;
    }

    public static function getDeploymentStaticHosts($deployment) {
        if (self::$init === false) self::init();
        $results = array();
        if (NagRedis::exists(md5('deployment:'.$deployment).':statichosts')) {
            $jsonEnc = NagRedis::get(md5('deployment:'.$deployment).':statichosts');
            $results = json_decode($jsonEnc, true);
            foreach ($results as $encIP => $tmpArray) {
                if ((!isset($tmpArray['subdeployment'])) || (empty($tmpArray['subdeployment']))) {
                    $results[$encIP]['subdeployment'] = 'N/A';
                }
            }
        }
        return $results;
    }

    public static function getDeploymentHosts($deployment) {
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

    public static function getDeploymentAuthGroup($deployment) {
        if (self::$init === false) self::init();
        if ($deployment == 'common') {
            return SUPERMEN;
        }
        else {
            $results = NagRedis::hGet(md5('deployment:'.$deployment), 'authgroups');
            if ($results === false) {
                return self::getDeploymentLdapGroup($deployment);
            }
            return $results;
        }
    }

    public static function getDeploymentLdapGroup($deployment) {
        if (self::$init === false) self::init();
        if ($deployment == 'common') {
            return SUPERMEN;
        } else {
            return NagRedis::hGet(md5('deployment:'.$deployment), 'ldapgroups');
        }
    }

    public static function getDeploymentAliasTemplate($deployment) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGet(md5('deployment:'.$deployment), 'aliastemplate');
        if ((empty($results)) || ($results === false)) {
            return 'host-dc';
        }
        return $results;
    }

    public static function getDeploymentGlobalNegate($deployment) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGet(md5('deployment:'.$deployment), 'deploynegate');
        if ((empty($results)) || ($results === false)) {
            return false;
        }
        return $results;
    }

    public static function getDeploymentStyle($deployment) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGet(md5('deployment:'.$deployment), 'deploystyle');
        if ((empty($results)) || ($results === false)) {
            return 'both';
        }
        return $results;
    }

    public static function getDeploymentMiscSettings($deployment) {
        if (self::$init === false) self::init();
        $results = array();
        $results['aliastemplate'] = self::getDeploymentAliasTemplate($deployment);
        $ensharding = NagRedis::hGet(md5('deployment:'.$deployment), 'ensharding');
        if ((empty($ensharding)) || ($ensharding === false)) {
            $results['ensharding'] = 'off';
        } else {
            $results['ensharding'] = $ensharding;
        }
        if ($results['ensharding'] == 'on') {
            $results['shardkey'] = NagRedis::hGet(md5('deployment:'.$deployment), 'shardkey');
            $results['shardcount'] = NagRedis::hGet(md5('deployment:'.$deployment), 'shardcount');
        }
        $results['deploystyle'] = self::getDeploymentStyle($deployment);
        $results['deploynegate'] = self::getDeploymentGlobalNegate($deployment);
        return $results;
    }

    public static function getDeploymentData($deployment, $revision, $jsonEncode = false) {
        if (self::$init === false) self::init();
        $results = array();
        $results['timeperiods'] = self::getCommonMergedDeploymentTimeperiodswData($deployment, $revision);
        $results['commands'] = self::getCommonMergedDeploymentCommands($deployment, $revision, false);
        $results['contacttemplates'] = self::getCommonMergedDeploymentContactTemplates($deployment, $revision);
        $results['contacts'] = self::getCommonMergedDeploymentContacts($deployment, $revision);
        $results['contactgroups'] = self::getCommonMergedDeploymentContactGroups($deployment, $revision);
        $results['hosttemplates'] = self::getCommonMergedDeploymentHostTemplates($deployment, $revision);
        $results['hostgroups'] = self::getCommonMergedDeploymentHostGroups($deployment, $revision);
        $results['servicetemplates'] = self::getCommonMergedDeploymentSvcTemplates($deployment, $revision);
        $results['services'] = self::getCommonMergedDeploymentSvcs($deployment, $revision);
        $results['servicegroups'] = self::getCommonMergedDeploymentSvcGroups($deployment, $revision);
        $results['servicedependencies'] = self::getCommonMergedDeploymentSvcDependencies($deployment, $revision);
        $results['serviceescalations'] = self::getDeploymentSvcEscalationswInfo($deployment, $revision);
        $results['nodetemplates'] = self::getDeploymentNodeTemplateswInfo($deployment, $revision, true);
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

    public static function getDeploymentCommands($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':commands');
    }

    public static function getDeploymentCommand($deployment, $command, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':command:'.$command);
    }

    public static function getDeploymentCommandExec($deployment, $command, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGet(md5('deployment:'.$deployment).':'.$revision.':command:'.$command, 'command_line');
    }

    public static function getCommonMergedDeploymentCommand($deployment, $command, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentCommand($deployment, $command, $revision) === true) {
            $results = self::getDeploymentCommand($deployment, $command, $revision);
            $results['deployment'] = $deployment;
        }
        else {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $results = self::getDeploymentCommand($commonRepo, $command, $commonRev);
            $results['deployment'] = $commonRepo;
        }
        return $results;
    }

    public static function getCommonMergedDeploymentCommandExec($deployment, $command, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentCommand($deployment, $command, $revision) === true) {
            $results = self::getDeploymentCommandExec($deployment, $command, $revision);
            $results['deployment'] = $deployment;
        }
        else {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $results = self::getDeploymentCommandExec($commonRepo, $command, $commonRev);
            $results['deployment'] = $commonRepo;
        }
        return $results;
    }

    public static function createDeploymentCommand($deployment, $command, array $commandInput, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':commands', $command)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':command:'.$command, $commandInput)) !== false) {
                $deployCmdData = new CommandData($deployment, $revision, $command, $commandInput, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($deployCmdData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentCommand($deployment, $command, array $commandInput, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':commands', $command)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':commands', $command);
        }
        $oldCmdInfo = self::getDeploymentCommand($deployment, $command, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':command:'.$command);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':command:'.$command, $commandInput)) !== false) {
            $deployCmdData = new CommandData($deployment, $revision, $command, $commandInput, 'modify', $oldCmdInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCmdData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentCommand($deployment, $command, $revision) {
        if (self::$init === false) self::init();
        $commandInfo = self::getDeploymentCommand($deployment, $command, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':commands', $command);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':command:'.$command);
        $deployCmdData = new CommandData($deployment, $revision, $command, $commandInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployCmdData);
        return;
    }

    public static function existsDeploymentCommand($deployment, $command, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':commands', $command);
    }

    public static function getCommonMergedDeploymentNotifyCommands($deployment, $revision) {
        if (self::$init === false) self::init();
        $commands = array();
        $deployCmds = self::getDeploymentCommands($deployment, $revision);
        foreach ($deployCmds as $cmd) {
            if (preg_match('/^notify-/', $cmd)) {
                array_push($commands, $cmd);
            }
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonCmds = self::getDeploymentCommands($commonRepo, $commonRev);
            foreach ($commonCmds as $cmd) {
                if (preg_match('/^notify-/', $cmd)) {
                    array_push($commands, $cmd);
                }
            }
        }
        asort($commands);
        return $commands;
    }

    public static function getCommonMergedDeploymentHostCheckCommands($deployment, $revision) {
        if (self::$init === false) self::init();
        $commands = array();
        $deployCmds = self::getDeploymentCommands($deployment, $revision);
        foreach ($deployCmds as $cmd) {
            if (preg_match('/host-alive$/', $cmd)) {
                array_push($commands, $cmd);
            }
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonCmds = self::getDeploymentCommands($commonRepo, $commonRev);
            foreach ($commonCmds as $cmd) {
                if (preg_match('/host-alive$/', $cmd)) {
                    array_push($commands, $cmd);
                }
            }
        }
        asort($commands);
        return $commands;
    }

    public static function getDeploymentCommandswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentCommands($deployment, $revision, $skipAlertsHostChks = true) {
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
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonCmds = self::getDeploymentCommands($commonRepo, $commonRev);
            if (!empty($commonCmds)) sort($commonCmds);
            foreach ($commonCmds as $cmd) {
                if ((isset($commands[$cmd])) && (!empty($commands[$cmd]))) continue;
                if ((preg_match('/^notify-|host-alive$/', $cmd)) && ($skipAlertsHostChks === true)) continue;
                $cmdInfo = self::getDeploymentCommand($commonRepo, $cmd, $commonRev);
                if (empty($cmdInfo)) continue;
                $cmdInfo['deployment'] = $commonRepo;
                $commands[$cmd] = $cmdInfo;
            }
        }
        return $commands;
    }

    public static function getDeploymentTimeperiods($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':timeperiods');
    }

    public static function existsDeploymentTimeperiod($deployment, $timePeriod, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':timeperiods', $timePeriod);
    }

    public static function getDeploymentTimeperiodInfo($deployment, $timePeriod, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':info');
    }

    public static function getDeploymentTimeperiodData($deployment, $timePeriod, $revision) {
        if (self::$init === false) self::init();
        $members = NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod);
        $results = array();
        foreach ($members as $member) {
            $results[$member] = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':data:'.$member);
        }
        return $results;
    }

    public static function createDeploymentTimeperiod($deployment, $timePeriod, array $timePeriodInfo, array $timePeriodData, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':timeperiods', $timePeriod)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':info', $timePeriodInfo)) !== false) {
                foreach ($timePeriodData as $md5Key => $timeArray) {
                    NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod, $md5Key);
                    NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key, $timeArray);
                }
                $deployTimeperiodData = new TimeperiodData($deployment, $revision, $timePeriod, $timePeriodInfo, $timePeriodData, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($deployTimeperiodData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentTimeperiod($deployment, $timePeriod, array $timePeriodInfo, array $timePeriodData, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':timeperiods', $timePeriod)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':timeperiods', $timePeriod);
        }
        $oldTimeperiodInfo = self::getDeploymentTimeperiodInfo($deployment, $timePeriod, $revision);
        $oldTimeperiodData = self::getDeploymentTimeperiodData($deployment, $timePeriod, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':timeperiod:'.$timePeriod.':info');
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':info', $timePeriodInfo)) !== false) {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod);
            foreach ($oldTimeperiodData as $md5Key => $md5KeyArray) {
                NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key);
            }
            foreach ($timePeriodData as $md5Key => $timeArray) {
                NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod, $md5Key);
                NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key, $timeArray);
            }
            $deployTimeperiodData = new TimeperiodData($deployment, $revision, $timePeriod, $timePeriodInfo, $timePeriodData, 'modify', $oldTimeperiodInfo, $oldTimeperiodData);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployTimeperiodData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentTimeperiod($deployment, $timePeriod, $revision) {
        if (self::$init === false) self::init();
        $timePeriodInfo = self::getDeploymentTimeperiodInfo($deployment, $timePeriod, $revision);
        $timePeriodData = self::getDeploymentTimeperiodData($deployment, $timePeriod, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':timeperiods', $timePeriod);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':info');
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod);
        foreach ($timePeriodData as $md5Key => $md5KeyArray) {
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key);
        }
        $deployTimeperiodData = new TimeperiodData($deployment, $revision, $timePeriod, $timePeriodInfo, $timePeriodData, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployTimeperiodData);
        return;
    }

    public static function getDeploymentTimeperiodswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentTimeperiods($deployment, $revision) {
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiodInfo($deployment, $time, $revision);
            if (empty($timePeriod)) continue;
            $timePeriod['deployment'] = $deployment;
            $timePeriods[$time] = $timePeriod;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonTimes = self::getDeploymentTimeperiods($commonRepo, $commonRev);
            foreach ($commonTimes as $time) {
                if ((isset($timePeriods[$time])) && (!empty($timePeriods[$time]))) continue;
                $timePeriod = self::getDeploymentTimeperiodInfo($commonRepo, $time, $commonRev);
                if (empty($timePeriod)) continue;
                $timePeriod['deployment'] = $commonRepo;
                $timePeriods[$time] = $timePeriod;
            }
        }
        return $timePeriods;
    }

    public static function getDeploymentTimeperiodswData($deployment, $revision) {
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        if (!empty($deployTimes)) sort($deployTimes);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiodInfo($deployment, $time, $revision);
            if (empty($timePeriod)) continue;
            $timePeriod['times'] = self::getDeploymentTimeperiodData($deployment, $time, $revision);
            if (!empty($timePeriod['times'])) sort($timePeriod['times']);
            $timePeriod['deployment'] = $deployment;
            $timePeriods[$time] = $timePeriod;
        }
        return $timePeriods;
    }

    public static function getCommonMergedDeploymentTimeperiodswData($deployment, $revision) {
        if (self::$init === false) self::init();
        $timePeriods = array();
        $deployTimes = self::getDeploymentTimeperiods($deployment, $revision);
        if (!empty($deployTimes)) sort($deployTimes);
        foreach ($deployTimes as $time) {
            $timePeriod = self::getDeploymentTimeperiodInfo($deployment, $time, $revision);
            if (empty($timePeriod)) continue;
            $timePeriod['times'] = self::getDeploymentTimeperiodData($deployment, $time, $revision);
            if (!empty($timePeriod['times'])) sort($timePeriod['times']);
            $timePeriod['deployment'] = $deployment;
            $timePeriods[$time] = $timePeriod;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonTimes = self::getDeploymentTimeperiods($commonRepo, $commonRev);
            if (!empty($commonTimes)) sort($commonTimes);
            foreach ($commonTimes as $time) {
                if ((isset($timePeriods[$time])) && (!empty($timePeriods[$time]))) continue;
                $timePeriod = self::getDeploymentTimeperiodInfo($commonRepo, $time, $commonRev);
                if (empty($timePeriod)) continue;
                $timePeriod['times'] = self::getDeploymentTimeperiodData($commonRepo, $time, $commonRev);
                if (!empty($timePeriod['times'])) sort($timePeriod['times']);
                $timePeriod['deployment'] = $commonRepo;
                $timePeriods[$time] = $timePeriod;
            }
        }
        return $timePeriods;
    }

    public static function getDeploymentTimeperiod($deployment, $timeperiod, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentTimeperiod($deployment, $timeperiod, $revision) === false) return false;
        $results = self::getDeploymentTimeperiodInfo($deployment, $timeperiod, $revision);
        if (empty($results)) continue;
        $results['times'] = self::getDeploymentTimeperiodData($deployment, $timeperiod, $revision);
        if (!empty($results['times'])) sort($results['times']);
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentContacts($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':contacts');
    }

    public static function getDeploymentContact($deployment, $contact, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':contact:'.$contact);
        if ((isset($results['host_notification_options'])) && (preg_match('/,/', $results['host_notification_options']))) {
            $results['host_notification_options'] = preg_split('/\s?,\s?/', $results['host_notification_options']);
        }
        elseif ((isset($results['host_notification_options'])) && (!empty($results['host_notification_options']))) {
            $results['host_notification_options'] = array($results['host_notification_options']);
        }
        if ((isset($results['service_notification_options'])) && (preg_match('/,/', $results['service_notification_options']))) {
            $results['service_notification_options'] = preg_split('/\s?,\s?/', $results['service_notification_options']);
        }
        elseif ((isset($results['service_notification_options'])) && (!empty($results['service_notification_options']))) {
            $results['service_notification_options'] = array($results['service_notification_options']);
        }
        return $results;
    }

    public static function existsDeploymentContact($deployment, $contact, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':contacts', $contact);
    }

    public static function createDeploymentContact($deployment, $contact, array $contactInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':contacts', $contact)) !== false) {
            if ($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':contact:'.$contact, $contactInfo) !== false) {
                $deployContactData = new ContactData($deployment, $revision, $contact, $contactInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($deployContactData);
                return true;
            }
        }
        return false;
    }

    public static function deleteDeploymentContact($deployment, $contact, $revision) {
        if (self::$init === false) self::init();
        $contactInfo = self::getDeploymentContact($deployment, $contact, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':contacts', $contact);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':contact:'.$contact);
        $deployContactData = new ContactData($deployment, $revision, $contact, $contactInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployContactData);
        return;
    }

    public static function modifyDeploymentContact($deployment, $contact, array $contactInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':contacts', $contact)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':contacts', $contact);
        }
        $oldContactInfo = self::getDeploymentContact($deployment, $contact, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':contact:'.$contact);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':contact:'.$contact, $contactInfo)) !== false) {
            $deployContactData = new ContactData($deployment, $revision, $contact, $contactInfo, 'modify', $oldContactInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactData);
            return true;
        }
        return false;
    }

    public static function getDeploymentContactswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $contacts = array();
        $deployContacts = self::getDeploymentContacts($deployment, $revision);
        if (!empty($deployContacts)) sort($deployContacts);
        foreach ($deployContacts as $contact) {
            $contactArray = self::getDeploymentContact($deployment, $contact, $revision);
            if (empty($contact)) continue;
            $contactArray['deployment'] = $deployment;
            $contacts[$contact] = $contactArray;
        }
        return $contacts;
    }

    public static function getCommonMergedDeploymentContacts($deployment, $revision) {
        if (self::$init === false) self::init();
        $contacts = array();
        $deployContacts = self::getDeploymentContacts($deployment, $revision);
        if (!empty($deployContacts)) sort($deployContacts);
        foreach ($deployContacts as $contact) {
            $contactArray = self::getDeploymentContact($deployment, $contact, $revision);
            if (empty($contact)) continue;
            $contactArray['deployment'] = $deployment;
            $contacts[$contact] = $contactArray;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonContacts = self::getDeploymentContacts($commonRepo, $commonRev);
            if (!empty($commonContacts)) sort($commonContacts);
            foreach ($commonContacts as $contact) {
                if ((isset($contacts[$contact])) && (!empty($contacts[$contact]))) continue;
                $contactArray = self::getDeploymentContact($commonRepo, $contact, $commonRev);
                if (empty($contactArray)) continue;
                $contactArray['deployment'] = $commonRepo;
                $contacts[$contact] = $contactArray;
            }
        }
        return $contacts;
    }

    public static function getDeploymentContactGroups($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':contactgroups');
    }

    public static function getDeploymentContactGroup($deployment, $contactGroup, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':contactgroup:'.$contactGroup);
        $explodeOpts = array('members','contactgroup_members');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function existsDeploymentContactGroup($deployment, $contactGroup, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':contactgroups', $contactGroup);
    }

    public static function getDeploymentContactGroupswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $cGrpInfo = array();
        $cGrps = self::getDeploymentContactGroups($deployment, $revision);
        foreach ($cGrps as $cGrp) {
            $cGrpInfo[$cGrp] = self::getDeploymentContactGroup($deployment, $cGrp, $revision);
        }
        return $cGrpInfo;
    }

    public static function createDeploymentContactGroup($deployment, $contactGroup, array $contactGroupInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':contactgroups', $contactGroup)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':contactgroup:'.$contactGroup, $contactGroupInfo)) !== false) {
                $deployContactGroupData = new ContactGroupData($deployment, $revision, $contactGroup, $contactGroupInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($deployContactGroupData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentContactGroup($deployment, $contactGroup, array $contactGroupInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':contactgroups', $contactGroup)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':contactgroups', $contactGroup);
        }
        $oldContactGroupInfo = self::getDeploymentContactGroup($deployment, $contactGroup, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':contactgroup:'.$contactGroup);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':contactgroup:'.$contactGroup, $contactGroupInfo)) !== false) {
            $deployContactGroupData = new ContactGroupData($deployment, $revision, $contactGroup, $contactGroupInfo, 'modify', $oldContactGroupInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployContactGroupData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentContactGroup($deployment, $contactGroup, $revision) {
        if (self::$init === false) self::init();
        $contactGroupInfo = self::getDeploymentContactGroup($deployment, $contactGroup, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':contactgroups', $contactGroup);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':contactgroup:'.$contactGroup);
        $deployContactGroupData = new ContactGroupData($deployment, $revision, $contactGroup, $contactGroupInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployContactGroupData);
        return;
    }

    public static function getCommonMergedDeploymentContactGroups($deployment, $revision) {
        if (self::$init === false) self::init();
        $contactGroups = array();
        $deployContactGroups = self::getDeploymentContactGroups($deployment, $revision);
        if (!empty($deployContactGroups)) sort($deployContactGroups);
        foreach ($deployContactGroups as $ctemplate) {
            $contactGroup = self::getDeploymentContactGroup($deployment, $ctemplate, $revision);
            if (empty($contactGroup)) continue;
            $contactGroup['deployment'] = $deployment;
            $contactGroups[$ctemplate] = $contactGroup;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonContactGroups = self::getDeploymentContactGroups($commonRepo, $commonRev);
            if (!empty($commonContactGroups)) sort($commonContactGroups);
            foreach ($commonContactGroups as $ctemplate) {
                if ((isset($contactGroups[$ctemplate])) && (!empty($contactGroups[$ctemplate]))) continue;
                $contactGroup = self::getDeploymentContactGroup($commonRepo, $ctemplate, $commonRev);
                if (empty($contactGroup)) continue;
                $contactGroup['deployment'] = $commonRepo;
                $contactGroups[$ctemplate] = $contactGroup;
            }
        }
        return $contactGroups;
    }

    public static function getDeploymentContactTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':contacttemplates');
    }

    public static function getDeploymentContactTemplate($deployment, $contactTemplate, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':contacttemplate:'.$contactTemplate);
        $explodeOpts = array('host_notification_options','service_notification_options');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function createDeploymentContactTemplate($deployment, $contactTemplate, array $contactInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':contacttemplates', $contactTemplate)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':contacttemplate:'.$contactTemplate, $contactInfo)) !== false) {
                $contactTemplateData = new ContactTemplateData($deployment, $revision, $contactTemplate, $contactInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($contactTemplateData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentContactTemplate($deployment, $contactTemplate, array $contactInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':contacttemplates', $contactTemplate)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':contacttemplates', $contactTemplate);
        }
        $oldContactInfo = self::getDeploymentContactTemplate($deployment, $contactTemplate, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':contacttemplate:'.$contactTemplate);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':contacttemplate:'.$contactTemplate, $contactInfo)) !== false) {
            $contactTemplateData = new ContactTemplateData($deployment, $revision, $contactTemplate, $contactInfo, 'modify', $oldContactInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($contactTemplateData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentContactTemplate($deployment, $contactTemplate, $revision) {
        if (self::$init === false) self::init();
        $contactInfo = self::getDeploymentContactTemplate($deployment, $contactTemplate, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':contacttemplates', $contactTemplate);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':contacttemplate:'.$contactTemplate);
        $contactTemplateData = new ContactTemplateData($deployment, $revision, $contactTemplate, $contactInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($contactTemplateData);
        return;
    }

    public static function getDeploymentContactTemplateswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $contactTemplates = array();
        $deployContactTemplates = self::getDeploymentContactTemplates($deployment, $revision);
        if (!empty($deployContactTemplates)) sort($deployContactTemplates);
        foreach ($deployContactTemplates as $ctemplate) {
            $contactTemplate = self::getDeploymentContactTemplate($deployment, $ctemplate, $revision);
            if (empty($contactTemplate)) continue;
            $contactTemplates[$ctemplate] = $contactTemplate;
        }
        return $contactTemplates;
    }

    public static function getCommonMergedDeploymentContactTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        $contactTemplates = array();
        $deployContactTemplates = self::getDeploymentContactTemplates($deployment, $revision);
        if (!empty($deployContactTemplates)) sort($deployContactTemplates);
        foreach ($deployContactTemplates as $ctemplate) {
            $contactTemplate = self::getDeploymentContactTemplate($deployment, $ctemplate, $revision);
            if (empty($contactTemplate)) continue;
            $contactTemplate['deployment'] = $deployment;
            $contactTemplates[$ctemplate] = $contactTemplate;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonContactTemplates = self::getDeploymentContactTemplates($commonRepo, $commonRev);
            if (!empty($commonContactTemplates)) sort($commonContactTemplates);
            foreach ($commonContactTemplates as $ctemplate) {
                if ((isset($contactTemplates[$ctemplate])) && (!empty($contactTemplates[$ctemplate]))) continue;
                $contactTemplate = self::getDeploymentContactTemplate($commonRepo, $ctemplate, $commonRev);
                if (empty($contactTemplate)) continue;
                $contactTemplate['deployment'] = $commonRepo;
                $contactTemplates[$ctemplate] = $contactTemplate;
            }
        }
        return $contactTemplates;
    }

    public static function existsDeploymentContactTemplate($deployment, $contactTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':contacttemplates', $contactTemplate);
    }

    public static function getDeploymentHostTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':hosttemplates');
    }

    public static function getDeploymentHostTemplate($deployment, $hostTemplate, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':hosttemplate:'.$hostTemplate);
        $explodeOpts = array('contacts','contact_groups','notification_options');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function existsDeploymentHostTemplate($deployment, $hostTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':hosttemplates', $hostTemplate);
    }

    public static function createDeploymentHostTemplate($deployment, $hostTemplate, array $hostInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':hosttemplates', $hostTemplate)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':hosttemplate:'.$hostTemplate, $hostInfo)) !== false) {
                $hostTemplateData = new HostTemplateData($deployment, $revision, $hostTemplate, $hostInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($hostTemplateData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentHostTemplate($deployment, $hostTemplate, array $hostInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':hosttemplates', $hostTemplate)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':hosttemplates', $hostTemplate);
        }
        $oldHostInfo = self::getDeploymentHostTemplate($deployment, $hostTemplate, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':hosttemplate:'.$hostTemplate);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':hosttemplate:'.$hostTemplate, $hostInfo)) !== false) {
            $hostTemplateData = new HostTemplateData($deployment, $revision, $hostTemplate, $hostInfo, 'modify', $oldHostInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostTemplateData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentHostTemplate($deployment, $hostTemplate, $revision) {
        if (self::$init === false) self::init();
        $hostInfo = self::getDeploymentHostTemplate($deployment, $hostTemplate, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':hosttemplates', $hostTemplate);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':hosttemplate:'.$hostTemplate);
        $hostTemplateData = new HostTemplateData($deployment, $revision, $hostTemplate, $hostInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($hostTemplateData);
        return;
    }

    public static function getDeploymentHostTemplateswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentHostTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        $hostTemplates = array();
        $deployHostTemplates = self::getDeploymentHostTemplates($deployment, $revision);
        if (!empty($deployHostTemplates)) sort($deployHostTemplates);
        foreach ($deployHostTemplates as $ctemplate) {
            $hostTemplate = self::getDeploymentHostTemplate($deployment, $ctemplate, $revision);
            if (empty($hostTemplate)) continue;
            $hostTemplate['deployment'] = $deployment;
            $hostTemplates[$ctemplate] = $hostTemplate;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonHostTemplates = self::getDeploymentHostTemplates($commonRepo, $commonRev);
            if (!empty($commonHostTemplates)) sort($commonHostTemplates);
            foreach ($commonHostTemplates as $ctemplate) {
                if ((isset($hostTemplates[$ctemplate])) && (!empty($hostTemplates[$ctemplate]))) continue;
                $hostTemplate = self::getDeploymentHostTemplate($commonRepo, $ctemplate, $commonRev);
                if (empty($hostTemplate)) continue;
                $hostTemplate['deployment'] = $commonRepo;
                $hostTemplates[$ctemplate] = $hostTemplate;
            }
        }
        return $hostTemplates;
    }

    public static function getDeploymentHostGroups($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':hostgroups');
    }

    public static function getDeploymentHostGroup($deployment, $hostGroup, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':hostgroup:'.$hostGroup);
    }

    public static function existsDeploymentHostGroup($deployment, $hostGroup, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':hostgroups', $hostGroup);
    }

    public static function createDeploymentHostGroup($deployment, $hostGroup, array $hostGrpInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':hostgroups', $hostGroup)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':hostgroup:'.$hostGroup, $hostGrpInfo)) !== false) {
                $hostGroupData = new HostGroupData($deployment, $revision, $hostGroup, $hostGrpInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($hostGroupData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentHostGroup($deployment, $hostGroup, array $hostGrpInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':hostgroups', $hostGroup)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':hostgroups', $hostGroup);
        }
        $oldHostGrpInfo = self::getDeploymentHostGroup($deployment, $hostGroup, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':hostgroup:'.$hostGroup);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':hostgroup:'.$hostGroup, $hostGrpInfo)) !== false) {
            $hostGroupData = new HostGroupData($deployment, $revision, $hostGroup, $hostGrpInfo, 'modify', $oldHostGrpInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($hostGroupData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentHostGroup($deployment, $hostGroup, $revision) {
        if (self::$init === false) self::init();
        $hostGrpInfo = self::getDeploymentHostGroup($deployment, $hostGroup, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':hostgroups', $hostGroup);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':hostgroup:'.$hostGroup);
        $hostGroupData = new HostGroupData($deployment, $revision, $hostGroup, $hostGrpInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($hostGroupData);
        return;
    }

    public static function getDeploymentHostGroupswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $hostGroups = array();
        $deployHostGroups = self::getDeploymentHostGroups($deployment, $revision);
        if (!empty($deployHostGroups)) sort($deployHostGroups);
        foreach ($deployHostGroups as $cgroup) {
            $hostGroup = self::getDeploymentHostGroup($deployment, $cgroup, $revision);
            if (empty($hostGroup)) continue;
            $hostGroup['deployment'] = $deployment;
            $hostGroups[$cgroup] = $hostGroup;
        }
        return $hostGroups;
    }

    public static function getCommonMergedDeploymentHostGroups($deployment, $revision) {
        if (self::$init === false) self::init();
        $hostGroups = array();
        $deployHostGroups = self::getDeploymentHostGroups($deployment, $revision);
        if (!empty($deployHostGroups)) sort($deployHostGroups);
        foreach ($deployHostGroups as $cgroup) {
            $hostGroup = self::getDeploymentHostGroup($deployment, $cgroup, $revision);
            if (empty($hostGroup)) continue;
            $hostGroup['deployment'] = $deployment;
            $hostGroups[$cgroup] = $hostGroup;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonHostGroups = self::getDeploymentHostGroups($commonRepo, $commonRev);
            if (!empty($commonHostGroups)) sort($commonHostGroups);
            foreach ($commonHostGroups as $cgroup) {
                if ((isset($hostGroups[$cgroup])) && (!empty($hostGroups[$cgroup]))) continue;
                $hostGroup = self::getDeploymentHostGroup($commonRepo, $cgroup, $commonRev);
                if (empty($hostGroup)) continue;
                $hostGroup['deployment'] = $commonRepo;
                $hostGroups[$cgroup] = $hostGroup;
            }
        }
        return $hostGroups;
    }

    public static function getDeploymentSvcTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':svctemplates');
    }

    public static function getDeploymentSvcTemplate($deployment, $svcTemplate, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':svctemplate:'.$svcTemplate);
        $explodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function existsDeploymentSvcTemplate($deployment, $svcTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svctemplates', $svcTemplate);
    }

    public static function createDeploymentSvcTemplate($deployment, $svcTemplate, array $svcInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svctemplates', $svcTemplate)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svctemplate:'.$svcTemplate, $svcInfo)) !== false) {
                $svcTemplateData = new ServiceTemplateData($deployment, $revision, $svcTemplate, $svcInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($svcTemplateData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentSvcTemplate($deployment, $svcTemplate, array $svcInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svctemplates', $svcTemplate)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svctemplates', $svcTemplate);
        }
        $oldSvcInfo = self::getDeploymentSvcTemplate($deployment, $svcTemplate, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svctemplate:'.$svcTemplate);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svctemplate:'.$svcTemplate, $svcInfo)) !== false) {
            $svcTemplateData = new ServiceTemplateData($deployment, $revision, $svcTemplate, $svcInfo, 'modify', $oldSvcInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcTemplateData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcTemplate($deployment, $svcTemplate, $revision) {
        if (self::$init === false) self::init();
        $svcInfo = self::getDeploymentSvcTemplate($deployment, $svcTemplate, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':svctemplates', $svcTemplate);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svctemplate:'.$svcTemplate);
        $svcTemplateData = new ServiceTemplateData($deployment, $revision, $svcTemplate, $svcInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($svcTemplateData);
        return;
    }

    public static function getDeploymentSvcTemplateswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentSvcTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        $svcTemplates = array();
        $deploySvcTemplates = self::getDeploymentSvcTemplates($deployment, $revision);
        if (!empty($deploySvcTemplates)) sort($deploySvcTemplates);
        foreach ($deploySvcTemplates as $ctemplate) {
            $svcTemplate = self::getDeploymentSvcTemplate($deployment, $ctemplate, $revision);
            if (empty($svcTemplate)) continue;
            $svcTemplate['deployment'] = $deployment;
            $svcTemplates[$ctemplate] = $svcTemplate;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonSvcTemplates = self::getDeploymentSvcTemplates($commonRepo, $commonRev);
            if (!empty($commonSvcTemplates)) sort($commonSvcTemplates);
            foreach ($commonSvcTemplates as $ctemplate) {
                if ((isset($svcTemplates[$ctemplate])) && (!empty($svcTemplates[$ctemplate]))) continue;
                $svcTemplate = self::getDeploymentSvcTemplate($commonRepo, $ctemplate, $commonRev);
                if (empty($svcTemplate)) continue;
                $svcTemplate['deployment'] = $commonRepo;
                $svcTemplates[$ctemplate] = $svcTemplate;
            }
        }
        return $svcTemplates;
    }

    public static function getDeploymentSvcGroups($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':svcgroups');
    }

    public static function getDeploymentSvcGroup($deployment, $svcGroup, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':svcgroup:'.$svcGroup);
    }

    public static function existsDeploymentSvcGroup($deployment, $svcGroup, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcgroups', $svcGroup);
    }

    public static function createDeploymentSvcGroup($deployment, $svcGroup, array $svcGrpInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcgroups', $svcGroup)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svcgroup:'.$svcGroup, $svcGrpInfo)) !== false) {
                $svcGroupData = new ServiceGroupData($deployment, $revision, $svcGroup, $svcGrpInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($svcGroupData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentSvcGroup($deployment, $svcGroup, array $svcGrpInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcgroups', $svcGroup)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcgroups', $svcGroup);
        }
        $oldSvcGrpInfo = self::getDeploymentSvcGroup($deployment, $svcGroup, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svcgroup:'.$svcGroup);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svcgroup:'.$svcGroup, $svcGrpInfo)) !== false) {
            $svcGroupData = new ServiceGroupData($deployment, $revision, $svcGroup, $svcGrpInfo, 'modify', $oldSvcGrpInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcGroupData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcGroup($deployment, $svcGroup, $revision) {
        if (self::$init === false) self::init();
        $svcGrpInfo = self::getDeploymentSvcGroup($deployment, $svcGroup, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':svcgroups', $svcGroup);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svcgroup:'.$svcGroup);
        $svcGroupData = new ServiceGroupData($deployment, $revision, $svcGroup, $svcGrpInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($svcGroupData);
        return;
    }

    public static function getDeploymentSvcGroupswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentSvcGroups($deployment, $revision) {
        if (self::$init === false) self::init();
        $svcGroups = array();
        $deploySvcGroups = self::getDeploymentSvcGroups($deployment, $revision);
        if (!empty($deploySvcGroups)) sort($deploySvcGroups);
        foreach ($deploySvcGroups as $cgroup) {
            $svcGroup = self::getDeploymentSvcGroup($deployment, $cgroup, $revision);
            if (empty($svcGroup)) continue;
            $svcGroup['deployment'] = $deployment;
            $svcGroups[$cgroup] = $svcGroup;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonSvcGroups = self::getDeploymentSvcGroups($commonRepo, $commonRev);
            if (!empty($commonSvcGroups)) sort($commonSvcGroups);
            foreach ($commonSvcGroups as $cgroup) {
                if ((isset($svcGroups[$cgroup])) && (!empty($svcGroups[$cgroup]))) continue;
                $svcGroup = self::getDeploymentSvcGroup($commonRepo, $cgroup, $commonRev);
                if (empty($svcGroup)) continue;
                $svcGroup['deployment'] = $commonRepo;
                $svcGroups[$cgroup] = $svcGroup;
            }
        }
        return $svcGroups;
    }

    public static function getDeploymentSvcs($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':svcs');
    }

    public static function getDeploymentSvc($deployment, $svc, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':svc:'.$svc);
        $explodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function existsDeploymentSvc($deployment, $svc, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcs', $svc);
    }

    public static function createDeploymentSvc($deployment, $svc, array $svcInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcs', $svc)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svc:'.$svc, $svcInfo)) !== false) {
                $svcData = new ServiceData($deployment, $revision, $svc, $svcInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($svcData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentSvc($deployment, $svc, array $svcInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcs', $svc)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcs', $svc);
        }
        $oldSvcInfo = self::getDeploymentSvc($deployment, $svc, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svc:'.$svc);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svc:'.$svc, $svcInfo)) !== false) {
            $svcData = new ServiceData($deployment, $revision, $svc, $svcInfo, 'modify', $oldSvcInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvc($deployment, $svc, $revision) {
        if (self::$init === false) self::init();
        $svcInfo = self::getDeploymentSvc($deployment, $svc, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':svcs', $svc);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svc:'.$svc);
        $svcData = new ServiceData($deployment, $revision, $svc, $svcInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($svcData);
        return;
    }

    public static function getDeploymentSvcswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentSvcs($deployment, $revision) {
        if (self::$init === false) self::init();
        $svcs = array();
        $tmpSvcsInfoArray = array();
        $deploySvcs = self::getDeploymentSvcs($deployment, $revision);
        $tmpSvcs = $deploySvcs;
        foreach ($deploySvcs as $cSvc) {
            $svc = self::getDeploymentSvc($deployment, $cSvc, $revision);
            if (empty($svc)) continue;
            $svc['deployment'] = $deployment;
            $tmpSvcsInfoArray[$cSvc] = $svc;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonSvcs = self::getDeploymentSvcs($commonRepo, $commonRev);
            foreach ($commonSvcs as $cSvc) {
                if ((isset($tmpSvcsInfoArray[$cSvc])) && (!empty($tmpSvcsInfoArray[$cSvc]))) continue;
                array_push($tmpSvcs, $cSvc);
                $svc = self::getDeploymentSvc($commonRepo, $cSvc, $commonRev);
                if (empty($svc)) continue;
                $svc['deployment'] = $commonRepo;
                $tmpSvcsInfoArray[$cSvc] = $svc;
            }
        }
        asort($tmpSvcs);
        foreach ($tmpSvcs as $tmpSvc) {
            $svcs[$tmpSvc] = $tmpSvcsInfoArray[$tmpSvc];
        }
        return $svcs;
    }

    public static function getCommonMergedDeploymentSvcsKeyedOnDeployment($deployment, $revision) {
        if (self::$init === false) self::init();
        $svcs = array();
        $deploySvcs = self::getDeploymentSvcs($deployment, $revision);
        foreach ($deploySvcs as $cSvc) {
            $svc = self::getDeploymentSvc($deployment, $cSvc, $revision);
            if (empty($svc)) continue;
            $svcs[$deployment][$cSvc] = $svc;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonSvcs = self::getDeploymentSvcs($commonRepo, $commonRev);
            foreach ($commonSvcs as $cSvc) {
                if ((isset($svcs[$cSvc])) && (!empty($svcs[$cSvc]))) continue;
                $svc = self::getDeploymentSvc($commonRepo, $cSvc, $commonRev);
                if (empty($svc)) continue;
                $svcs[$commonRepo][$cSvc] = $svc;
            }
        }
        return $svcs;
    }

    public static function getDeploymentSvcDependencies($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':svcdeps');
    }

    public static function getDeploymentSvcDependency($deployment, $svcDep, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':svcdep:'.$svcDep);
        $explodeOpts = array('execution_failure_criteria','notification_failure_criteria');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function existsDeploymentSvcDependency($deployment, $svcDep, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcdeps', $svcDep);
    }

    public static function getDeploymentSvcDependencieswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentSvcDependencies($deployment, $revision) {
        if (self::$init === false) self::init();
        $deps = array();
        $deployDeps = self::getDeploymentSvcDependencies($deployment, $revision);
        if (!empty($deployDeps)) sort($deployDeps);
        foreach ($deployDeps as $cDep) {
            $dep = self::getDeploymentSvcDependency($deployment, $cDep, $revision);
            if (empty($dep)) continue;
            $dep['deployment'] = $deployment;
            $deps[$cDep] = $dep;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonDeps = self::getDeploymentSvcDependencies($commonRepo, $commonRev);
            if (!empty($commonDeps)) sort($commonDeps);
            foreach ($commonDeps as $cDep) {
                if ((isset($deps[$cDep])) && (!empty($deps[$cDep]))) continue;
                $dep = self::getDeploymentSvcDependency($commonRepo, $cDep, $commonRev);
                if (empty($dep)) continue;
                $dep['deployment'] = $commonRepo;
                $deps[$cDep] = $dep;
            }
        }
        return $deps;
    }

    public static function createDeploymentSvcDependency($deployment, $svcDep, array $svcDepInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcdeps', $svcDep)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svcdep:'.$svcDep, $svcDepInfo)) !== false) {
                $svcDepData = new ServiceDependencyData($deployment, $revision, $svcDep, $svcDepInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($svcDepData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentSvcDependency($deployment, $svcDep, array $svcDepInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcdeps', $svcDep)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcdeps', $svcDep);
        }
        $oldSvcDepInfo = self::getDeploymentSvcDependency($deployment, $svcDep, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svcdep:'.$svcDep);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svcdep:'.$svcDep, $svcDepInfo)) !== false) {
            $svcDepData = new ServiceDependencyData($deployment, $revision, $svcDep, $svcDepInfo, 'modify', $oldSvcDepInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcDepData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcDependency($deployment, $svcDep, $revision) {
        if (self::$init === false) self::init();
        $svcDepInfo = self::getDeploymentSvcDependency($deployment, $svcDep, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':svcdeps', $svcDep);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svcdep:'.$svcDep);
        $svcDepData = new ServiceDependencyData($deployment, $revision, $svcDep, $svcDepInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($svcDepData);
        return;
    }

    public static function getDeploymentSvcEscalations($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':svcescs');
    }

    public static function getDeploymentSvcEscalation($deployment, $svcEsc, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':svcesc:'.$svcEsc);
        $explodeOpts = array('escalation_options','contacts','contact_groups');
        foreach ($explodeOpts as $opt) {
            if ((isset($results[$opt])) && (preg_match('/,/', $results[$opt]))) {
                $results[$opt] = preg_split('/\s?,\s?/', $results[$opt]);
            }
            elseif ((isset($results[$opt])) && (!empty($results[$opt]))) {
                $results[$opt] = array($results[$opt]);
            }
        }
        return $results;
    }

    public static function existsDeploymentSvcEscalation($deployment, $svcEsc, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcescs', $svcEsc);
    }

    public static function getDeploymentSvcEscalationswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $svcEscalations = array();
        $escalations = self::getDeploymentSvcEscalations($deployment, $revision);
        foreach ($escalations as $escalation) {
            $svcEscalations[$escalation] = self::getDeploymentSvcEscalation($deployment, $escalation, $revision);
            $svcEscalations[$escalation]['deployment'] = $deployment;
        }
        return $svcEscalations;
    }

    public static function createDeploymentSvcEscalation($deployment, $svcEsc, array $svcEscInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcescs', $svcEsc)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svcesc:'.$svcEsc, $svcEscInfo)) !== false) {
                $svcEscData = new ServiceEscalationData($deployment, $revision, $svcEsc, $svcEscInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($svcEscData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentSvcEscalation($deployment, $svcEsc, array $svcEscInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':svcescs', $svcEsc)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':svcescs', $svcEsc);
        }
        $oldSvcEscInfo = self::getDeploymentSvcEscalation($deployment, $svcEsc, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svcesc:'.$svcEsc);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':svcesc:'.$svcEsc, $svcEscInfo)) !== false) {
            $svcEscData = new ServiceEscalationData($deployment, $revision, $svcEsc, $svcEscInfo, 'modify', $oldSvcEscInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($svcEscData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSvcEscalation($deployment, $svcEsc, $revision) {
        if (self::$init === false) self::init();
        $svcEscInfo = self::getDeploymentSvcEscalation($deployment, $svcEsc, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':svcescs', $svcEsc);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':svcesc:'.$svcEsc);
        $svcEscData = new ServiceEscalationData($deployment, $revision, $svcEsc, $svcEscInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($svcEscData);
        return;
    }

    public static function getDeploymentNodeTemplates($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':nodetemplates');
    }

    public static function getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision) {
        if (self::$init === false) self::init();
        $results = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':nodetemplate:'.$nodeTemplate);
        if (((!isset($results['subdeployment'])) || (empty($results['subdeployment']))) &&
            ($results['type'] != 'standard')) {
                $results['subdeployment'] = 'N/A';
        }
        $results['deployment'] = $deployment;
        return $results;
    }

    public static function getDeploymentNodeTemplatewInfo($deployment, $nodeTemplate, $revision, $templatemerge = false) {
        if (self::$init === false) self::init();
        $stdtemplates = self::getDeploymentStandardTemplateswInfo($deployment, $revision, true);
        $templateInfo = self::getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision);
        if (($templateInfo['type'] == 'standard') && ($templatemerge !== false)) {
            continue;
        }
        if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
            $templateInfo['services'] = explode(',', $templateInfo['services']);
        }
        if ((isset($templateInfo['nservices'])) && (!empty($templateInfo['nservices']))) {
            $templateInfo['nservices'] = explode(',', $templateInfo['nservices']);
        }
        if ($templatemerge !== false) {
            if ((isset($templateInfo['stdtemplate'])) && (!empty($templateInfo['stdtemplate']))) {
                $stdtemplate = $stdtemplates[$templateInfo['stdtemplate']];
                if ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) {
                    if ((isset($stdtemplate['hosttemplate'])) && (!empty($stdtemplate['hosttemplate']))) {
                        $templateInfo['hosttemplate'] = $stdtemplate['hosttemplate'];
                    }
                }
                if ((isset($stdtemplate['services'])) && (!empty($stdtemplate['services']))) {
                    $stdsvcs = explode(',', $stdtemplate['services']);
                    if (!empty($templateInfo['nservices'])) {
                        $nstdsvcs = $templateInfo['nservices'];
                        unset($templateInfo['nservices']);
                    } else {
                        $nstdsvcs = array();
                    }
                    $svcdiff = array_values(array_diff($stdsvcs, $nstdsvcs));
                    if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
                        $templateInfo['services'] = array_merge($templateInfo['services'], $svcdiff);
                    } else {
                        $templateInfo['services'] = $svcdiff;
                    }
                }
                unset($templateInfo['stdtemplate']);
            }
        }
        return $templateInfo;
    }

    public static function getDeploymentNodeTemplateswInfo($deployment, $revision, $templatemerge = false) {
        if (self::$init === false) self::init();
        $results = array();
        $templates = self::getDeploymentNodeTemplates($deployment, $revision);
        $stdtemplates = self::getDeploymentStandardTemplateswInfo($deployment, $revision, true);
        if (!empty($templates)) sort($templates);
        foreach ($templates as $template) {
            $templateInfo = self::getDeploymentNodeTemplate($deployment, $template, $revision);
            if (($templateInfo['type'] == 'standard') && ($templatemerge !== false)) {
                continue;
            }
            if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
                $templateInfo['services'] = explode(',', $templateInfo['services']);
            }
            if ((isset($templateInfo['nservices'])) && (!empty($templateInfo['nservices']))) {
                $templateInfo['nservices'] = explode(',', $templateInfo['nservices']);
            }
            if ($templatemerge !== false) {
                if ((isset($templateInfo['stdtemplate'])) && (!empty($templateInfo['stdtemplate']))) {
                    $stdtemplate = $stdtemplates[$templateInfo['stdtemplate']];
                    if ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) {
                        if ((isset($stdtemplate['hosttemplate'])) && (!empty($stdtemplate['hosttemplate']))) {
                            $templateInfo['hosttemplate'] = $stdtemplate['hosttemplate'];
                        }
                    }
                    if ((isset($stdtemplate['services'])) && (!empty($stdtemplate['services']))) {
                        $stdsvcs = explode(',', $stdtemplate['services']);
                        if (!empty($templateInfo['nservices'])) {
                            $nstdsvcs = $templateInfo['nservices'];
                            unset($templateInfo['nservices']);
                        } else {
                            $nstdsvcs = array();
                        }
                        $svcdiff = array_values(array_diff($stdsvcs, $nstdsvcs));
                        if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
                            $templateInfo['services'] = array_merge($templateInfo['services'], $svcdiff);
                        } else {
                            $templateInfo['services'] = $svcdiff;
                        }
                    }
                    unset($templateInfo['stdtemplate']);
                }
            }
            $results[$template] = $templateInfo;
        }
        return $results;
    }

    public static function existsDeploymentNodeTemplate($deployment, $nodeTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nodetemplates', $nodeTemplate);
    }

    public static function createDeploymentNodeTemplate($deployment, $nodeTemplate, array $nodeTemplateInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nodetemplates', $nodeTemplate)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo)) !== false) {
                if ($nodeTemplateInfo['type'] == 'standard') {
                    self::addDeploymentStandardTemplate($deployment, $nodeTemplate, $revision);
                }
                $nodeTemplateData = new NodeTemplateData($deployment, $revision, $nodeTemplate, $nodeTemplateInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($nodeTemplateData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentNodeTemplate($deployment, $nodeTemplate, array $nodeTemplateInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nodetemplates', $nodeTemplate)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nodetemplates', $nodeTemplate);
        }
        $oldNodeTemplateData = self::getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nodetemplate:'.$nodeTemplate);
        if (($return = NagRedis::hMset(md5('deployment:'.$deployment).':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo)) !== false) {
            if ($nodeTemplateInfo['type'] == 'standard') {
                self::addDeploymentStandardTemplate($deployment, $nodeTemplate, $revision);
            } elseif (($nodeTemplateInfo['type'] != 'standard') && ($oldNodeTemplateData['type'] == 'standard')) {
                self::removeDeploymentStandardTemplate($deployment, $nodeTemplate, $revision);
            }
            $nodeTemplateData = new NodeTemplateData($deployment, $revision, $nodeTemplate, $nodeTemplateInfo, 'modify', $oldNodeTemplateData);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nodeTemplateData);
            return true;
        }
        return false;
    }
    
    public static function deleteDeploymentNodeTemplate($deployment, $nodeTemplate, $revision) {
        if (self::$init === false) self::init();
        $nodeTemplateInfo = self::getDeploymentNodeTemplate($deployment, $nodeTemplate, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':nodetemplates', $nodeTemplate);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nodetemplate:'.$nodeTemplate);
        if ($nodeTemplateInfo['type'] == 'standard') {
            self::removeDeploymentStandardTemplate($deployment, $nodeTemplate, $revision);
        }
        $nodeTemplateData = new NodeTemplateData($deployment, $revision, $nodeTemplate, $nodeTemplateInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($nodeTemplateData);
    }

    public static function checkDeploymentNodeTemplateType($deployment, $nodeTemplate, $revision, $type) {
        if (self::$init === false) self::init();
        $nodeTemplateType = NagRedis::hGet(md5('deployment:'.$deployment).':'.$revision.':nodetemplate:'.$nodeTemplate, 'type');
        if ($nodeTemplateType == $type) {
            return true;
        }
        return false;
    }

    public static function addDeploymentStandardTemplate($deployment, $nodeTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':stdnodetemplates', $nodeTemplate);
    }

    public static function removeDeploymentStandardTemplate($deployment, $nodeTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':stdnodetemplates', $nodeTemplate);
    }

    public static function existsDeploymentStandardTemplate($deployment, $nodeTemplate, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':stdnodetemplates', $nodeTemplate);
    }

    public static function getDeploymentStandardTemplates($deployment, $revision, $commonmerge = false) {
        if (self::$init === false) self::init();
        $results = NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':stdnodetemplates');
        if ($commonmerge !== false) {
            $crepo = self::getDeploymentCommonRepo($deployment);
            $crev = self::getDeploymentRev($crepo);
            $cresults = NagRedis::sMembers(md5('deployment:'.$crepo).':'.$crev.':stdnodetemplates');
            $results = array_merge($results, $cresults);
        }
        return $results;
    }

    public static function getDeploymentStandardTemplateswInfo($deployment, $revision, $commonmerge = false) {
        if (self::$init === false) self::init();
        $results = array();
        $deployresults = NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':stdnodetemplates');
        foreach ($deployresults as $deploystd) {
            $results[$deploystd] = self::getDeploymentNodeTemplate($deployment, $deploystd, $revision);
        }
        if (($deployment != 'common') && ($commonmerge !== false)) {
            $crepo = self::getDeploymentCommonRepo($deployment);
            $crev = self::getDeploymentRev($crepo);
            $cresults = NagRedis::sMembers(md5('deployment:'.$crepo).':'.$crev.':stdnodetemplates');
            foreach ($cresults as $cresult) {
                if ((isset($results[$cresult])) && (!empty($results[$cresult]))) continue;
                $results[$cresult] = self::getDeploymentNodeTemplate($crepo, $cresult, $crev);
            }
        }
        return $results;
    }

    public static function existsDeploymentResourceCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':resourcecfg');
    }

    public static function getDeploymentResourceCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':resourcecfg');
    }

    public static function writeDeploymentResourceCfg($deployment, array $resources, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentResourceCfg($deployment, $revision)) {
            $oldResources = self::getDeploymentResourceCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':resourcecfg');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':resourcecfg', $resources);
            $deployResourceData = new ResourceConfigData($deployment, $revision, $resources, 'modify', $oldResources);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployResourceData);
            return true;
        } else {
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':resourcecfg', $resources);
            $deployResourceData = new ResourceConfigData($deployment, $revision, $resources, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployResourceData);
            return true;
        }
        return false;
    }

    public static function createDeploymentResourceCfg($deployment, array $resources, $revision) {
        if (self::$init === false) self::init();
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':resourcecfg', $resources);
        $deployResourceData = new ResourceConfigData($deployment, $revision, $resources, 'create');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployResourceData);
        return true;
    }

    public static function modifyDeploymentResourceCfg($deployment, array $resources, $revision) {
        if (self::$init === false) self::init();
        $oldResources = self::getDeploymentResourceCfg($deployment, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':resourcecfg');
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':resourcecfg', $resources);
        $deployResourceData = new ResourceConfigData($deployment, $revision, $resources, 'modify', $oldResources);
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployResourceData);
        return true;
    }

    public static function deleteDeploymentResourceCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentResourceCfg($deployment, $revision)) {
            $oldCfgInfo = self::getDeploymentResourceCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':resourcecfg');
            $deployResourceData = new ResourceConfigData($deployment, $revision, array(), 'delete', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployResourceData);
            return true;
        }
        return false;
    }

    public static function existsDeploymentModgearmanCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':modgearmancfg');
    }

    public static function getDeploymentModgearmanCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':modgearmancfg');
    }

    public static function writeDeploymentModgearmanCfg($deployment, array $cfgInfo, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentModgearmanCfg($deployment, $revision)) {
            $oldCfgInfo = self::getDeploymentModgearmanCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':modgearmancfg');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':modgearmancfg', $cfgInfo);
            $deployModgearmanData = new ModgearmanConfigData($deployment, $revision, $cfgInfo, 'modify', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployModgearmanData);
            return true;
        } else {
            NagRedis::hMset(md5('deployment:'.$deployment).':'.$revision.':modgearmancfg', $cfgInfo);
            $deployModgearmanData = new ModgearmanConfigData($deployment, $revision, $cfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployModgearmanData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentModgearmanCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentModgearmanCfg($deployment, $revision)) {
            $oldCfgInfo = self::getDeploymentModgearmanCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':modgearmancfg');
            $deployModgearmanData = new ModgearmanConfigData($deployment, $revision, array(), 'delete', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployModgearmanData);
            return true;
        }
        return false;
    }

    public static function existsDeploymentCgiCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':cgicfg');
    }

    public static function getDeploymentCgiCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':cgicfg');
    }

    public static function createDeploymentCgiCfg($deployment, $cfgInfo, $revision) {
        if (self::$init === false) self::init();
        NagRedis::hMset(md5('deployment:'.$deployment).':'.$revision.':cgicfg', $cfgInfo);
        $deployCgiData = new CgiConfigData($deployment, $revision, $cfgInfo, 'create');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployCgiData);
        return true;
    }

    public static function modifyDeploymentCgiCfg($deployment, $cfgInfo, $revision) {
        if (self::$init === false) self::init();
        $oldCfgInfo = self::getDeploymentCgiCfg($deployment, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':cgicfg');
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':cgicfg', $cfgInfo);
        $deployCgiData = new CgiConfigData($deployment, $revision, $cfgInfo, 'modify', $oldCfgInfo);
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployCgiData);
        return true;
    }

    public static function writeDeploymentCgiCfg($deployment, $cfgInfo, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentCgiCfg($deployment, $revision)) {
            $oldCfgInfo = self::getDeploymentCgiCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':cgicfg');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':cgicfg', $cfgInfo);
            $deployCgiData = new CgiConfigData($deployment, $revision, $cfgInfo, 'modify', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCgiData);
            return true;
        } else {
            NagRedis::hMset(md5('deployment:'.$deployment).':'.$revision.':cgicfg', $cfgInfo);
            $deployCgiData = new CgiConfigData($deployment, $revision, $cfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCgiData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentCgiCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentCgiCfg($deployment, $revision)) {
            $oldCfgInfo = self::getDeploymentCgiCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':cgicfg');
            $deployCgiData = new CgiConfigData($deployment, $revision, array(), 'delete', $oldCfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployCgiData);
            return true;
        }
        return false;
    }

    public static function existsDeploymentNagiosCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':nagioscfg');
    }

    public static function getDeploymentNagiosCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        $return = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':nagioscfg');
        ksort($return);
        return $return;
    }

    public static function createDeploymentNagiosCfg($deployment, $nagiosInfo, $revision) {
        if (self::$init === false) self::init();
        NagRedis::hMset(md5('deployment:'.$deployment).':'.$revision.':nagioscfg', $nagiosInfo);
        $deployNagiosData = new NagiosConfigData($deployment, $revision, $nagiosInfo, 'create');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployNagiosData);
        return true;
    }

    public static function modifyDeploymentNagiosCfg($deployment, $nagiosInfo, $revision) {
        if (self::$init === false) self::init();
        $oldNagiosInfo = self::getDeploymentNagiosCfg($deployment, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nagioscfg');
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nagioscfg', $nagiosInfo);
        $deployNagiosData = new NagiosConfigData($deployment, $revision, $nagiosInfo, 'modify', $oldNagiosInfo);
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployNagiosData);
        return true;
    }

    public static function writeDeploymentNagiosCfg($deployment, $nagiosInfo, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNagiosCfg($deployment, $revision)) {
            $oldNagiosInfo = self::getDeploymentNagiosCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nagioscfg');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nagioscfg', $nagiosInfo);
            $deployNagiosData = new NagiosConfigData($deployment, $revision, $nagiosInfo, 'modify', $oldNagiosInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNagiosData);
            return true;
        } else {
            NagRedis::hMset(md5('deployment:'.$deployment).':'.$revision.':nagioscfg', $nagiosInfo);
            $deployNagiosData = new NagiosConfigData($deployment, $revision, $nagiosInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNagiosData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNagiosCfg($deployment, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNagiosCfg($deployment, $revision)) {
            $oldNagiosInfo = self::getDeploymentNagiosCfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nagioscfg');
            $deployNagiosData = new NagiosConfigData($deployment, $revision, array(), 'delete', $oldNagiosInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNagiosData);
            return true;
        }
        return false;
    }

    public static function getDeploymentNRPECmds($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':nrpecmds');
    }

    public static function getDeploymentNRPECmd($deployment, $nrpeCmd, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':nrpecmd:'.$nrpeCmd);
    }

    public static function getDeploymentNRPECmdswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $results = array();
        $nrpeCmds = self::getDeploymentNRPECmds($deployment, $revision);
        foreach ($nrpeCmds as $nrpeCmd) {
            $results[$nrpeCmd] = self::getDeploymentNrpeCmd($deployment, $nrpeCmd, $revision);
        }
        return $results;
    }

    public static function getCommonMergedDeploymentNRPECmd($deployment, $nrpeCmd, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPECmd($deployment, $nrpeCmd, $revision) === true) {
            return self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
        } else {
            if ($deployment != 'common') {
                $commonRepo = self::getDeploymentCommonRepo($deployment);
                $commonRev = self::getDeploymentRev($commonRepo);
                if (self::existsDeploymentNRPECmd($commonRepo, $nrpeCmd, $commonRev) === true) {
                    return self::getDeploymentNRPECmd($commonRepo, $nrpeCmd, $commonRev);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentNRPECmdLine($deployment, $nrpeCmd, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGet(md5('deployment:'.$deployment).':'.$revision.':nrpecmd:'.$nrpeCmd, 'cmd_line');
    }

    public static function existsDeploymentNRPECmd($deployment, $nrpeCmd, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nrpecmds', $nrpeCmd);
    }

    public static function createDeploymentNRPECmd($deployment, $nrpeCmd, array $nrpeCmdInput, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nrpecmds', $nrpeCmd)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpecmd:'.$nrpeCmd, $nrpeCmdInput)) !== false) {
                $deployNRPECmdData = new NRPECmdData($deployment, $revision, $nrpeCmd, $nrpeCmdInput, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($deployNRPECmdData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentNRPECmd($deployment, $nrpeCmd, array $nrpeCmdInput, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nrpecmds', $nrpeCmd)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nrpecmds', $nrpeCmd);
        }
        $oldNRPECmdInfo = self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpecmd:'.$nrpeCmd);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpecmd:'.$nrpeCmd, $nrpeCmdInput)) !== false) {
            $deployNRPECmdData = new NRPECmdData($deployment, $revision, $nrpeCmd, $nrpeCmdInput, 'modify', $oldNRPECmdInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPECmdData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNRPECmd($deployment, $nrpeCmd, $revision) {
        if (self::$init === false) self::init();
        $nrpeCmdInfo = self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':nrpecmds', $nrpeCmd);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpecmd:'.$nrpeCmd);
        $deployNRPECmdData = new NRPECmdData($deployment, $revision, $nrpeCmd, $nrpeCmdInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployNRPECmdData);
        return;
    }

    public static function getCommonMergedDeploymentNRPECmds($deployment, $revision) {
        if (self::$init === false) self::init();
        $nrpeCmds = array();
        $deployNRPECmds = self::getDeploymentNRPECmds($deployment, $revision);
        foreach ($deployNRPECmds as $nrpeCmd) {
            $nrpeCmdInfo = self::getDeploymentNRPECmd($deployment, $nrpeCmd, $revision);
            if (empty($nrpeCmdInfo)) continue;
            $nrpeCmdInfo['deployment'] = $deployment;
            $nrpeCmds[$nrpeCmd] = $nrpeCmdInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonNRPECmds = self::getDeploymentNRPECmds($commonRepo, $commonRev);
            foreach ($commonNRPECmds as $nrpeCmd) {
                if ((isset($nrpeCmds[$nrpeCmd])) && (!empty($nrpeCmds[$nrpeCmd]))) continue;
                $nrpeCmdInfo = self::getDeploymentNRPECmd($commonRepo, $nrpeCmd, $commonRev);
                if (empty($nrpeCmdInfo)) continue;
                $nrpeCmdInfo['deployment'] = $commonRepo;
                $nrpeCmds[$nrpeCmd] = $nrpeCmdInfo;
            }
        }
        return $nrpeCmds;
    }

    public static function existsDeploymentNRPECfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':nrpecfg');
    }

    public static function getDeploymentNRPECfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':nrpecfg');
    }

    public static function createDeploymentNRPECfg($deployment, array $nrpeCfgInfo, $revision) {
        if (self::$init === false) self::init();
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpecfg', $nrpeCfgInfo);
        $deployNRPEData = new NRPECfgData($deployment, $revision, $nrpeCfgInfo, 'create');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployNRPEData);
        return true;
    }

    public static function modifyDeploymentNRPECfg($deployment, array $nrpeCfgInfo, $revision) {
        if (self::$init === false) self::init();
        $oldnrpemeta = self::getDeploymentNRPECfg($deployment, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpecfg');
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpecfg', $nrpeCfgInfo);
        $deployNRPEData = new NRPECfgData($deployment, $revision, $nrpeCfgInfo, 'modify', $oldnrpemeta);
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deployNRPEData);
        return true;
    }

    public static function writeDeploymentNRPECfg($deployment, array $nrpeCfgInfo, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPECfg($deployment, $revision)) {
            $oldnrpemeta = self::getDeploymentNRPECfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpecfg');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpecfg', $nrpeCfgInfo);
            $deployNRPEData = new NRPECfgData($deployment, $revision, $nrpeCfgInfo, 'modify', $oldnrpemeta);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPEData);
            return true;
        } else {
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpecfg', $nrpeCfgInfo);
            $deployNRPEData = new NRPECfgData($deployment, $revision, $nrpeCfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPEData);
            return true;
        }
        return false;
    }

    public static function importDeploymentNRPECfg($deployment, $revision, $location, $fileInfo) {
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
        $cfgInfo['cmds'] = implode(",", array_keys($fileInfo['cmds']));
        self::writeDeploymentNRPECfg($deployment, $cfgInfo, $revision);
    }

    public static function deleteDeploymentNRPECfg($deployment, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPECfg($deployment, $revision)) {
            $oldnrpemeta = self::getDeploymentNRPECfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpecfg');
            $deployNRPEData = new NRPECfgData($deployment, $revision, array(), 'delete', $oldnrpemeta);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deployNRPEData);
        }
        return true;
    }

    public static function existsDeploymentSupNRPECfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::exists(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg');
    }

    public static function getDeploymentSupNRPECfg($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg');
    }

    public static function createDeploymentSupNRPECfg($deployment, array $supNRPECfgInfo, $revision) {
        if (self::$init === false) self::init();
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg', $supNRPECfgInfo);
        $deploySupNRPEData = new SupNRPECfgData($deployment, $revision, $supNRPECfgInfo, 'create');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deploySupNRPEData);
        return true;
    }

    public static function modifyDeploymentSupNRPECfg($deployment, array $supNRPECfgInfo, $revision) {
        if (self::$init === false) self::init();
        $oldsupNRPECfgInfo = self::getDeploymentSupNRPECfg($deployment, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg');
        NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg', $supNRPECfgInfo);
        $deploySupNRPEData = new SupNRPECfgData($deployment, $revision, $supNRPECfgInfo, 'modify', $oldsupNRPECfgInfo);
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($deploySupNRPEData);
        return true;
    }

    public static function writeDeploymentSupNRPECfg($deployment, array $supNRPECfgInfo, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentSupNRPECfg($deployment, $revision)) {
            $oldsupNRPECfgInfo = self::getDeploymentSupNRPECfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg');
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg', $supNRPECfgInfo);
            $deploySupNRPEData = new SupNRPECfgData($deployment, $revision, $supNRPECfgInfo, 'modify', $oldsupNRPECfgInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deploySupNRPEData);
            return true;
        } else {
            NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg', $supNRPECfgInfo);
            $deploySupNRPEData = new SupNRPECfgData($deployment, $revision, $supNRPECfgInfo, 'create');
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deploySupNRPEData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSupNRPECfg($deployment, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentSupNRPECfg($deployment, $revision)) {
            $oldsupnrpemeta = self::getDeploymentSupNRPECfg($deployment, $revision);
            NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':supnrpecfg');
            $deploySupNRPEData = new SupNRPECfgData($deployment, $revision, array(), 'delete', $oldsupnrpemeta);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($deploySupNRPEData);
        }
        return true;
    }

    public static function importDeploymentSupNRPECfg($deployment, $revision, $location, $fileInfo) {
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
        $cfgInfo['cmds'] = implode(",", array_keys($fileInfo));
        self::writeDeploymentSupNRPECfg($deployment, $cfgInfo, $revision);
    }

    public static function getDeploymentNRPEPlugins($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':nrpeplugins');
    }
    
    public static function getDeploymentNRPEPlugin($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':nrpeplugin:'.$plugin);
    }

    public static function getCommonMergedDeploymentNRPEPlugin($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNRPEPlugin($deployment, $plugin, $revision) === true) {
            $results = self::getDeploymentNRPEPlugin($deployment, $plugin, $revision);
            $results['deployment'] = $deployment;
            return $results;
        } else {
            if ($deployment != 'common') {
                $commonRepo = self::getDeploymentCommonRepo($deployment);
                $commonRev = self::getDeploymentRev($commonRepo);
                if (self::existsDeploymentNRPEPlugin($commonRepo, $plugin, $commonRev) === true) {
                    $results = self::getDeploymentNRPEPlugin($commonRepo, $plugin, $commonRev);
                    $results['deployment'] = $commonRepo;
                    return $results;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentNRPEPluginswData($deployment, $revision) {
        if (self::$init === false) self::init();
        $nrpePlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        $results = array();
        foreach ($nrpePlugins as $nrpePlugin) {
            $results[$nrpePlugin] = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
        }
        return $results;
    }

    public static function existsDeploymentNRPEPlugin($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nrpeplugins', $plugin);
    }

    public static function getDeploymentNRPEPluginsMetaData($deployment, $revision) {
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

    public static function getCommonMergedDeploymentNRPEPluginsMetaData($deployment, $revision) {
        if (self::$init === false) self::init();
        $deployNRPEPlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        $nrpePlugins = array();
        foreach ($deployNRPEPlugins as $nrpePlugin) {
            $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
            if (empty($nrpePluginInfo)) continue;
            $nrpePluginInfo['deployment'] = $deployment;
            unset($nrpePluginInfo['file']);
            $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonNRPEPlugins = self::getDeploymentNRPEPlugins($commonRepo, $commonRev);
            foreach ($commonNRPEPlugins as $nrpePlugin) {
                if ((isset($nrpePlugins[$nrpePlugin])) && (!empty($nrpePlugins[$nrpePlugin]))) continue;
                $nrpePluginInfo = self::getDeploymentNRPEPlugin($commonRepo, $nrpePlugin, $commonRev);
                if (empty($nrpePluginInfo)) continue;
                $nrpePluginInfo['deployment'] = $commonRepo;
                unset($nrpePluginInfo['file']);
                $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
            }
        }
        return $nrpePlugins;
    }

    public static function getDeploymentNRPEPluginFileContents($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGet(md5('deployment:'.$deployment).':'.$revision.':nrpeplugin:'.$plugin, 'file');
    }

    public static function getDeploymentNRPEPluginswInfo($deployment, $revision) {
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

    public static function getCommonMergedDeploymentNRPEPlugins($deployment, $revision) {
        if (self::$init === false) self::init();
        $nrpePlugins = array();
        $deployNRPEPlugins = self::getDeploymentNRPEPlugins($deployment, $revision);
        foreach ($deployNRPEPlugins as $nrpePlugin) {
            $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
            if (empty($nrpePluginInfo)) continue;
            $nrpePluginInfo['deployment'] = $deployment;
            $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonNRPEPlugins = self::getDeploymentNRPEPlugins($commonRepo, $commonRev);
            foreach ($commonNRPEPlugins as $nrpePlugin) {
                if ((isset($nrpePlugins[$nrpePlugin])) && (!empty($nrpePlugins[$nrpePlugin]))) continue;
                $nrpePluginInfo = self::getDeploymentNRPEPlugin($commonRepo, $nrpePlugin, $commonRev);
                if (empty($nrpePluginInfo)) continue;
                $nrpePluginInfo['deployment'] = $commonRepo;
                $nrpePlugins[$nrpePlugin] = $nrpePluginInfo;
            }
        }
        return $nrpePlugins;
    }

    public static function createDeploymentNRPEPlugin($deployment, $nrpePlugin, array $nrpePluginInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nrpeplugins', $nrpePlugin)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpeplugin:'.$nrpePlugin, $nrpePluginInfo)) !== false) {
                $nrpePluginData = new NRPEPluginData($deployment, $revision, $nrpePlugin, $nrpePluginInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($nrpePluginData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentNRPEPlugin($deployment, $nrpePlugin, array $nrpePluginInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nrpeplugins', $nrpePlugin)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nrpeplugins', $nrpePlugin);
        }
        $oldNRPEPluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpeplugin:'.$nrpePlugin);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nrpeplugin:'.$nrpePlugin, $nrpePluginInfo)) !== false) {
            $nrpePluginData = new NRPEPluginData($deployment, $revision, $nrpePlugin, $nrpePluginInfo, 'modify', $oldNRPEPluginInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nrpePluginData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision) {
        if (self::$init === false) self::init();
        $nrpePluginInfo = self::getDeploymentNRPEPlugin($deployment, $nrpePlugin, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':nrpeplugins', $nrpePlugin);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nrpeplugin:'.$nrpePlugin);
        $nrpePluginData = new NRPEPluginData($deployment, $revision, $nrpePlugin, $nrpePluginInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($nrpePluginData);
        return;
    }

    public static function getDeploymentSupNRPEPlugins($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugins');
    }
    
    public static function getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision) {
        if (self::$init === false) self::init();
        $return = NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugin:'.$supNRPEPlugin);
        return $return;
    }

    public static function getCommonMergedDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision) === true) {
            $results = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            $results['deployment'] = $deployment;
            return $results;
        } else {
            if ($deployment != 'common') {
                $commonRepo = self::getDeploymentCommonRepo($deployment);
                $commonRev = self::getDeploymentRev($commonRepo);
                if (self::existsDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev) === true) {
                    $results = self::getDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev);
                    $results['deployment'] = $commonRepo;
                    return $results;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentSupNRPEPluginswData($deployment, $revision) {
        if (self::$init === false) self::init();
        $nrpePlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        $results = array();
        foreach ($nrpePlugins as $nrpePlugin) {
            $results[$nrpePlugin] = self::getDeploymentSupNRPEPlugin($deployment, $nrpePlugin, $revision);
        }
        return $results;
    }

    public static function existsDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugins', $supNRPEPlugin);
    }

    public static function getDeploymentSupNRPEPluginsMetaData($deployment, $revision) {
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            unset($supNRPEPluginInfo['file']);
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        return $supNRPEPlugins;
    }

    public static function getCommonMergedDeploymentSupNRPEPluginsMetaData($deployment, $revision) {
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            $supNRPEPluginInfo['deployment'] = $deployment;
            unset($supNRPEPluginInfo['file']);
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonSupNRPEPlugins = self::getDeploymentSupNRPEPlugins($commonRepo, $commonRev);
            foreach ($commonSupNRPEPlugins as $supNRPEPlugin) {
                if ((isset($supNRPEPlugins[$supNRPEPlugin])) && (!empty($supNRPEPlugins[$supNRPEPlugin]))) continue;
                $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev);
                if (empty($supNRPEPluginInfo)) continue;
                $supNRPEPluginInfo['deployment'] = $commonRepo;
                unset($supNRPEPluginInfo['file']);
                $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
            }
        }
        return $supNRPEPlugins;
    }

    public static function getDeploymentSupNRPEPluginFileContents($deployment, $supNRPEPlugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGet(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugin:'.$supNRPEPlugin, 'file');
    }

    public static function getDeploymentSupNRPEPluginswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        return $supNRPEPlugins;
    }

    public static function getCommonMergedDeploymentSupNRPEPlugins($deployment, $revision) {
        if (self::$init === false) self::init();
        $supNRPEPlugins = array();
        $deploySupNRPEPlugins = self::getDeploymentSupNRPEPlugins($deployment, $revision);
        foreach ($deploySupNRPEPlugins as $supNRPEPlugin) {
            $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
            if (empty($supNRPEPluginInfo)) continue;
            $supNRPEPluginInfo['deployment'] = $deployment;
            $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonSupNRPEPlugins = self::getDeploymentSupNRPEPlugins($commonRepo, $commonRev);
            foreach ($commonSupNRPEPlugins as $supNRPEPlugin) {
                if ((isset($supNRPEPlugins[$supNRPEPlugin])) && (!empty($supNRPEPlugins[$supNRPEPlugin]))) continue;
                $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($commonRepo, $supNRPEPlugin, $commonRev);
                if (empty($supNRPEPluginInfo)) continue;
                $supNRPEPluginInfo['deployment'] = $commonRepo;
                $supNRPEPlugins[$supNRPEPlugin] = $supNRPEPluginInfo;
            }
        }
        return $supNRPEPlugins;
    }

    public static function createDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, array $supNRPEPluginInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugins', $supNRPEPlugin)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugin:'.$supNRPEPlugin, $supNRPEPluginInfo)) !== false) {
                $supNRPEPluginData = new SupNRPEPluginData($deployment, $revision, $supNRPEPlugin, $supNRPEPluginInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($supNRPEPluginData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, array $supNRPEPluginInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugins', $supNRPEPlugin)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugins', $supNRPEPlugin);
        }
        $oldSupNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugin:'.$supNRPEPlugin);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugin:'.$supNRPEPlugin, $supNRPEPluginInfo)) !== false) {
            $supNRPEPluginData = new SupNRPEPluginData($deployment, $revision, $supNRPEPlugin, $supNRPEPluginInfo, 'modify', $oldSupNRPEPluginInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($supNRPEPluginData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision) {
        if (self::$init === false) self::init();
        $supNRPEPluginInfo = self::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugins', $supNRPEPlugin);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':supnrpeplugin:'.$supNRPEPlugin);
        $supNRPEPluginData = new SupNRPEPluginData($deployment, $revision, $supNRPEPlugin, $supNRPEPluginInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($supNRPEPluginData);
        return;
    }

    public static function getDeploymentNagiosPlugins($deployment, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sMembers(md5('deployment:'.$deployment).':'.$revision.':nagiosplugins');
    }
    
    public static function getDeploymentNagiosPlugin($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGetAll(md5('deployment:'.$deployment).':'.$revision.':nagiosplugin:'.$plugin);
    }

    public static function getCommonMergedDeploymentNagiosPlugin($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        if (self::existsDeploymentNagiosPlugin($deployment, $plugin, $revision) === true) {
            $results = self::getDeploymentNagiosPlugin($deployment, $plugin, $revision);
            $results['deployment'] = $deployment;
            return $results;
        } else {
            if ($deployment != 'common') {
                $commonRepo = self::getDeploymentCommonRepo($deployment);
                $commonRev = self::getDeploymentRev($commonRepo);
                if (self::existsDeploymentNagiosPlugin($commonRepo, $plugin, $commonRev) === true) {
                    $results = self::getDeploymentNagiosPlugin($commonRepo, $plugin, $commonRev);
                    $results['deployment'] = $commonRepo;
                    return $results;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public static function getDeploymentNagiosPluginswData($deployment, $revision) {
        if (self::$init === false) self::init();
        $nagiosplugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        $results = array();
        foreach ($nagiosplugins as $nagiosplugin) {
            $results[$nagiosplugin] = self::getDeploymentNagiosPlugin($deployment, $nagiosplugin, $revision);
        }
        return $results;
    }

    public static function existsDeploymentNagiosPlugin($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nagiosplugins', $plugin);
    }

    public static function getCommonMergedDeploymentNagiosPluginsMetaData($deployment, $revision) {
        if (self::$init === false) self::init();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        $nagiosplugins = array();
        foreach ($deployNagiosPlugins as $nagiosplugin) {
            $nagiospluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosplugin, $revision);
            if (empty($nagiospluginInfo)) continue;
            $nagiospluginInfo['deployment'] = $deployment;
            unset($nagiospluginInfo['file']);
            $nagiosplugins[$nagiosplugin] = $nagiospluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonNagiosPlugins = self::getDeploymentNagiosPlugins($commonRepo, $commonRev);
            foreach ($commonNagiosPlugins as $nagiosplugin) {
                if ((isset($nagiosplugins[$nagiosplugin])) && (!empty($nagiosplugins[$nagiosplugin]))) continue;
                $nagiosPluginInfo = self::getDeploymentNagiosPlugin($commonRepo, $nagiosplugin, $commonRev);
                if (empty($nagiosPluginInfo)) continue;
                $nagiosPluginInfo['deployment'] = $commonRepo;
                unset($nagiosPluginInfo['file']);
                $nagiosplugins[$nagiosplugin] = $nagiosPluginInfo;
            }
        }
        return $nagiosplugins;
    }

    public static function getDeploymentNagiosPluginsMetaData($deployment, $revision) {
        if (self::$init === false) self::init();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        $nagiosplugins = array();
        foreach ($deployNagiosPlugins as $nagiosplugin) {
            $nagiospluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosplugin, $revision);
            if (empty($nagiospluginInfo)) continue;
            unset($nagiospluginInfo['file']);
            unset($nagiospluginInfo['desc']);
            $nagiosplugins[$nagiosplugin] = $nagiospluginInfo;
        }
        return $nagiosplugins;
    }

    public static function getDeploymentNagiosPluginFileContents($deployment, $plugin, $revision) {
        if (self::$init === false) self::init();
        return NagRedis::hGet(md5('deployment:'.$deployment).':'.$revision.':nagiosplugin:'.$plugin, 'file');
    }

    public static function getDeploymentNagiosPluginswInfo($deployment, $revision) {
        if (self::$init === false) self::init();
        $nagiosPlugins = array();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        foreach ($deployNagiosPlugins as $nagiosPlugin) {
            $nagiosPluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
            if (empty($nagiosPluginInfo)) continue;
            $nagiosPlugins[$nagiosPlugin] = $nagiosPluginInfo;
        }
        return $nagiosPlugins;
    }

    public static function getCommonMergedDeploymentNagiosPlugins($deployment, $revision) {
        if (self::$init === false) self::init();
        $nagiosPlugins = array();
        $deployNagiosPlugins = self::getDeploymentNagiosPlugins($deployment, $revision);
        foreach ($deployNagiosPlugins as $nagiosPlugin) {
            $nagiosPluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
            if (empty($nagiosPluginInfo)) continue;
            $nagiosPluginInfo['deployment'] = $deployment;
            $nagiosPlugins[$nagiosPlugin] = $nagiosPluginInfo;
        }
        if ($deployment != 'common') {
            $commonRepo = self::getDeploymentCommonRepo($deployment);
            $commonRev = self::getDeploymentRev($commonRepo);
            $commonNagiosPlugins = self::getDeploymentNagiosPlugins($commonRepo, $commonRev);
            foreach ($commonNagiosPlugins as $nagiosPlugin) {
                if ((isset($nagiosPlugins[$nagiosPlugin])) && (!empty($nagiosPlugins[$nagiosPlugin]))) continue;
                $nagiosPluginInfo = self::getDeploymentNagiosPlugin($commonRepo, $nagiosPlugin, $commonRev);
                if (empty($nagiosPluginInfo)) continue;
                $nagiosPluginInfo['deployment'] = $commonRepo;
                $nagiosPlugins[$nagiosPlugin] = $nagiosPluginInfo;
            }
        }
        return $nagiosPlugins;
    }

    public static function createDeploymentNagiosPlugin($deployment, $nagiosPlugin, array $nagiosPluginInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nagiosplugins', $nagiosPlugin)) !== false) {
            if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nagiosplugin:'.$nagiosPlugin, $nagiosPluginInfo)) !== false) {
                $nagiosPluginData = new NagiosPluginData($deployment, $revision, $nagiosPlugin, $nagiosPluginInfo, 'create');
                self::addAuditUserLog($deployment, $revision);
                self::$log->addToLog($nagiosPluginData);
                return true;
            }
        }
        return false;
    }

    public static function modifyDeploymentNagiosPlugin($deployment, $nagiosPlugin, array $nagiosPluginInfo, $revision) {
        if (self::$init === false) self::init();
        if (($return = NagRedis::sIsMember(md5('deployment:'.$deployment).':'.$revision.':nagiosplugins', $nagiosPlugin)) === false) {
            NagRedis::sAdd(md5('deployment:'.$deployment).':'.$revision.':nagiosplugins', $nagiosPlugin);
        }
        $oldNagiosPluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nagiosplugin:'.$nagiosPlugin);
        if (($return = NagRedis::hMSet(md5('deployment:'.$deployment).':'.$revision.':nagiosplugin:'.$nagiosPlugin, $nagiosPluginInfo)) !== false) {
            $nagiosPluginData = new NagiosPluginData($deployment, $revision, $nagiosPlugin, $nagiosPluginInfo, 'modify', $oldNagiosPluginInfo);
            self::addAuditUserLog($deployment, $revision);
            self::$log->addToLog($nagiosPluginData);
            return true;
        }
        return false;
    }

    public static function deleteDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision) {
        if (self::$init === false) self::init();
        $nagiosPluginInfo = self::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $revision);
        NagRedis::sRem(md5('deployment:'.$deployment).':'.$revision.':nagiosplugins', $nagiosPlugin);
        NagRedis::del(md5('deployment:'.$deployment).':'.$revision.':nagiosplugin:'.$nagiosPlugin);
        $nagiosPluginData = new NagiosPluginData($deployment, $revision, $nagiosPlugin, $nagiosPluginInfo, 'delete');
        self::addAuditUserLog($deployment, $revision);
        self::$log->addToLog($nagiosPluginData);
        return;
    }

}
