<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * NagHelpers 
 *
 *      Object is meant for processing / coalescing data from multiple sources
 *      All other static functions should return data immediately
 */

class NagHelpers {

    private static $m_static_cache;
    private static $aliastemplate = 'host-dc';
    private static $enableSharding = false;
    private static $shardKey = 0;
    private static $shardCount = 0;
    private static $shardPosition = 0;
    private static $globalnegate = false;

    public function __construct($resetFlag = false) {
        if ((!self::$m_static_cache) || ($resetFlag === true)) {
            self::$m_static_cache = new NagHelpersCache();
        }
    }

    public function setAliasTemplate($template) {
        self::$aliastemplate = $template;
    }

    public function setGlobalNegate($negate) {
        if ((isset($negate)) && (!empty($negate))) {
            self::$globalnegate = $negate;
        }
    }

    public function enableSharding($shardKey, $shardCount, $shardPosition) {
        self::$enableSharding = true;
        self::$shardKey = $shardKey;
        self::$shardCount = $shardCount;
        self::$shardPosition = $shardPosition;
    }

    private function getShard($host) {
        $hash = substr(hash("sha1", $host . self::$shardKey), 0, 8);
        return intval($hash, 16) % self::$shardCount;
    }

    private function skipHost($host) {
        $hostShard = self::getShard($host);
        $hostShard++;
        if ($hostShard != self::$shardPosition) return true;
        else return false;
    }

    public function importHost($host, $hostData) {
        if (self::$globalnegate !== false) {
            $negate = self::$globalnegate;
            if (preg_match("/$negate/", $host)) return;
        }
        if ((!isset($hostData['alias'])) || (empty($hostData['alias']))) {
            if (preg_match('/\./', $hostData['host_name'])) {
                $tmpArray = preg_split('/\./', $hostData['host_name']);
                if (self::$aliastemplate == 'host') {
                    $hostData['alias'] = $tmpArray[0];
                } else {
                    $hostData['alias'] = $tmpArray[0].'-'.$tmpArray[1];
                }
            }
            else {
                $hostData['alias'] = $hostData['host_name'];
            }
        }
        if ((!isset($hostData['hostgroups'])) || (empty($hostData['hostgroups']))) {
            $hostData['hostgroups'] = array();
        } elseif ((isset($hostData['hostgroups'])) && (!empty($hostData['hostgroups']))) {
            if (!is_array($hostData['hostgroups'])) {
                $hostData['hostgroups'] = array($hostData['hostgroups']);
            }
        }
        if ((!isset($hostData['contacts'])) || (empty($hostData['contacts']))) {
            $hostData['contacts'] = array();
        } elseif ((isset($hostData['contacts'])) && (!empty($hostData['contacts']))) {
            if (!is_array($hostData['contacts'])) {
                $hostData['contacts'] = array($hostData['contacts']);
            }
        }
        if ((!isset($hostData['contact_groups'])) || (empty($hostData['contact_groups']))) {
            $hostData['contact_groups'] = array();
        } elseif ((isset($hostData['contact_groups'])) && (!empty($hostData['contact_groups']))) {
            if (!is_array($hostData['contact_groups'])) {
                $hostData['contact_groups'] = array($hostData['contact_groups']);
            }
        }
        if ((!isset($hostData['use'])) || (empty($hostData['use']))) {
            $hostData['use'] = array();
        } elseif ((isset($hostData['use'])) && (!empty($hostData['use']))) {
            if (!is_array($hostData['use'])) {
                $hostData['use'] = array($hostData['use']);
            }
        }
        $this->getCache()->addHost($host, $hostData);
    }

    public function importStaticHost(array $hostArray) {
        if ((empty($hostArray)) || (!isset($hostArray['host'])) || (!isset($hostArray['ip']))) return;
        if (self::$globalnegate !== false) {
            $negate = self::$globalnegate;
            if (preg_match("/$negate/", $hostArray['host'])) return;
        }
        $fields = array();
        $fields['host_name'] = $hostArray['host'];
        $fields['address'] = $hostArray['ip'];
        if (preg_match('/\./', $hostArray['host'])) {
            $hostFields = preg_split('/\./', $hostArray['host']);
            if (self::$aliastemplate == 'host') {
                $fields['alias'] = $hostFields[0];
            } else {
                $fields['alias'] = $hostFields[0].'-'.$hostFields[1];
            }
        } else {
            $fields['alias'] = $hostArray['host'];
        }
        if ((!isset($hostArray['hostgroups'])) || (empty($hostArray['hostgroups']))) {
            $fields['hostgroups'] = array();
        } elseif ((isset($hostArray['hostgroups'])) && (!empty($hostArray['hostgroups']))) {
            if (!is_array($hostArray['hostgroups'])) {
                $fields['hostgroups'] = array($hostArray['hostgroups']);
            }
        }
        if ((!isset($hostArray['contacts'])) || (empty($hostArray['contacts']))) {
            $fields['contacts'] = array();
        } elseif ((isset($hostArray['contacts'])) && (!empty($hostArray['contacts']))) {
            if (!is_array($hostArray['contacts'])) {
                $fields['contacts'] = array($hostArray['contacts']);
            }
        }
        if ((!isset($hostArray['contact_groups'])) || (empty($hostArray['contact_groups']))) {
            $fields['contact_groups'] = array();
        } elseif ((isset($hostArray['contact_groups'])) && (!empty($hostArray['contact_groups']))) {
            if (!is_array($hostArray['contact_groups'])) {
                $fields['contact_groups'] = array($hostArray['contact_groups']);
            }
        }
        if ((!isset($hostArray['use'])) || (empty($hostArray['use']))) {
            $fields['use'] = array();
        } elseif ((isset($hostArray['use'])) && (!empty($hostArray['use']))) {
            if (!is_array($hostArray['use'])) {
                $fields['use'] = array($hostArray['use']);
            }
        }
        $this->getCache()->addHost($fields['host_name'], $fields);
    }

    public function importServices(stdClass $services) {
        foreach ($services as $svcName => $svcObj) {
            $this->getCache()->addSvc($svcName);
            foreach ($svcObj as $key => $value) {
                if (($key == 'deployment') || ($key == 'check_command')) continue;
                if (preg_match("/^carg\d+/", $key)) continue;
                if ( ($key == 'use') || ($key == 'contacts') ||
                     ($key == 'contact_groups') || ($key == 'host_name') ||
                     ($key == 'hostgroup_name') )
                   {
                    if (!is_array($value)) {
                        $this->getCache()->addSvcInfo($svcName, $key, array($value));
                    }
                    else {
                        $this->getCache()->addSvcInfo($svcName, $key, $value);
                    }
                } else {
                    $this->getCache()->addSvcInfo($svcName, $key, $value);
                }
            }
            $command = $svcObj->check_command;
            for ($i=1;$i<=32; $i++) {
                $key = 'carg'.$i;
                if ((isset($svcObj->$key)) && (!empty($svcObj->$key))) {
                    $command .= "!".$svcObj->$key;
                }
            }
            $this->getCache()->addSvcInfo($svcName, 'check_command', $command);
        }
    }

    public function getNodeTemplatePriorities(stdClass $nodeTemplates) {
        $priorities = array();
        $priorities[1] = array();
        $priorities[2] = array();
        $priorities[3] = array();
        $priorities[4] = array();
        $priorities[5] = array();
        $priorities[6] = array();
        foreach ($nodeTemplates as $ntName => $ntObj) {
            if ((isset($ntObj->priority)) && (!empty($ntObj->priority))) {
                array_push($priorities[$ntObj->priority], $ntName);
            }
            else {
                if ($ntObj->type == 'unclassified') {
                    array_push($priorities[6], $ntName);
                }
                else {
                    array_push($priorities[1], $ntName);
                }
            }
        }
        return $priorities;
    }

    public function importServiceDependencies(stdClass $serviceDependencies) {
        foreach ($serviceDependencies as $svcDepName => $svcDepObj) {
            $svcDepArray = array();
            $svcDepArray['service_description'] = $this->getCache()->getSvcDesc($svcDepObj->service_description);
            $svcDepArray['dependent_service_description'] = $this->getCache()->getSvcDesc($svcDepObj->dependent_service_description);
            $svcDepArray['execution_failure_criteria'] = $svcDepObj->execution_failure_criteria;
            $svcDepArray['notification_failure_criteria'] = $svcDepObj->notification_failure_criteria;
            if ((isset($svcDepObj->inherits_parent)) && (!empty($svcDepObj->inherits_parent))) {
                $svcDepArray['inherits_parent'] = $svcDepObj->inherits_parent;
            }
            $svcDepArray['host_name'] = array();
            $svcDepArray['hostgroup_name'] = array();
            $svcDepArray['_saigon_service_description'] = $svcDepObj->service_description;
            $svcDepArray['_saigon_dependent_service_description'] = $svcDepObj->dependent_service_description;
            $this->getCache()->addSvcDependency($svcDepName, $svcDepArray);
        }
    }

    public function importServiceEscalations(stdClass $serviceEscalations) {
        foreach ($serviceEscalations as $svcEsc => $svcEscObj) {
            $svcEscArray = array();
            $svcEscArray['service_description'] = $this->getCache()->getSvcDesc($svcEscObj->service_description);
            if ($svcEscObj->contacts != null) {
                if (!is_array($svcEscObj->contacts)) {
                    if (preg_match('/,/', $svcEscObj->contacts)) {
                        $svcEscArray['contacts'] = preg_split('/\s?,\s?/', $svcEscObj->contacts);
                    }
                    else {
                        $svcEscArray['contacts'] = array($svcEscObj->contacts);
                    }
                }
                else {
                    $svcEscArray['contacts'] = $svcEscObj->contacts;
                }
            }
            else {
                $svcEscArray['contacts'] = array();
            }
            if ($svcEscObj->contact_groups != null) {
                if (!is_array($svcEscObj->contact_groups)) {
                    if (preg_match('/,/', $svcEscObj->contact_groups)) {
                        $svcEscArray['contact_groups'] = preg_split('/\s?,\s?/', $svcEscObj->contact_groups);
                    }
                    else {
                        $svcEscArray['contact_groups'] = array($svcEscObj->contact_groups);
                    }
                }
                else {
                    $svcEscArray['contact_groups'] = $svcEscObj->contact_groups;
                }
            }
            else {
                $svcEscArray['contact_groups'] = array();
            }
            $svcEscArray['first_notification'] = $svcEscObj->first_notification;
            if ($svcEscObj->last_notification == 'all') {
                $svcEscArray['last_notification'] = '0';
            } else {
                $svcEscArray['last_notification'] = $svcEscObj->last_notification;
            }
            $svcEscArray['notification_interval'] = $svcEscObj->notification_interval;
            $svcEscArray['host_name'] = array();
            $svcEscArray['hostgroup_name'] = array();
            if ($svcEscObj->escalation_period != null) $svcEscArray['escalation_period'] = $svcEscObj->escalation_period;
            if ($svcEscObj->escalation_options != null) $svcEscArray['escalation_options'] = $svcEscObj->escalation_options;
            $svcEscArray['_saigon_service_description'] = $svcEscObj->service_description;
            $this->getCache()->addSvcEscalation($svcEsc, $svcEscArray);
        }
    }

    public function scrubHosts() {
        // Return immediately if sharding is disabled...
        if (self::$enableSharding === false) return;
        $hosts = $this->getCache()->getHosts();
        $results = array();
        $roundtwo = array();
        foreach ($hosts as $host => $hostdata) {
            if ((isset($hostdata['parents'])) && (!empty($hostdata['parents']))) {
                $roundtwo[$host] = $hostdata;
                continue;
            }
            if ((self::$enableSharding === true) && (self::skipHost($host) === true)) continue;
            $results[$host] = $hostdata;
        }
        if (empty($roundtwo)) {
            $this->getCache()->updateHosts($results);
        }
        else {
            // Making sure our children end up with their parents.
            $chkhosts = array_keys($results);
            foreach ($roundtwo as $host => $hostdata) {
                if (in_array($hostdata['parents'], $chkhosts)) {
                    $results[$host] = $hostdata;
                }
            }
            // One last time...
            $chkhosts = array_keys($results);
            foreach ($roundtwo as $host => $hostdata) {
                if ((isset($results[$host])) && (!empty($results[$host]))) continue;
                if (in_array($hostdata['parents'], $chkhosts)) {
                    $results[$host] = $hostdata;
                }
            }
            $this->getCache()->updateHosts($results);
        }
    }

    public function returnCache() {
        return $this->getCache()->get();
    }

    public function returnHosts() {
        return $this->getCache()->getHosts();
    }

    public function returnServices() {
        return $this->getCache()->getSvcs();
    }

    public function returnServiceDependencies() {
        return $this->getCache()->getSvcDependencies();
    }

    public function returnServiceEscalations() {
        return $this->getCache()->getSvcEscalations();
    }

    protected function getCache() {
        if(isset(self::$m_static_cache) && self::$m_static_cache instanceof NagHelpersCache) {
            return self::$m_static_cache;
        }
        throw new Exception(__METHOD__."  Tried to access cache but it is not initialized correctly");
    }

}

class NagHelpersCache {
    private $m_cache;

    public function __construct() {
        $this->m_cache = array();
    }

    public function addHost($host, $data) {
        $this->m_cache['hosts'][$host] = $data;
    }

    public function getHosts() {
        if ((isset($this->m_cache['hosts'])) && (!empty($this->m_cache['hosts']))) {
            return $this->m_cache['hosts'];
        }
        return array();
    }

    public function updateHosts(array $hosts) {
        $this->m_cache['hosts'] = $hosts;
    }

    public function addSvc($svc) {
        $this->m_cache['services'][$svc] = array();
    }

    public function addSvcInfo($svc, $key, $value) {
        $this->m_cache['services'][$svc][$key] = $value;
    }

    public function addSvcDependency($svcDepName, array $svcDepArray) {
        $this->m_cache['svcdeps'][$svcDepName] = $svcDepArray;
    }

    public function getSvcDependencies() {
        if ((isset($this->m_cache['svcdeps'])) && (!empty($this->m_cache['svcdeps']))) {
            return $this->m_cache['svcdeps'];
        }
        return array();
    }

    public function addSvcEscalation($svcEscName, array $svcEscArray) {
        $this->m_cache['svcescs'][$svcEscName] = $svcEscArray;
    }

    public function getSvcEscalations() {
        if ((isset($this->m_cache['svcescs'])) && (!empty($this->m_cache['svcescs']))) {
            return $this->m_cache['svcescs'];
        }
        return array();
    }

    public function getSvcs() {
        if ((isset($this->m_cache['services'])) && (!empty($this->m_cache['services']))) {
            return $this->m_cache['services'];
        }
        return array();
    }

    public function get() {
        return $this->m_cache;
    }

    public function getSvcDesc($svcName) {
        if ((isset($this->m_cache['services'][$svcName]['service_description'])) &&
            (!empty($this->m_cache['services'][$svcName]['service_description']))) {
            return $this->m_cache['services'][$svcName]['service_description'];
        }
        return null;
    }

    public function getSvcHosts($svcName) {
        if ((isset($this->m_cache['services'][$svcName]['host_name'])) &&
            (!empty($this->m_cache['services'][$svcName]['host_name']))) {
            return $this->m_cache['services'][$svcName]['host_name'];
        }
        return null;
    }

    public function getSvcHostGroups($svcName) {
        if ((isset($this->m_cache['services'][$svcName]['hostgroup_name'])) &&
            (!empty($this->m_cache['services'][$svcName]['hostgroup_name']))) {
            return $this->m_cache['services'][$svcName]['hostgroup_name'];
        }
        return null;
    }

    public function addTrackHostToHostgroup($hostgroup, $host) {
        if (!isset($this->m_cache['trackhthg'][$hostgroup])) {
            $this->m_cache['trackhthg'][$hostgroup] = array();
        }
        elseif ( (isset($this->m_cache['trackhthg'][$hostgroup])) &&
            (!is_array($this->m_cache['trackhthg'][$hostgroup])) ) {
            $tmp = array();
            array_push($tmp, $this->m_cache['trackhthg'][$hostgroup]);
            $this->m_cache['trackhthg'][$hostgroup] = $tmp;
        }
        if (!in_array($host, $this->m_cache['trackhthg'][$hostgroup])) {
            array_push($this->m_cache['trackhthg'][$hostgroup], $host);
        }
    }

    public function getTrackHostToHostgroup($hostgroup) {
        if ((isset($this->m_cache['trackhthg'][$hostgroup])) &&
            (!empty($this->m_cache['trackhthg'][$hostgroup]))) {
            return $this->m_cache['trackhthg'][$hostgroup];
        }
        return array();
    }
}

