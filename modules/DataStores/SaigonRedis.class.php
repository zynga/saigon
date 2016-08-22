<?php
//
// Copyright (c) 2014, Pinterest
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class SaigonRedis implements DataStoreAPI
{

    protected $redisHosts = null;
    protected $redis = null;
    protected $timestamp = null;

    public function __construct()
    {
        function getRedisHashFunc($key) {
            return substr($key, 0, 3);
        }
        $this->redisHosts = $this->buildRedisHostArray(REDIS_CLUSTER);
        $this->redis = new RedisArray($this->redisHosts, array("function" => "getRedisHashFunc"));
        $this->timestamp = time();
    }

    private function reconnect()
    {
        if ((time() - $this->timestamp) > 60) {
            $this->redis =
                new RedisArray($this->redisHosts, array("function" => "getRedisHashFunc"));
            $this->timestamp = time();
        }
    }

    private function buildRedisHostArray($hostConfig)
    {
        if ((!isset($hostConfig)) || (empty($hostConfig))) {
            return false;
        }
        $results = array();
        if (preg_match("/,/", $hostConfig)) {
            $hosts = preg_split("/\s?,\s?/", $hostConfig);
            foreach ($hosts as $host) {
                array_push($results, $host);
            }
        } else {
                array_push($results, $hostConfig);
        }
        return $results;
    }

    private function createBaseKey($namespace, $deployment = false)
    {
        if ( $deployment !== false ) {
            return md5($namespace.':'.$deployment);
        }
        else {
            return md5($namespace);
        }
    }

    public function addAuditUserLog($deployment, $revision, $user)
    {
        $baseKey = $this->createBaseKey('deployment-audit', $deployment);
        $this->redis->sAdd($baseKey.':'.$revision.':users', $user);
    }

    public function getAuditLog($deployment)
    {
        $this->reconnect();
        $revisions = $this->getDeploymentAllRevs($deployment);
        $results = array();
        $baseKey = $this->createBaseKey('deployment-audit', $deployment);
        if (is_array($revisions)) {
            foreach ($revisions as $revision) {
                $revnote = $this->redis->get($baseKey.':'.$revision.':revnote');
                if ($revnote === false) $results[$revision]['revnote'] = 'Not Available';
                else $results[$revision]['revnote'] = base64_decode($revnote);
                $users = $this->redis->sMembers($baseKey.':'.$revision.':users');
                if (($users === false) || (empty($users))) $results[$revision]['users'] = 'Not Available';
                else $results[$revision]['users'] = implode(", ", $users);
                $time = $this->redis->get($baseKey.':'.$revision.':revtime');
                if ($time === false) $results[$revision]['revtime'] = 'Not Available';
                else $results[$revision]['revtime'] = $time;
            }
        } else {
            $revnote = $this->redis->get($baseKey.':'.$revisions.':revnote');
            if ($revnote === false) $results[$revisions]['revnote'] = 'Not Available';
            else $results[$revisions]['revnote'] = base64_decode($revnote);
            $users = $this->redis->sMembers($baseKey.':'.$revisions.':users');
            if (($users === false) || (empty($users))) $results[$revisions]['users'] = 'Not Available';
            else $results[$revisions]['users'] = implode(", ", $users);
            $time = $this->redis->get($baseKey.':'.$revision.':revtime');
            if ($time === false) $results[$revision]['revtime'] = 'Not Available';
            else $results[$revision]['revtime'] = $time;
        }
        return $results;
    }

    public function setAuditLog($deployment, $revision, array $revisionData)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment-audit', $deployment);
        $this->redis->set(
            $baseKey.':'.$revision.':revnote', base64_encode($revisionData['revnote'])
        );
        $this->redis->set($baseKey.':'.$revision.':revtime', $revisionData['revtime']);
        $users = explode(',', $revisionData['users']);
        foreach ($users as $user) {
            $this->redis->sAdd($baseKey.':'.$revision.':users', $user);
        }
        return true;
    }

    public function getCommonRepos()
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('commonrepos');
        return $this->redis->sMembers($baseKey);
    }

    public function addCommonRepo($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('commonrepos');
        return $this->redis->sAdd($baseKey, $deployment);
    }

    public function delCommonRepo($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('commonrepos');
        return $this->redis->sRem($baseKey, $deployment);
    }

    public function getDeploymentRev($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $revision = $this->redis->hGet($baseKey, 'revision');
        if ((empty($revision)) || ($revision === false)) { 
            return false;
        } else {
            return $revision;
        }
    }

    public function getDeploymentNextRev($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'nextrevision');
    }

    public function getDeploymentPrevRev($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'prevrevision');
    }

    public function getDeploymentRevs($deployment)
    {
        $results = array();
        $results['currrev'] = $this->getDeploymentRev($deployment);
        $results['nextrev'] = $this->getDeploymentNextRev($deployment);
        $results['prevrev'] = $this->getDeploymentPrevRev($deployment);
        return $results;
    }

    public function getDeploymentAllRevs($deployment)
    {
        $this->reconnect();
        $tmpResults = array();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $keys = $this->redis->keys($baseKey.':*');
        $keys = array_shift($keys);
        foreach ($keys as $key) {
            $split = preg_split('/:/', $key);
            if ((!isset($tmpResults[$split[1]])) &&
                (preg_match("/\d+/", $split[1]))) {
                $tmpResults[$split[1]] = "enabled_".$split[1];
            }
        }
        $results = array_keys($tmpResults);
        return $results;
    }

    public function setDeploymentRevs($deployment, $from, $to, $note) 
    {
        $this->reconnect();
        $revinfo = array('revision' => $to, 'prevrevision' => $from);
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->hMSet($baseKey, $revinfo);
        $baseKey = $this->createBaseKey('deployment-audit', $deployment);
        $this->redis->set($baseKey.':'.$to.':revnote', base64_encode($note));
        $this->redis->set($baseKey.':'.$to.':revtime', time());
    }

    public function setDeploymentAllRevs($deployment, $prev, $curr, $next)
    {
        $this->reconnect();
        $revinfo = array('revision' => $curr, 'prevrevision' => $prev, 'nextrevision' => $next);
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->hmSet($baseKey, $revinf);
    }

    public function deleteDeploymentRev($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (is_array($revision)) {
            foreach ($revision as $subrevision) {
                $search_keys = $this->redis->keys($baseKey.':'.$subrevision.':*');
                $keys = array_shift($search_keys);
                $this->redis->del($keys);
            }
        } else {
            $search_keys = $this->redis->keys($baseKey.':'.$revision.':*');
            $keys = array_shift($search_keys);
            $this->redis->del($keys);
        }
    }

    public function deleteDeployment($deployment)
    {
        $this->reconnect();
        $deploystyle = $this->getDeploymentStyle($deployment);
        if ($deploystyle == 'commonrepo') {
            $this->delCommonRepo($deployment);
        }
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $search_keys = $this->redis->keys($baseKey.'*');
        $keys = array_shift($search_keys);
        $this->redis->del($keys);
        $baseKey = $this->createBaseKey('deployment-audit', $deployment);
        $search_auditkeys = $this->redis->keys($baseKey.'*');
        $auditkeys = array_shift($search_auditkeys);
        $this->redis->del($auditkeys);
        $baseKey = $this->createBaseKey('deployments');
        $this->redis->sRem($baseKey, $deployment);
    }

    public function incrDeploymentNextRev($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hIncrBy($baseKey, 'nextrevision', 1);
    }

    public function existsDeploymentRev($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->keys($baseKey.':'.$revision.':*');
    }

    public function createDeployment(
        $deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployments');
        if (($return = $this->redis->sAdd($baseKey, $deployment)) !== false) {
            $baseKey = $this->createBaseKey('deployment', $deployment);
            if (($return = $this->redis->hMSet($baseKey, $deployInfo)) !== false) {
                $this->redis->hIncrBy($baseKey, 'revision', 1);
                $this->redis->hIncrBy($baseKey, 'nextrevision', 2);
                if (!empty($deployHostSearch)) {
                    foreach ($deployHostSearch as $md5Key => $tmpArray) {
                        $this->redis->sAdd($baseKey.':hostsearches', $md5Key);
                        $this->redis->hMset($baseKey.':hostsearch:'.$md5Key, $tmpArray);
                    }
                }
                if (!empty($deployStaticHosts)) {
                    $this->redis->set($baseKey.':statichosts', json_encode($deployStaticHosts));
                }
                return true;
            }
            /* Cleanup */
            $baseKey = $this->createBaseKey('deployments');
            $this->redis->sRem($baseKey, $deployment);
            return false;
        }
        return false;
    }

    public function modifyDeployment(
        $deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployments');
        if (($return = $this->redis->sIsMember($baseKey, $deployment)) !== false) {
            $baseKey = $this->createBaseKey('deployment', $deployment);
            $oldHostSearches = $this->redis->sMembers($baseKey.':hostsearches');
            if (($return = $this->redis->hMSet($baseKey, $deployInfo)) !== false) {
                /* Delete the old data */
                $this->redis->del($baseKey.':statichosts');
                if (!empty($oldHostSearches)) {
                    foreach ($oldHostSearches as $md5Key) {
                        $this->delDeploymentDynamicHost($deployment, $md5Key);
                    }
                }
                /* Write the new data */
                if (!empty($deployHostSearch)) {
                    foreach ($deployHostSearch as $md5Key => $tmpArray) {
                        $this->addDeploymentDynamicHost($deployment, $md5Key, $tmpArray);
                    }
                }
                if (!empty($deployStaticHosts)) {
                    $this->redis->set($baseKey.':statichosts', json_encode($deployStaticHosts));
                }
                return true;
            }
            return false;
        }
        return false;
    }

    public function addDeploymentDynamicHost($deployment, $md5Key, array $hostInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->sAdd($baseKey.':hostsearches', $md5Key);
        $this->redis->hMset($baseKey.':hostsearch:'.$md5Key, $hostInfo);
    }

    public function delDeploymentDynamicHost($deployment, $md5Key)
    {
        $this->reconnect();
        $hostInfo = array();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sIsMember($baseKey.':hostsearches', $md5Key)) === true) {
            $hostInfo = $this->redis->hGetAll($baseKey.':hostsearch:'.$md5Key);
            $this->redis->sRem($baseKey.':hostsearches', $md5Key);
            $this->redis->del($baseKey.':hostsearch:'.$md5Key);
        }
        return $hostInfo;
    }

    public function addDeploymentStaticHost($deployment, $ip, array $hostInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
            $ip = NagMisc::encodeIP($ip);
        }
        $staticHosts = $this->redis->get($baseKey.':statichosts');
        $newStaticHosts = json_decode($staticHosts, true);
        $newStaticHosts[$ip] = $hostInfo;
        $this->redis->set($baseKey.':statichosts', json_encode($newStaticHosts));
    }

    public function delDeploymentStaticHost($deployment, $ip)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
            $ip = NagMisc::encodeIP($ip);
        }
        $staticHosts = $this->redis->get($baseKey.':statichosts');
        $staticHosts = json_decode($staticHosts, true);
        if ((isset($staticHosts[$ip])) && (!empty($staticHosts[$ip]))) {
            $host = $staticHosts[$ip];
            unset($staticHosts[$ip]);
            $this->redis->set($baseKey.':statichosts', json_encode($staticHosts));
            return $host;
        }
        return false;
    }

    public function getDeployments()
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployments');
        return $this->redis->sMembers($baseKey);
    }

    public function existsDeployment($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployments');
        return $this->redis->sIsMember($baseKey, $deployment);
    }

    public function getDeploymentInfo($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey);
    }

    public function getDeploymentCommonRepo($deployment)
    {
        if ($deployment == 'common') return 'undefined';
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'commonrepo');
    }

    public function getDeploymentHostSearches($deployment)
    {
        $this->reconnect();
        $results = array();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($this->redis->exists($baseKey.':hostsearches')) {
            $members = $this->redis->sMembers($baseKey.':hostsearches');
            foreach ($members as $member) {
                $memberInfo = $this->redis->hGetAll($baseKey.':hostsearch:'.$member);
                $results[$member] = $memberInfo;
            }
        }
        return $results;
    }

    public function getDeploymentStaticHosts($deployment)
    {
        $this->reconnect();
        $results = array();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($this->redis->exists($baseKey.':statichosts')) {
            $jsonEnc = $this->redis->get($baseKey.':statichosts');
            $results = json_decode($jsonEnc, true);
        }
        return $results;
    }

    public function getDeploymentAuthGroup($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'authgroups');
    }

    public function getDeploymentLdapGroup($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'ldapgroups');
    }

    public function getDeploymentAliasTemplate($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'aliastemplate');
    }

    public function getDeploymentGlobalNegate($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'deploynegate');
    }

    public function getDeploymentStyle($deployment)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey, 'deploystyle');
    }

    public function getDeploymentMiscSettings($deployment)
    {
        $this->reconnect();
        $results = array();
        $results['aliastemplate'] = $this->getDeploymentAliasTemplate($deployment);
        $results['deploystyle'] = $this->getDeploymentStyle($deployment);
        $results['deploynegate'] = $this->getDeploymentGlobalNegate($deployment);
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $ensharding = $this->redis->hGet($baseKey, 'ensharding');
        if ((empty($ensharding)) || ($ensharding === false)) {
            $results['ensharding'] = 'off';
        } else {
            $results['ensharding'] = $ensharding;
        }
        if ($results['ensharding'] == 'on') {
            $results['shardkey'] = $this->redis->hGet($baseKey, 'shardkey');
            $results['shardcount'] = $this->redis->hGet($baseKey, 'shardcount');
        }
        if (CHAT_INTEGRATION === true) {
            $results['chat_rooms'] = $this->redis->hGet($baseKey, 'chat_rooms');
        }
        return $results;
    }

    public function getDeploymentCommands($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':commands');
    }

    public function existsDeploymentCommand($deployment, $revision, $command)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':commands', $command);
    }

    public function getDeploymentCommand($deployment, $revision, $command)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':command:'.$command);
    }

    public function getDeploymentCommandExec($deployment, $revision, $command)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':command:'.$command, 'command_line');
    }

    public function createDeploymentCommand($deployment, $revision, $command, array $commandInput)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentCommand($deployment, $revision, $command)) === false) {
            if (($return = $this->redis->sAdd($baseKey.':'.$revision.':commands', $command)) !== false) {
                if (($return = $this->redis->hMSet($baseKey.':'.$revision.':command:'.$command, $commandInput)) !== false) {
                    return true;
                }
                $this->redis->sRem($baseKey.':'.$revision.':commands', $command);
                return false;
            }
            return false;
        }
        return false;
    }

    public function modifyDeploymentCommand($deployment, $revision, $command, array $commandInput)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentCommand($deployment, $revision, $command)) === true) {
            $this->redis->del($baseKey.':'.$revision.':command:'.$command);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':command:'.$command, $commandInput)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentCommand($deployment, $revision, $command)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $commandInfo = $this->getDeploymentCommand($deployment, $revision, $command);
        $this->redis->sRem($baseKey.':'.$revision.':commands', $command);
        $this->redis->del($baseKey.':'.$revision.':command:'.$command);
        return $commandInfo;
    }

    public function getDeploymentTimeperiods($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':timeperiods');
    }

    public function existsDeploymentTimeperiod($deployment, $revision, $timePeriod)
	{
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':timeperiods', $timePeriod);
    }

    public function existsDeploymentTimeperiodData($deployment, $revision, $timePeriod)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':timeperiod:'.$timePeriod);
    }

    public function getDeploymentTimeperiod($deployment, $revision, $timePeriod)
	{
        $this->reconnect();
        if (($return = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === false) {
            return false;
        }
        $results = $this->getDeploymentTimeperiodInfo($deployment, $revision, $timePeriod);
        if (empty($results)) {
            return false;
        }
        $results['times'] = $this->getDeploymentTimeperiodData($deployment, $revision, $timePeriod);
        return $results;
    }

    public function getDeploymentTimeperiodInfo($deployment, $revision, $timePeriod)
	{
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':info');
    }

    public function getDeploymentTimeperiodData($deployment, $revision, $timePeriod)
	{
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $members = $this->redis->sMembers($baseKey.':'.$revision.':timeperiod:'.$timePeriod);
        $results = array();
        foreach ($members as $member) {
            $results[$member] =
                $this->redis->hGetAll($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':data:'.$member);
        }
        return $results;
    }

    public function createDeploymentTimeperiod(
        $deployment, $revision, $timePeriod, array $timePeriodInfo, array $timePeriodData
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':timeperiods', $timePeriod)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':info', $timePeriodInfo)) !== false) {
                foreach ($timePeriodData as $md5Key => $timeArray) {
                    $this->redis->sAdd($baseKey.':'.$revision.':timeperiod:'.$timePeriod, $md5Key);
                    $this->redis->hMSet($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key, $timeArray);
                }
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':timeperiods', $timePeriod);
            return false;
        }
        return false;
    }

    public function modifyDeploymentTimeperiod(
        $deployment, $revision, $timePeriod, array $timePeriodInfo, array $timePeriodData
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === true) {
            $oldTimeperiodKeys =
                $this->redis->sMembers($baseKey.':'.$revision.':timeperiod:'.$timePeriod);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':info', $timePeriodInfo)) !== false) {
                /* Delete the old data */
                foreach ($oldTimeperiodKeys as $timePeriodKey) {
                    $this->redis->sRem($baseKey.':'.$revision.':timeperiod:'.$timePeriod, $timePeriodKey);
                    $this->redis->del($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':data:'.$timePeriodKey);
                }
                /* Create the new data */
                foreach ($timePeriodData as $md5Key => $timeArray) {
                    $this->redis->sAdd($baseKey.':'.$revision.':timeperiod:'.$timePeriod, $md5Key);
                    $this->redis->hMSet($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key, $timeArray);
                }
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentTimeperiod($deployment, $revision, $timePeriod)
	{
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldTimePeriodData = $this->getDeploymentTimeperiod($deployment, $revision, $timePeriod);
        $this->redis->sRem($baseKey.':'.$revision.':timeperiods', $timePeriod);
        $this->redis->del($baseKey.':'.$revision.':timeperiod:'.$timePeriod);
        $this->redis->del($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':info');
        foreach ($oldTimePeriodData['times'] as $md5Key => $md5KeyArray) {
            $this->redis->del($baseKey.':'.$revision.':timeperiod:'.$timePeriod.':data:'.$md5Key);
        }
        return $oldTimePeriodData;
    }

    public function getDeploymentContacts($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':contacts');
    }

    public function getDeploymentContact($deployment, $revision, $contact)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $contactInfo = $this->redis->hGetAll($baseKey.':'.$revision.':contact:'.$contact);
        $explodeOpts = array('host_notification_options','service_notification_options');
        foreach ($explodeOpts as $opt) {
            if ((isset($contactInfo[$opt])) &&
                (preg_match('/,/', $contactInfo[$opt]))) {
                $contactInfo[$opt] = preg_split('/\s?,\s?/', $contactInfo[$opt]);
            }
            elseif ((isset($contactInfo[$opt])) && (!empty($contactInfo[$opt]))) {
                $contactInfo[$opt] = array($contactInfo[$opt]);
            }
            else {
                unset($contactInfo[$opt]);
            }
        }
        return $contactInfo;
    }

    public function existsDeploymentContact($deployment, $revision, $contact)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':contacts', $contact);
    }

    public function createDeploymentContact($deployment, $revision, $contact, array $contactInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('host_notification_options','service_notification_options');
        foreach ($implodeOpts as $opt) {
            if ((isset($contactInfo[$opt])) &&
                (!empty($contactInfo[$opt])) &&
                (is_array($contactInfo[$opt]))) {
                $contactInfo[$opt] = implode(',', $contactInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':contacts', $contact)) !== false) {
            if ($return = $this->redis->hMSet($baseKey.':'.$revision.':contact:'.$contact, $contactInfo) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':contacts', $contact);
            return false;
        }
        return false;
    }

    public function modifyDeploymentContact($deployment, $revision, $contact, array $contactInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('host_notification_options','service_notification_options');
        foreach ($implodeOpts as $opt) {
            if ((isset($contactInfo[$opt])) &&
                (!empty($contactInfo[$opt])) &&
                (is_array($contactInfo[$opt]))) {
                $contactInfo[$opt] = implode(',', $contactInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentContact($deployment, $revision, $contact)) === true) {
            $this->redis->del($baseKey.':'.$revision.':contact:'.$contact);
            if ($return = $this->redis->hMSet($baseKey.':'.$revision.':contact:'.$contact, $contactInfo) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentContact($deployment, $revision, $contact)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $contactInfo = $this->getDeploymentContact($deployment, $revision, $contact);
        $this->redis->sRem($baseKey.':'.$revision.':contacts', $contact);
        $this->redis->del($baseKey.':'.$revision.':contact:'.$contact);
        return $contactInfo;
    }

    public function getDeploymentContactGroups($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':contactgroups');
    }

    public function getDeploymentContactGroup($deployment, $revision, $contactGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $contactGroupInfo =
            $this->redis->hGetAll($baseKey.':'.$revision.':contactgroup:'.$contactGroup);
        $explodeOpts = array('members','contactgroup_members');
        foreach ($explodeOpts as $opt) {
            if ((isset($contactGroupInfo[$opt])) &&
                (preg_match('/,/', $contactGroupInfo[$opt]))) {
                $contactGroupInfo[$opt] = preg_split('/\s?,\s?/', $contactGroupInfo[$opt]);
            }
            elseif ((isset($contactGroupInfo[$opt])) && (!empty($contactGroupInfo[$opt]))) {
                $contactGroupInfo[$opt] = array($contactGroupInfo[$opt]);
            }
            else {
                unset($contactGroupInfo[$opt]);
            }
        }
        return $contactGroupInfo;
    }

    public function existsDeploymentContactGroup($deployment, $revision, $contactGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':contactgroups', $contactGroup);
    }

    public function createDeploymentContactGroup(
        $deployment, $revision, $contactGroup, array $contactGroupInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('members','contactgroup_members');
        foreach ($implodeOpts as $opt) {
            if ((isset($contactGroupInfo[$opt])) &&
                (!empty($contactGroupInfo[$opt])) &&
                (is_array($contactGroupInfo[$opt]))) {
                $contactGroupInfo[$opt] = implode(',', $contactGroupInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':contactgroups', $contactGroup)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':contactgroup:'.$contactGroup, $contactGroupInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':contactgroups', $contactGroup);
            return false;
        }
        return false;
    }

    public function modifyDeploymentContactGroup(
        $deployment, $revision, $contactGroup, array $contactGroupInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('members','contactgroup_members');
        foreach ($implodeOpts as $opt) {
            if ((isset($contactGroupInfo[$opt])) &&
                (!empty($contactGroupInfo[$opt])) &&
                (is_array($contactGroupInfo[$opt]))) {
                $contactGroupInfo[$opt] = implode(',', $contactGroupInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentContactGroup($deployment, $revision, $contactGroup)) === true) {
            $this->redis->del($baseKey.':'.$revision.':contactgroup:'.$contactGroup);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':contactgroup:'.$contactGroup, $contactGroupInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentContactGroup($deployment, $revision, $contactGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldContactGroupInfo =
            $this->getDeploymentContactGroup($deployment, $revision, $contactGroup);
        $this->redis->sRem($baseKey.':'.$revision.':contactgroups', $contactGroup);
        $this->redis->del($baseKey.':'.$revision.':contactgroup:'.$contactGroup);
        return $oldContactGroupInfo;
    }

    public function getDeploymentContactTemplates($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':contacttemplates');
    }

    public function existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':contacttemplates', $contactTemplate);
    }

    public function getDeploymentContactTemplate($deployment, $revision, $contactTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $contactTemplateInfo =
            $this->redis->hGetAll($baseKey.':'.$revision.':contacttemplate:'.$contactTemplate);
        $explodeOpts = array('host_notification_options','service_notification_options');
        foreach ($explodeOpts as $opt) {
            if ((isset($contactTemplateInfo[$opt])) &&
                (preg_match('/,/', $contactTemplateInfo[$opt]))) {
                $contactTemplateInfo[$opt] = preg_split('/\s?,\s?/', $contactTemplateInfo[$opt]);
            }
            elseif ((isset($contactTemplateInfo[$opt])) && (!empty($contactTemplateInfo[$opt]))) {
                $contactTemplateInfo[$opt] = array($contactTemplateInfo[$opt]);
            }
            else {
                unset($contactTemplateInfo[$opt]);
            }
        }
        return $contactTemplateInfo;
    }

    public function createDeploymentContactTemplate(
        $deployment, $revision, $contactTemplate, array $contactTemplateInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('host_notification_options','service_notification_options');
        foreach ($implodeOpts as $opt) {
            if ((isset($contactTemplateInfo[$opt])) &&
                (!empty($contactTemplateInfo[$opt])) &&
                (is_array($contactTemplateInfo[$opt]))) {
                $contactTemplateInfo[$opt] = implode(',', $contactTemplateInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':contacttemplates', $contactTemplate)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':contacttemplate:'.$contactTemplate, $contactTemplateInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':contacttemplates', $contactTemplate);
            return false;
        }
        return false;
    }

    public function modifyDeploymentContactTemplate(
        $deployment, $revision, $contactTemplate, array $contactTemplateInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('host_notification_options','service_notification_options');
        foreach ($implodeOpts as $opt) {
            if ((isset($contactTemplateInfo[$opt])) &&
                (!empty($contactTemplateInfo[$opt])) &&
                (is_array($contactTemplateInfo[$opt]))) {
                $contactTemplateInfo[$opt] = implode(',', $contactTemplateInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)) === true) {
            $this->redis->del($baseKey.':'.$revision.':contacttemplate:'.$contactTemplate);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':contacttemplate:'.$contactTemplate, $contactTemplateInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentContactTemplate($deployment, $revision, $contactTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldContactInfo =
            $this->getDeploymentContactTemplate($deployment, $revision, $contactTemplate);
        $this->redis->sRem($baseKey.':'.$revision.':contacttemplates', $contactTemplate);
        $this->redis->del($baseKey.':'.$revision.':contacttemplate:'.$contactTemplate);
        return $oldContactInfo;
    }

    public function getDeploymentHostTemplates($deployment, $revision) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':hosttemplates');
    }

    public function existsDeploymentHostTemplate($deployment, $revision, $hostTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':hosttemplates', $hostTemplate);
    }

    public function getDeploymentHostTemplate($deployment, $revision, $hostTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $hostTemplateInfo =
            $this->redis->hGetAll($baseKey.':'.$revision.':hosttemplate:'.$hostTemplate);
        $explodeOpts = array('contacts','contact_groups','notification_options');
        foreach ($explodeOpts as $opt) {
            if ((isset($hostTemplateInfo[$opt])) && (preg_match('/,/', $hostTemplateInfo[$opt]))) {
                $hostTemplateInfo[$opt] = preg_split('/\s?,\s?/', $hostTemplateInfo[$opt]);
            }
            elseif ((isset($hostTemplateInfo[$opt])) && (!empty($hostTemplateInfo[$opt]))) {
                $hostTemplateInfo[$opt] = array($hostTemplateInfo[$opt]);
            }
            else {
                unset($hostTemplateInfo[$opt]);
            }
        }
        return $hostTemplateInfo;
    }

    public function createDeploymentHostTemplate(
        $deployment, $revision, $hostTemplate, array $hostTemplateInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('contacts','contact_groups','notification_options');
        foreach ($implodeOpts as $opt) {
            if ((isset($hostTemplateInfo[$opt])) &&
                (!empty($hostTemplateInfo[$opt])) &&
                (is_array($hostTemplateInfo[$opt]))) {
                $hostTemplateInfo[$opt] = implode(',', $hostTemplateInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':hosttemplates', $hostTemplate)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':hosttemplate:'.$hostTemplate, $hostTemplateInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':hosttemplates', $hostTemplate);
            return false;
        }
        return false;
    }

    public function modifyDeploymentHostTemplate(
        $deployment, $revision, $hostTemplate, array $hostTemplateInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('contacts','contact_groups','notification_options');
        foreach ($implodeOpts as $opt) {
            if ((isset($hostTemplateInfo[$opt])) &&
                (!empty($hostTemplateInfo[$opt])) &&
                (is_array($hostTemplateInfo[$opt]))) {
                $hostTemplateInfo[$opt] = implode(',', $hostTemplateInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentHostTemplate($deployment,$revision, $hostTemplate)) === true) {
            $this->redis->del($baseKey.':'.$revision.':hosttemplate:'.$hostTemplate);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':hosttemplate:'.$hostTemplate, $hostTemplateInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentHostTemplate($deployment, $revision, $hostTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldHostTemplateInfo =
            $this->getDeploymentHostTemplate($deployment, $revision, $hostTemplate);
        $this->redis->sRem($baseKey.':'.$revision.':hosttemplates', $hostTemplate);
        $this->redis->del($baseKey.':'.$revision.':hosttemplate:'.$hostTemplate);
        return $oldHostTemplateInfo;
    }

    public function getDeploymentHostGroups($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':hostgroups');
    }

    public function existsDeploymentHostGroup($deployment, $revision, $hostGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':hostgroups', $hostGroup);
    }

    public function getDeploymentHostGroup($deployment, $revision, $hostGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':hostgroup:'.$hostGroup);
    }

    public function createDeploymentHostGroup(
        $deployment, $revision, $hostGroup, array $hostGroupInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':hostgroups', $hostGroup)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':hostgroup:'.$hostGroup, $hostGroupInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':hostgroups', $hostGroup);
            return false;
        }
        return false;
    }

    public function modifyDeploymentHostGroup(
        $deployment, $revision, $hostGroup, array $hostGroupInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentHostGroup($deployment, $revision, $hostGroup)) === true) {
            $this->redis->del($baseKey.':'.$revision.':hostgroup:'.$hostGroup);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':hostgroup:'.$hostGroup, $hostGroupInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentHostGroup($deployment, $revision, $hostGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldHostGroupInfo = $this->getDeploymentHostGroup($deployment, $revision, $hostGroup);
        $this->redis->sRem($baseKey.':'.$revision.':hostgroups', $hostGroup);
        $this->redis->del($baseKey.':'.$revision.':hostgroup:'.$hostGroup);
        return $oldHostGroupInfo;
    }

    public function getDeploymentSvcTemplates($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':svctemplates');
    }

    public function existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':svctemplates', $svcTemplate);
    }

    public function getDeploymentSvcTemplate($deployment, $revision, $svcTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $svcTemplateInfo =
            $this->redis->hGetAll($baseKey.':'.$revision.':svctemplate:'.$svcTemplate);
        $explodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($explodeOpts as $opt) {
            if ((isset($svcTemplateInfo[$opt])) && (preg_match('/,/', $svcTemplateInfo[$opt]))) {
                $svcTemplateInfo[$opt] = preg_split('/\s?,\s?/', $svcTemplateInfo[$opt]);
            }
            elseif ((isset($svcTemplateInfo[$opt])) && (!empty($svcTemplateInfo[$opt]))) {
                $svcTemplateInfo[$opt] = array($svcTemplateInfo[$opt]);
            }
            else {
                unset($svcTemplateInfo[$opt]);
            }
        }
        return $svcTemplateInfo;
    }

    public function createDeploymentSvcTemplate(
        $deployment, $revision, $svcTemplate, array $svcTemplateInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcTemplateInfo[$opt])) &&
                (!empty($svcTemplateInfo[$opt])) &&
                (is_array($svcTemplateInfo[$opt]))) {
                $svcTemplateInfo[$opt] = implode(',', $svcTemplateInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':svctemplates', $svcTemplate)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svctemplate:'.$svcTemplate, $svcTemplateInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':svctemplates', $svcTemplate);
            return false;
        }
        return false;
    }

    public function modifyDeploymentSvcTemplate(
        $deployment, $revision, $svcTemplate, array $svcTemplateInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcTemplateInfo[$opt])) &&
                (!empty($svcTemplateInfo[$opt])) &&
                (is_array($svcTemplateInfo[$opt]))) {
                $svcTemplateInfo[$opt] = implode(',', $svcTemplateInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)) === true) {
            $this->redis->del($baseKey.':'.$revision.':svctemplate:'.$svcTemplate);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svctemplate:'.$svcTemplate, $svcTemplateInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentSvcTemplate($deployment, $revision, $svcTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldServiceTemplateInfo =
            $this->getDeploymentSvcTemplate($deployment, $revision, $svcTemplate);
        $this->redis->sRem($baseKey.':'.$revision.':svctemplates', $svcTemplate);
        $this->redis->del($baseKey.':'.$revision.':svctemplate:'.$svcTemplate);
        return $oldServiceTemplateInfo;
    }

    public function getDeploymentSvcGroups($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':svcgroups');
    }

    public function existsDeploymentSvcGroup($deployment, $revision, $svcGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':svcgroups', $svcGroup);
    }

    public function getDeploymentSvcGroup($deployment, $revision, $svcGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':svcgroup:'.$svcGroup);
    }

    public function createDeploymentSvcGroup($deployment, $revision, $svcGroup, array $svcGrpInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':svcgroups', $svcGroup)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svcgroup:'.$svcGroup, $svcGrpInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':svcgroups', $svcGroup);
            return false;
        }
        return false;
    }

    public function modifyDeploymentSvcGroup($deployment, $revision, $svcGroup, array $svcGrpInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentSvcGroup($deployment,$revision, $svcGroup)) === true) {
            $this->redis->del($baseKey.':'.$revision.':svcgroup:'.$svcGroup);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svcgroup:'.$svcGroup, $svcGrpInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentSvcGroup($deployment, $revision, $svcGroup)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldServiceGroupInfo = $this->getDeploymentSvcGroup($deployment, $revision, $svcGroup);
        $this->redis->sRem($baseKey.':'.$revision.':svcgroups', $svcGroup);
        $this->redis->del($baseKey.':'.$revision.':svcgroup:'.$svcGroup);
        return $oldServiceGroupInfo;
    }

    public function getDeploymentSvcs($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':svcs');
    }

    public function existsDeploymentSvc($deployment, $revision, $svc)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':svcs', $svc);
    }

    public function getDeploymentSvc($deployment, $revision, $svc)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $svcInfo = $this->redis->hGetAll($baseKey.':'.$revision.':svc:'.$svc);
        $explodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($explodeOpts as $opt) {
            if ((isset($svcInfo[$opt])) && (preg_match('/,/', $svcInfo[$opt]))) {
                $svcInfo[$opt] = preg_split('/\s?,\s?/', $svcInfo[$opt]);
            }
            elseif ((isset($svcInfo[$opt])) && (!empty($svcInfo[$opt]))) {
                $svcInfo[$opt] = array($svcInfo[$opt]);
            }
            else {
                unset($svcInfo[$opt]);
            }
        }
        return $svcInfo;
    }

    public function createDeploymentSvc($deployment, $revision, $svc, array $svcInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcInfo[$opt])) &&
                (!empty($svcInfo[$opt])) &&
                (is_array($svcInfo[$opt]))) {
                $svcInfo[$opt] = implode(',', $svcInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':svcs', $svc)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svc:'.$svc, $svcInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':svcs', $svc);
            return false;
        }
        return false;
    }

    public function modifyDeploymentSvc($deployment, $revision, $svc, array $svcInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcInfo[$opt])) &&
                (!empty($svcInfo[$opt])) &&
                (is_array($svcInfo[$opt]))) {
                $svcInfo[$opt] = implode(',', $svcInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentSvc($deployment, $revision, $svc)) === true) {
            $this->redis->del($baseKey.':'.$revision.':svc:'.$svc);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svc:'.$svc, $svcInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentSvc($deployment, $revision, $svc)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldServiceInfo = $this->getDeploymentSvc($deployment, $revision, $svc);
        $this->redis->sRem($baseKey.':'.$revision.':svcs', $svc);
        $this->redis->del($baseKey.':'.$revision.':svc:'.$svc);
        return $oldServiceInfo;
    }

    public function getDeploymentSvcDependencies($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':svcdeps');
    }

    public function existsDeploymentSvcDependency($deployment, $revision, $svcDep)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':svcdeps', $svcDep);
    }

    public function getDeploymentSvcDependency($deployment, $revision, $svcDep)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $svcDepInfo = $this->redis->hGetAll($baseKey.':'.$revision.':svcdep:'.$svcDep);
        $explodeOpts = array('execution_failure_criteria','notification_failure_criteria');
        foreach ($explodeOpts as $opt) {
            if ((isset($svcDepInfo[$opt])) && (preg_match('/,/', $svcDepInfo[$opt]))) {
                $svcDepInfo[$opt] = preg_split('/\s?,\s?/', $svcDepInfo[$opt]);
            }
            elseif ((isset($svcDepInfo[$opt])) && (!empty($svcDepInfo[$opt]))) {
                $svcDepInfo[$opt] = array($svcDepInfo[$opt]);
            }
            else {
                unset($svcDepInfo[$opt]);
            }
        }
        return $svcDepInfo;
    }

    public function createDeploymentSvcDependency(
        $deployment, $revision, $svcDep, array $svcDepInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('execution_failure_criteria','notification_failure_criteria');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcDepInfo[$opt])) &&
                (!empty($svcDepInfo[$opt])) &&
                (is_array($svcDepInfo[$opt]))) {
                $svcDepInfo[$opt] = implode(',', $svcDepInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':svcdeps', $svcDep)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svcdep:'.$svcDep, $svcDepInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':svcdeps', $svcDep);
            return false;
        }
        return false;
    }

    public function modifyDeploymentSvcDependency(
        $deployment, $revision, $svcDep, array $svcDepInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('execution_failure_criteria','notification_failure_criteria');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcDepInfo[$opt])) &&
                (!empty($svcDepInfo[$opt])) &&
                (is_array($svcDepInfo[$opt]))) {
                $svcDepInfo[$opt] = implode(',', $svcDepInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentSvcDependency($deployment, $revision, $svcDep)) === true) {
            $this->redis->del($baseKey.':'.$revision.':svcdep:'.$svcDep);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svcdep:'.$svcDep, $svcDepInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentSvcDependency($deployment, $revision, $svcDep)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldServiceDepInfo = $this->getDeploymentSvcDependency($deployment, $revision, $svcDep);
        $this->redis->sRem($baseKey.':'.$revision.':svcdeps', $svcDep);
        $this->redis->del($baseKey.':'.$revision.':svcdep:'.$svcDep);
        return $oldServiceDepInfo;
    }

    public function getDeploymentSvcEscalations($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':svcescs');
    }

    public function existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':svcescs', $svcEsc);
    }

    public function getDeploymentSvcEscalation($deployment, $revision, $svcEsc)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $svcEscInfo = $this->redis->hGetAll($baseKey.':'.$revision.':svcesc:'.$svcEsc);
        $explodeOpts = array('escalation_options','contacts','contact_groups');
        foreach ($explodeOpts as $opt) {
            if ((isset($svcEscInfo[$opt])) && (preg_match('/,/', $svcEscInfo[$opt]))) {
                $svcEscInfo[$opt] = preg_split('/\s?,\s?/', $svcEscInfo[$opt]);
            }
            elseif ((isset($svcEscInfo[$opt])) && (!empty($svcEscInfo[$opt]))) {
                $svcEscInfo[$opt] = array($svcEscInfo[$opt]);
            }
            else {
                unset($svcEscInfo[$opt]);
            }
        }
        return $svcEscInfo;
    }

    public function createDeploymentSvcEscalation(
        $deployment, $revision, $svcEsc, array $svcEscInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('escalation_options','contacts','contact_groups');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcEscInfo[$opt])) &&
                (!empty($svcEscInfo[$opt])) &&
                (is_array($svcEscInfo[$opt]))) {
                $svcEscInfo[$opt] = implode(',', $svcEscInfo[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':svcescs', $svcEsc)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svcesc:'.$svcEsc, $svcEscInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':svcescs', $svcEsc);
            return false;
        }
        return false;
    }

    public function modifyDeploymentSvcEscalation(
        $deployment, $revision, $svcEsc, array $svcEscInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('escalation_options','contacts','contact_groups');
        foreach ($implodeOpts as $opt) {
            if ((isset($svcEscInfo[$opt])) &&
                (!empty($svcEscInfo[$opt])) &&
                (is_array($svcEscInfo[$opt]))) {
                $svcEscInfo[$opt] = implode(',', $svcEscInfo[$opt]);
            }
        }
        if (($return = $this->existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)) === true) {
            $this->redis->del($baseKey.':'.$revision.':svcesc:'.$svcEsc);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':svcesc:'.$svcEsc, $svcEscInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentSvcEscalation($deployment, $revision, $svcEsc)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldServiceEscInfo = $this->getDeploymentSvcEscalation($deployment, $revision, $svcEsc);
        $this->redis->sRem($baseKey.':'.$revision.':svcescs', $svcEsc);
        $this->redis->del($baseKey.':'.$revision.':svcesc:'.$svcEsc);
        return $oldServiceEscInfo;
    }

    public function getDeploymentNodeTemplates($deployment, $revision) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':nodetemplates');
    }

    public function existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':nodetemplates', $nodeTemplate);
    }

    public function getDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $nodeTemplateInfo =
            $this->redis->hGetAll($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate);
        $explodeOpts = array('services','nservices','contacts','contactgroups','svcescs');
        foreach ($explodeOpts as $opt) {
            if ((isset($nodeTemplateInfo[$opt])) && (preg_match('/,/', $nodeTemplateInfo[$opt]))) {
                $nodeTemplateInfo[$opt] = preg_split('/\s?,\s?/', $nodeTemplateInfo[$opt]);
            }
            elseif ((isset($nodeTemplateInfo[$opt])) && (!empty($nodeTemplateInfo[$opt]))) {
                $nodeTemplateInfo[$opt] = array($nodeTemplateInfo[$opt]);
            }
            else {
                unset($nodeTemplateInfo[$opt]);
            }
        }
        return $nodeTemplateInfo;
    }

    public function createDeploymentNodeTemplate(
        $deployment, $revision, $nodeTemplate, array $nodeTemplateInfo
    ) {
        $this->reconnect();
        $implodeOpts = array('services','nservices','contacts','contactgroups','svcescs');
        foreach ($implodeOpts as $opt) {
            if ((isset($nodeTemplateInfo[$opt])) &&
                (!empty($nodeTemplateInfo[$opt])) &&
                (is_array($nodeTemplateInfo[$opt]))) {
                $nodeTemplateInfo[$opt] = implode(',', $nodeTemplateInfo[$opt]);
            }
        }
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($nodeTemplateInfo['type'] == 'standard') {
            if ($this->addDeploymentStandardTemplate($deployment, $revision, $nodeTemplate) !== false) {
                if ($this->redis->sAdd($baseKey.':'.$revision.':nodetemplates', $nodeTemplate) !== false) {
                    if ($this->redis->hMSet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo) !== false) {
                        return true;
                    }
                    $this->redis->sRem($baseKey.':'.$revision.':nodetemplates', $nodeTemplate);
                    $this->deleteDeploymentStandardTemplate($deployment, $revision, $nodeTemplate);
                    return false;
                }
                $this->deleteDeploymentStandardTemplate($deployment, $revision, $nodeTemplate);
                return false;
            }
            return false;
        }
        elseif ($nodeTemplateInfo['type'] == 'unclassified') {
            if ($this->addDeploymentUnclassifiedTemplate($deployment, $revision, $nodeTemplate) === true) {
                if ($this->redis->sAdd($baseKey.':'.$revision.':nodetemplates', $nodeTemplate) !== false) {
                    if ($this->redis->hMSet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo) !== false) {
                        return true;
                    }
                    $this->redis->sRem($baseKey.':'.$revision.':nodetemplates', $nodeTemplate);
                    $this->deleteDeploymentUnclassifiedTemplate($deployment, $revision);
                    return false;
                }
                $this->deleteDeploymentUnclassifiedTemplate($deployment, $revision);
                return false;
            }
            return false;
        }
        else {
            if (($return = $this->redis->sAdd($baseKey.':'.$revision.':nodetemplates', $nodeTemplate)) !== false) {
                if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo)) !== false) {
                    return true;
                }
                $this->redis->sRem($baseKey.':'.$revision.':nodetemplates', $nodeTemplate);
                return false;
            }
            return false;
        }
        return false;
    }

    public function modifyDeploymentNodeTemplate(
        $deployment, $revision, $nodeTemplate, array $nodeTemplateInfo
    ) {
        $this->reconnect();
        $implodeOpts = array('services','nservices','contacts','contactgroups','svcescs');
        foreach ($implodeOpts as $opt) {
            if ((isset($nodeTemplateInfo[$opt])) &&
                (!empty($nodeTemplateInfo[$opt])) &&
                (is_array($nodeTemplateInfo[$opt]))) {
                $nodeTemplateInfo[$opt] = implode(',', $nodeTemplateInfo[$opt]);
            }
        }
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($nodeTemplateInfo['type'] == 'standard') {
            if ($this->existsDeploymentStandardTemplate($deployment, $revision, $nodeTemplate) === true) {
                $this->redis->del($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate);
                if ($this->redis->hMSet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo) !== false) {
                    return true;
                }
                return false;
            }
            return false;
        }
        elseif ($nodeTemplateInfo['type'] == 'unclassified') {
            if ($this->existsDeploymentUnclassifiedTemplate($deployment, $revision) === true) {
                $this->redis->del($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate);
                if ($this->redis->hMSet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo) !== false) {
                    return true;
                }
                return false;
            }
            return false;
        }
        else {
            if (($return = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === true) {
                $this->redis->del($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate);
                if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, $nodeTemplateInfo)) !== false) {
                    return true;
                }
                return false;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldNodeTemplateInfo =
            $this->getDeploymentNodeTemplate($deployment, $revision, $nodeTemplate);
        $this->redis->sRem($baseKey.':'.$revision.':nodetemplates', $nodeTemplate);
        $this->redis->del($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate);
        return $oldNodeTemplateInfo;
    }

    public function getDeploymentNodeTemplateType($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':nodetemplate:'.$nodeTemplate, 'type');
    }

    public function addDeploymentUnclassifiedTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentUnclassifiedTemplate($deployment, $revision)) === false) {
            $this->redis->set($baseKey.':'.$revision.':unclassifiedtemplate', $nodeTemplate);
            return true;
        }
        return false;
    }

    public function deleteDeploymentUnclassifiedTemplate($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->del($baseKey.':'.$revision.':unclassifiedtemplate');
        return true;
    }

    public function existsDeploymentUnclassifiedTemplate($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':unclassifiedtemplate');
    }

    public function addDeploymentStandardTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sAdd($baseKey.':'.$revision.':stdnodetemplates', $nodeTemplate);
    }

    public function deleteDeploymentStandardTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sRem($baseKey.':'.$revision.':stdnodetemplates', $nodeTemplate);
    }

    public function existsDeploymentStandardTemplate($deployment, $revision, $nodeTemplate)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':stdnodetemplates', $nodeTemplate);
    }

    public function getDeploymentStandardTemplates($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':stdnodetemplates');
    }

    public function existsDeploymentResourceCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':resourcecfg');
    }

    public function getDeploymentResourceCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':resourcecfg');
    }

    public function createDeploymentResourceCfg($deployment, $revision, array $resources)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->hMSet($baseKey.':'.$revision.':resourcecfg', $resources);
        return true;
    }

    public function modifyDeploymentResourceCfg($deployment, $revision, array $resources)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->del($baseKey.':'.$revision.':resourcecfg');
        $this->redis->hMSet($baseKey.':'.$revision.':resourcecfg', $resources);
        return true;
    }

    public function deleteDeploymentResourceCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldResourceCfgInfo = $this->getDeploymentResourceCfg($deployment, $revision);
        $this->redis->del($baseKey.':'.$revision.':resourcecfg');
        return $oldResourceCfgInfo;
    }

    public function existsDeploymentModgearmanCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':modgearmancfg');
    }

    public function getDeploymentModgearmanCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':modgearmancfg');
    }

    public function createDeploymentModgearmanCfg($deployment, $revision, array $cfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->hMset($baseKey.':'.$revision.':modgearmancfg', $cfgInfo);
        return true;
    }

    public function modifyDeploymentModgearmanCfg($deployment, $revision, array $cfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->del($baseKey.':'.$revision.':modgearmancfg');
        $this->redis->hMset($baseKey.':'.$revision.':modgearmancfg', $cfgInfo);
        return true;
    }

    public function deleteDeploymentModgearmanCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldModgearmanCfgInfo = $this->getDeploymentModgearmanCfg($deployment, $revision);
        $this->redis->del($baseKey.':'.$revision.':modgearmancfg');
        return $oldModgearmanCfgInfo;
    }

    public function existsDeploymentCgiCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':cgicfg');
    }

    public function getDeploymentCgiCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $cgiCfgInfo = $this->redis->hGetAll($baseKey.':'.$revision.':cgicfg');
        $explodeOpts = array(
            'authorized_for_system_information', 'authorized_for_configuration_information',
            'authorized_for_system_commands', 'authorized_for_read_only',
            'authorized_for_all_services', 'authorized_for_all_service_commands',
            'authorized_for_all_hosts', 'authorized_for_all_host_commands'
        );
        foreach ($explodeOpts as $opt) {
            if ((isset($cgiCfgInfo[$opt])) && (preg_match('/,/', $cgiCfgInfo[$opt]))) {
                $cgiCfgInfo[$opt] = preg_split('/\s?,\s?/', $cgiCfgInfo[$opt]);
            }
            elseif ((isset($cgiCfgInfo[$opt])) && (!empty($cgiCfgInfo[$opt]))) {
                $cgiCfgInfo[$opt] = array($cgiCfgInfo[$opt]);
            }
            else {
                unset($cgiCfgInfo[$opt]);
            }
        }
        return $cgiCfgInfo;
    }

    public function createDeploymentCgiCfg($deployment, $revision, $cfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array(
            'authorized_for_system_information', 'authorized_for_configuration_information',
            'authorized_for_system_commands', 'authorized_for_read_only',
            'authorized_for_all_services', 'authorized_for_all_service_commands',
            'authorized_for_all_hosts', 'authorized_for_all_host_commands'
        );
        foreach ($implodeOpts as $opt) {
            if ((isset($cfgInfo[$opt])) &&
                (!empty($cfgInfo[$opt])) &&
                (is_array($cfgInfo[$opt]))) {
                $cfgInfo[$opt] = implode(',', $cfgInfo[$opt]);
            }
        }
        $this->redis->hMset($baseKey.':'.$revision.':cgicfg', $cfgInfo);
        return true;
    }

    public function modifyDeploymentCgiCfg($deployment, $revision, $cfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array(
            'authorized_for_system_information', 'authorized_for_configuration_information',
            'authorized_for_system_commands', 'authorized_for_read_only',
            'authorized_for_all_services', 'authorized_for_all_service_commands',
            'authorized_for_all_hosts', 'authorized_for_all_host_commands'
        );
        foreach ($implodeOpts as $opt) {
            if ((isset($cfgInfo[$opt])) &&
                (!empty($cfgInfo[$opt])) &&
                (is_array($cfgInfo[$opt]))) {
                $cfgInfo[$opt] = implode(',', $cfgInfo[$opt]);
            }
        }
        $this->redis->del($baseKey.':'.$revision.':cgicfg');
        $this->redis->hMset($baseKey.':'.$revision.':cgicfg', $cfgInfo);
        return true;
    }

    public function deleteDeploymentCgiCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldCGICfgInfo = $this->getDeploymentCgiCfg($deployment, $revision);
        $this->redis->del($baseKey.':'.$revision.':cgicfg');
        return $oldCGICfgInfo;
    }

    public function existsDeploymentNagiosCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':nagioscfg');
    }

    public function getDeploymentNagiosCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':nagioscfg');
    }

    public function createDeploymentNagiosCfg($deployment, $revision, $nagiosInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->hMset($baseKey.':'.$revision.':nagioscfg', $nagiosInfo);
        return true;
    }

    public function modifyDeploymentNagiosCfg($deployment, $revision, $nagiosInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $this->redis->del($baseKey.':'.$revision.':nagioscfg');
        $this->redis->hMset($baseKey.':'.$revision.':nagioscfg', $nagiosInfo);
        return true;
    }

    public function deleteDeploymentNagiosCfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldNagiosCfgInfo = $this->getDeploymentNagiosCfg($deployment, $revision);
        $this->redis->del($baseKey.':'.$revision.':nagioscfg');
        return $oldNagiosCfgInfo;
    }

    public function getDeploymentNRPECmds($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':nrpecmds');
    }

    public function existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':nrpecmds', $nrpeCmd);
    }

    public function getDeploymentNRPECmd($deployment, $revision, $nrpeCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':nrpecmd:'.$nrpeCmd);
    }

    public function getDeploymentNRPECmdLine($deployment, $revision, $nrpeCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':nrpecmd:'.$nrpeCmd, 'cmd_line');
    }

    public function createDeploymentNRPECmd($deployment, $revision, $nrpeCmd, array $nrpeCmdInput)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':nrpecmds', $nrpeCmd)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nrpecmd:'.$nrpeCmd, $nrpeCmdInput)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':nrpecmds', $nrpeCmd);
            return false;
        }
        return false;
    }

    public function modifyDeploymentNRPECmd($deployment, $revision, $nrpeCmd, array $nrpeCmdInput)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)) === true) {
            $this->redis->del($baseKey.':'.$revision.':nrpecmd:'.$nrpeCmd);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nrpecmd:'.$nrpeCmd, $nrpeCmdInput)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentNRPECmd($deployment, $revision, $nrpeCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldNRPECmdInfo = $this->getDeploymentNRPECmd($deployment, $revision, $nrpeCmd);
        $this->redis->sRem($baseKey.':'.$revision.':nrpecmds', $nrpeCmd);
        $this->redis->del($baseKey.':'.$revision.':nrpecmd:'.$nrpeCmd);
        return $oldNRPECmdInfo;
    }

    public function existsDeploymentNRPECfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':nrpecfg');
    }

    public function getDeploymentNRPECfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $nrpeCfgInfo = $this->redis->hGetAll($baseKey.':'.$revision.':nrpecfg');
        $explodeOpts = array('cmds');
        foreach ($explodeOpts as $opt) {
            if ((isset($nrpeCfgInfo[$opt])) && (preg_match('/,/', $nrpeCfgInfo[$opt]))) {
                $nrpeCfgInfo[$opt] = preg_split('/\s?,\s?/', $nrpeCfgInfo[$opt]);
            }
            elseif ((isset($nrpeCfgInfo[$opt])) && (!empty($nrpeCfgInfo[$opt]))) {
                $nrpeCfgInfo[$opt] = array($nrpeCfgInfo[$opt]);
            }
            else {
                unset($nrpeCfgInfo[$opt]);
            }
        }
        return $nrpeCfgInfo;
    }

    public function createDeploymentNRPECfg($deployment, $revision, array $nrpeCfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ((isset($nrpeCfgInfo['cmds'])) &&
            (!empty($nrpeCfgInfo['cmds'])) &&
            (is_array($nrpeCfgInfo['cmds']))
        ) {
            $nrpeCfgInfo['cmds'] = implode(',', $nrpeCfgInfo['cmds']);
        }
        $this->redis->hMSet($baseKey.':'.$revision.':nrpecfg', $nrpeCfgInfo);
        return true;
    }

    public function modifyDeploymentNRPECfg($deployment, $revision, array $nrpeCfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ((isset($nrpeCfgInfo['cmds'])) &&
            (!empty($nrpeCfgInfo['cmds'])) &&
            (is_array($nrpeCfgInfo['cmds']))
        ) {
            $nrpeCfgInfo['cmds'] = implode(',', $nrpeCfgInfo['cmds']);
        }
        $this->redis->del($baseKey.':'.$revision.':nrpecfg');
        $this->redis->hMSet($baseKey.':'.$revision.':nrpecfg', $nrpeCfgInfo);
        return true;
    }

    public function deleteDeploymentNRPECfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldNRPECfgInfo = $this->getDeploymentNRPECfg($deployment, $revision);
        $this->redis->del($baseKey.':'.$revision.':nrpecfg');
        return $oldNRPECfgInfo;
    }

    public function existsDeploymentSupNRPECfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->exists($baseKey.':'.$revision.':supnrpecfg');
    }

    public function getDeploymentSupNRPECfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $supNRPECfgInfo = $this->redis->hGetAll($baseKey.':'.$revision.':supnrpecfg');
        $explodeOpts = array('cmds');
        foreach ($explodeOpts as $opt) {
            if ((isset($supNRPECfgInfo[$opt])) && (preg_match('/,/', $supNRPECfgInfo[$opt]))) {
                $supNRPECfgInfo[$opt] = preg_split('/\s?,\s?/', $supNRPECfgInfo[$opt]);
            }
            elseif ((isset($supNRPECfgInfo[$opt])) && (!empty($supNRPECfgInfo[$opt]))) {
                $supNRPECfgInfo[$opt] = array($supNRPECfgInfo[$opt]);
            }
            else {
                unset($supNRPECfgInfo[$opt]);
            }
        }
        return $supNRPECfgInfo;
    }

    public function createDeploymentSupNRPECfg($deployment, $revision, array $supNRPECfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ((isset($supNRPECfgInfo['cmds'])) &&
            (!empty($supNRPECfgInfo['cmds'])) &&
            (is_array($supNRPECfgInfo['cmds']))
        ) {
            $supNRPECfgInfo['cmds'] = implode(',', $supNRPECfgInfo['cmds']);
        }
        $this->redis->hMSet($baseKey.':'.$revision.':supnrpecfg', $supNRPECfgInfo);
        return true;
    }

    public function modifyDeploymentSupNRPECfg($deployment, $revision, array $supNRPECfgInfo)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ((isset($supNRPECfgInfo['cmds'])) &&
            (!empty($supNRPECfgInfo['cmds'])) &&
            (is_array($supNRPECfgInfo['cmds']))
        ) {
            $supNRPECfgInfo['cmds'] = implode(',', $supNRPECfgInfo['cmds']);
        }
        $this->redis->del($baseKey.':'.$revision.':supnrpecfg');
        $this->redis->hMSet($baseKey.':'.$revision.':supnrpecfg', $supNRPECfgInfo);
        return true;
    }

    public function deleteDeploymentSupNRPECfg($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldSupNRPECfgInfo = $this->getDeploymentSupNRPECfg($deployment, $revision);
        $this->redis->del($baseKey.':'.$revision.':supnrpecfg');
        return $oldSupNRPECfgInfo;
    }

    public function getDeploymentNRPEPlugins($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':nrpeplugins');
    }
    
    public function existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':nrpeplugins', $nrpePlugin);
    }

    public function getDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':nrpeplugin:'.$nrpePlugin);
    }

    public function getDeploymentNRPEPluginFileContents($deployment, $revision, $nrpePlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':nrpeplugin:'.$nrpePlugin, 'file');
    }

    public function createDeploymentNRPEPlugin(
        $deployment, $revision, $nrpePlugin, array $nrpePluginInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':nrpeplugins', $nrpePlugin)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nrpeplugin:'.$nrpePlugin, $nrpePluginInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':nrpeplugins', $nrpePlugin);
            return false;
        }
        return false;
    }

    public function modifyDeploymentNRPEPlugin(
        $deployment, $revision, $nrpePlugin, array $nrpePluginInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)) === true) {
            $this->redis->del($baseKey.':'.$revision.':nrpeplugin:'.$nrpePlugin);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nrpeplugin:'.$nrpePlugin, $nrpePluginInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldNPREPluginInfo = $this->getDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin);
        $this->redis->sRem($baseKey.':'.$revision.':nrpeplugins', $nrpePlugin);
        $this->redis->del($baseKey.':'.$revision.':nrpeplugin:'.$nrpePlugin);
        return $oldNPREPluginInfo;
    }

    public function getDeploymentSupNRPEPlugins($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':supnrpeplugins');
    }
    
    public function existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':supnrpeplugins', $supNRPEPlugin);
    }

    public function getDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':supnrpeplugin:'.$supNRPEPlugin);
    }

    public function getDeploymentSupNRPEPluginFileContents($deployment, $revision, $supNRPEPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':supnrpeplugin:'.$supNRPEPlugin, 'file');
    }

    public function createDeploymentSupNRPEPlugin(
        $deployment, $revision, $supNRPEPlugin, array $supNRPEPluginInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':supnrpeplugins', $supNRPEPlugin)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':supnrpeplugin:'.$supNRPEPlugin, $supNRPEPluginInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':supnrpeplugins', $supNRPEPlugin);
            return false;
        }
        return false;
    }

    public function modifyDeploymentSupNRPEPlugin(
        $deployment, $revision, $supNRPEPlugin, array $supNRPEPluginInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)) === true) {
            $this->redis->del($baseKey.':'.$revision.':supnrpeplugin:'.$supNRPEPlugin);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':supnrpeplugin:'.$supNRPEPlugin, $supNRPEPluginInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldSupNRPEPluginInfo =
            $this->getDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin);
        $this->redis->sRem($baseKey.':'.$revision.':supnrpeplugins', $supNRPEPlugin);
        $this->redis->del($baseKey.':'.$revision.':supnrpeplugin:'.$supNRPEPlugin);
        return $oldSupNRPEPluginInfo;
    }

    public function getDeploymentNagiosPlugins($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':nagiosplugins');
    }
    
    public function existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':nagiosplugins', $nagiosPlugin);
    }

    public function getDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGetAll($baseKey.':'.$revision.':nagiosplugin:'.$nagiosPlugin);
    }

    public function getDeploymentNagiosPluginFileContents($deployment, $revision, $nagiosPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':nagiosplugin:'.$nagiosPlugin, 'file');
    }

    public function createDeploymentNagiosPlugin(
        $deployment, $revision, $nagiosPlugin, array $nagiosPluginInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':nagiosplugins', $nagiosPlugin)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nagiosplugin:'.$nagiosPlugin, $nagiosPluginInfo)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':nagiosplugins', $nagiosPlugin);
            return false;
        }
        return false;
    }

    public function modifyDeploymentNagiosPlugin(
        $deployment, $revision, $nagiosPlugin, array $nagiosPluginInfo
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if (($return = $this->existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)) === true) {
            $this->redis->del($baseKey.':'.$revision.':nagiosplugin:'.$nagiosPlugin);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':nagiosplugin:'.$nagiosPlugin, $nagiosPluginInfo)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldNagiosPluginInfo =
            $this->getDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin);
        $this->redis->sRem($baseKey.':'.$revision.':nagiosplugins', $nagiosPlugin);
        $this->redis->del($baseKey.':'.$revision.':nagiosplugin:'.$nagiosPlugin);
        return $oldNagiosPluginInfo;
    }

    public function getDeploymentClusterCmds($deployment, $revision)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sMembers($baseKey.':'.$revision.':clustercmds');
    }

    public function existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->sIsMember($baseKey.':'.$revision.':clustercmds', $clusterCmd);
    }

    public function getDeploymentClusterCmd($deployment, $revision, $clusterCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $clusterCmdInfo = $this->redis->hGetAll($baseKey.':'.$revision.':clustercmd:'.$clusterCmd);
        $explodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($explodeOpts as $opt) {
            if ((isset($clusterCmdInfo[$opt])) && (preg_match('/,/', $clusterCmdInfo[$opt]))) {
                $clusterCmdInfo[$opt] = preg_split('/\s?,\s?/', $clusterCmdInfo[$opt]);
            }
            elseif ((isset($clusterCmdInfo[$opt])) && (!empty($clusterCmdInfo[$opt]))) {
                $clusterCmdInfo[$opt] = array($clusterCmdInfo[$opt]);
            }
            else {
                unset($clusterCmdInfo[$opt]);
            }
        }
        return $clusterCmdInfo;
    }

    public function getDeploymentClusterCmdLine($deployment, $revision, $clusterCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        return $this->redis->hGet($baseKey.':'.$revision.':clustercmd:'.$clusterCmd, 'cmd_line');
    }

    public function createDeploymentClusterCmd(
        $deployment, $revision, $clusterCmd, array $clusterCmdInput
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($implodeOpts as $opt) {
            if ((isset($clusterCmdInput[$opt])) &&
                (!empty($clusterCmdInput[$opt])) &&
                (is_array($clusterCmdInput[$opt]))) {
                $clusterCmdInput[$opt] = implode(',', $clusterCmdInput[$opt]);
            }
        }
        if (($return = $this->redis->sAdd($baseKey.':'.$revision.':clustercmds', $clusterCmd)) !== false) {
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':clustercmd:'.$clusterCmd, $clusterCmdInput)) !== false) {
                return true;
            }
            $this->redis->sRem($baseKey.':'.$revision.':clustercmds', $clusterCmd);
            return false;
        }
        return false;
    }

    public function modifyDeploymentClusterCmd(
        $deployment, $revision, $clusterCmd, array $clusterCmdInput
    ) {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $implodeOpts = array('notification_options','contacts','contact_groups','servicegroups');
        foreach ($implodeOpts as $opt) {
            if ((isset($clusterCmdInput[$opt])) &&
                (!empty($clusterCmdInput[$opt])) &&
                (is_array($clusterCmdInput[$opt]))) {
                $clusterCmdInput[$opt] = implode(',', $clusterCmdInput[$opt]);
            }
        }
        if (($return = $this->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)) === true) {
            $this->redis->del($baseKey.':'.$revision.':clustercmd:'.$clusterCmd);
            if (($return = $this->redis->hMSet($baseKey.':'.$revision.':clustercmd:'.$clusterCmd, $clusterCmdInput)) !== false) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function deleteDeploymentClusterCmd($deployment, $revision, $clusterCmd)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        $oldClusterCommandInfo =
            $this->getDeploymentClusterCmd($deployment, $revision, $clusterCmd);
        $this->redis->sRem($baseKey.':'.$revision.':clustercmds', $clusterCmd);
        $this->redis->del($baseKey.':'.$revision.':clustercmd:'.$clusterCmd);
        return $oldClusterCommandInfo;
    }

    public function existsConsumerDeploymentLock($deployment, $revision, $lockType)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($lockType == 'diff') {
            $key = $baseKey.':difflock';
        }
        else {
            $key = $baseKey.':'.$revision.':'.$lockType.'lock';
        }
        return $this->redis->exists($key);
    }

    public function createConsumerDeploymentLock($deployment, $revision, $lockType, $ttl)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($lockType == 'diff') {
            $key = $baseKey.':difflock';
        }
        else {
            $key = $baseKey.':'.$revision.':'.$lockType.'lock';
        }
        $this->redis->set($key, 1);
        $this->redis->expire($key, $ttl);
        return true;
    }

    public function deleteConsumerDeploymentLock($deployment, $revision, $lockType)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($lockType == 'diff') {
            $key = $baseKey.':difflock';
        }
        else {
            $key = $baseKey.':'.$revision.':'.$lockType.'lock';
        }
        return $this->redis->del($key);
    }

    public function setConsumerDeploymentInfo($deployment, $revision, $infoType, $info)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($infoType == 'diff') {
            $key = $baseKey.':diffoutput';
            return $this->redis->hMSet($key, $info);
        }
        elseif ($infoType == 'hostaudit') {
            $key = $baseKey.':hostaudit';
            $info = json_encode($info);
            return $this->redis->set($key, $info);
        }
        else {
            $key = $baseKey.':'.$revision.':'.$infoType.'output';
            return $this->redis->hMSet($key, $info);
        }
    }

    public function getConsumerDeploymentInfo($deployment, $revision, $infoType)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('deployment', $deployment);
        if ($infoType == 'diff') {
            $key = $baseKey.':diffoutput';
            return $this->redis->hGetAll($key);
        }
        elseif ($infoType == 'hostaudit') {
            $key = $baseKey.':hostaudit';
            $results = $this->redis->get($key);
            $results = json_decode($results);
            return $results;
        }
        else {
            $key = $baseKey.':'.$revision.':'.$infoType.'output';
            return $this->redis->hGetAll($key);
        }
    }

    public function getCDCRouterZones()
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('cdcrouterzones');
        return $this->redis->sMembers($baseKey);
    }

    public function existsCDCRouterZone($zone)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('cdcrouterzones');
        return $this->redis->sIsMember($baseKey, $zone);
    }

    public function getCDCRouterZone($zone)
    {
        $this->reconnect();
        $baseKey = $this->createBaseKey('cdcrouterzones', $zone);
        return $this->redis->get($baseKey);
    }

    public function writeCDCRouterZones(array $zoneData)
    {
        $this->reconnect();
        $membersKey = $this->createBaseKey('cdcrouterzones');
        $oldMembers = $this->getCDCRouterZones();
        foreach ($oldMembers as $oldMember) {
            $this->redis->sRem($membersKey, $oldMember);
            $memberKey = $this->createBaseKey('cdcrouterzones', $oldMember);
            $this->redis->del($memberKey);
        }
        foreach ($zoneData as $zone => $zoneInfo) {
            $this->redis->sAdd($membersKey, $zone);
            $zoneKey = $this->createBaseKey('cdcrouterzones', $zone);
            $this->redis->set($zoneKey, json_encode($zoneInfo));
        }
        return true;
    }
}
