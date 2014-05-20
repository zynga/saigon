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
        self::$globalnegate = $negate;
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
            $tmpArray = preg_split('/\./', $hostData['host_name']);
            if (self::$aliastemplate == 'host') {
                $hostData['alias'] = $tmpArray[0];
            } else {
                $hostData['alias'] = $tmpArray[0].'-'.$tmpArray[1];
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
        $this->getCache()->addHost($fields['host_name'], $fields);
    }

    public function importServices(stdClass $services) {
        foreach ($services as $svcName => $svcObj) {
            $this->getCache()->addSvc($svcName);
            foreach ($svcObj as $key => $value) {
                if ($key == 'deployment') continue;
                if ((empty($value)) && ($value == null)) continue;
                $this->getCache()->addSvcInfo($svcName, $key, $value);
            }
        }
    }

    public function importNodeTemplate(stdClass $nodeTemplates, $subdeployment = false) {
        foreach ($nodeTemplates as $ntName => $ntObj) {
            if (($subdeployment !== false) && (!empty($subdeployment))) {
                if ((isset($ntObj->subdeployment)) && ($ntObj->subdeployment != $subdeployment)) {
                    continue;
                }
            }
            if ((!isset($ntObj->type)) || ($ntObj->type == 'dynamic')) {
                $pattern = $ntObj->regex;
                foreach ($this->getCache()->getHosts() as $fqdnHost => $hostArray) {
                    if (preg_match("/$pattern/", $fqdnHost)) {
                        if ((isset($ntObj->hostgroup)) && (!empty($ntObj->hostgroup))) {
                            $this->getCache()->addTrackHostToHostgroup($ntObj->hostgroup, $fqdnHost);
                        }
                        if ((isset($ntObj->nregex)) && (preg_match("/$ntObj->nregex/", $fqdnHost))) {
                            if ((isset($ntObj->services)) && (!empty($ntObj->services))) {
                                foreach ($ntObj->services as $service) {
                                    $this->getCache()->addNegateHostToSvc($service, $fqdnHost);
                                }
                            }
                        } else {
                            if ((isset($ntObj->hosttemplate)) && (!empty($ntObj->hosttemplate))) {
                                $this->getCache()->addHostTemplateToHost($fqdnHost, $ntObj->hosttemplate);
                            }
                            if ((isset($ntObj->hostgroup)) && (!empty($ntObj->hostgroup))) {
                                $this->getCache()->addHostGroupToHost($fqdnHost, $ntObj->hostgroup);
                            } else {
                                /* Add hostname to service checks, because a hostgroup wasn't specified */
                                foreach ($ntObj->services as $service) {
                                    $this->getCache()->addHostToSvc($service, $fqdnHost);
                                }
                            }
                        }
                    }
                }
                if ((isset($ntObj->hostgroup)) && (!empty($ntObj->hostgroup))) {
                    $haveHosts = $this->getCache()->getTrackHostToHostgroup($ntObj->hostgroup);
                    if ((!empty($haveHosts)) && (isset($ntObj->services)) && (!empty($ntObj->services))) {
                        foreach ($ntObj->services as $service) {
                            $this->getCache()->addHostGroupToSvc($service, $ntObj->hostgroup);
                        }
                    }
                }
            } else if ($ntObj->type == 'static') {
                $nodes = explode(',', $ntObj->selhosts);
                foreach ($this->getCache()->getHosts() as $fqdnHost => $hostArray) {
                    if (in_array($hostArray['address'], $nodes)) {
                        if ((isset($ntObj->hosttemplate)) && (!empty($ntObj->hosttemplate))) {
                            $this->getCache()->addHostTemplateToHost($fqdnHost, $ntObj->hosttemplate);
                        }
                        if ((isset($ntObj->hostgroup)) && (!empty($ntObj->hostgroup))) {
                            $this->getCache()->addHostGroupToHost($fqdnHost, $ntObj->hostgroup);
                        } else {
                            foreach ($ntObj->services as $service) {
                                $this->getCache()->addHostToSvc($service, $fqdnHost);
                            }
                        }
                    }
                }
                if ((isset($ntObj->hostgroup)) && (!empty($ntObj->hostgroup))) {
                    foreach ($ntObj->services as $service) {
                        $this->getCache()->addHostGroupToSvc($service, $ntObj->hostgroup);
                    }
                }
            }
        }
    }

    public function importServiceDependencies(stdClass $serviceDependencies) {
        foreach ($serviceDependencies as $svcDepName => $svcDepObj) {
            $svcDepArray = array();
            $svcDepArray['service_description'] = $this->getCache()->getSvcDesc($svcDepObj->service_description);
            $svcDepArray['dependent_service_description'] = $this->getCache()->getSvcDesc($svcDepObj->dependent_service_description);
            /* Something is wrong, we are missing a service needed to build this dependency */
            if (($svcDepArray['service_description'] == null) || ($svcDepArray['dependent_service_description'] == null)) continue;
            $svcDepHosts = $this->getCache()->getSvcHosts($svcDepObj->dependent_service_description);
            if ($svcDepHosts != null) $svcDepArray['host_name'] = $svcDepHosts;
            $svcDepHostGroups = $this->getCache()->getSvcHostGroups($svcDepObj->dependent_service_description);
            if ($svcDepHostGroups != null) $svcDepArray['hostgroup_name'] = $svcDepHostGroups;
            $svcDepArray['execution_failure_criteria'] = $svcDepObj->execution_failure_criteria;
            $svcDepArray['notification_failure_criteria'] = $svcDepObj->notification_failure_criteria;
            $this->getCache()->addSvcDependency($svcDepName, $svcDepArray);
        }
    }

    public function importServiceEscalations(stdClass $serviceEscalations) {
        foreach ($serviceEscalations as $svcEsc => $svcEscObj) {
            $svcEscArray = array();
            $svcEscArray['service_description'] = $this->getCache()->getSvcDesc($svcEscObj->service_description);
            if ($svcEscObj->contacts != null) $svcEscArray['contacts'] = $svcEscObj->contacts;
            if ($svcEscObj->contact_groups != null) $svcEscArray['contact_groups'] = $svcEscObj->contact_groups;
            $svcEscArray['first_notification'] = $svcEscObj->first_notification;
            if ($svcEscObj->last_notification == 'all') {
                $svcEscArray['last_notification'] = '0';
            } else {
                $svcEscArray['last_notification'] = $svcEscObj->last_notification;
            }
            $svcEscArray['notification_interval'] = $svcEscObj->notification_interval;
            if ($svcEscObj->escalation_period != null) $svcEscArray['escalation_period'] = $svcEscObj->escalation_period;
            if ($svcEscObj->escalation_options != null) $svcEscArray['escalation_options'] = $svcEscObj->escalation_options;
            $svcEscHosts = $this->getCache()->getSvcHosts($svcEscObj->service_description);
            if ($svcEscHosts != null) $svcEscArray['host_name'] = $svcEscHosts;
            $svcEscHostGroups = $this->getCache()->getSvcHostGroups($svcEscObj->service_description);
            if ($svcEscHostGroups != null) $svcEscArray['hostgroup_name'] = $svcEscHostGroups;
            $this->getCache()->addSvcEscalation($svcEsc, $svcEscArray);
        }
    }

    public function returnCache() {
        return $this->getCache()->get();
    }

    public function returnHosts() {
        return $this->getCache()->getHosts();
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

    public function returnServices($hostCache) {
        $services = $this->getCache()->getSvcs();
        if (self::$enableSharding === false) return $services;
        $hosts = array_keys($hostCache);
        $hostgroups = $this->buildHostGroups($hosts, $hostCache);
        foreach ($services as $service => $svcData) {
            if ((isset($svcData['host_name'])) && (!empty($svcData['host_name']))) {
                $scrubbed = array();
                foreach ($svcData['host_name'] as $host) {
                    if (preg_match("/!/", $host)) {
                        $host = trim("!");
                        if (in_array($host, $hosts)) {
                            array_push($scrubbed, "!" . $host);
                        }
                    }
                    elseif (in_array($host, $hosts)) {
                        array_push($scrubbed, $host);
                    }
                }
                if (empty($scrubbed)) {
                    unset($services[$service]['host_name']);
                }
                else {
                    $services[$service]['host_name'] = $scrubbed;
                }
            }

            if ((isset($svcData['hostgroup_name'])) && (!empty($svcData['hostgroup_name']))) {
                $scrubbed = array();
                foreach ($svcData['hostgroup_name'] as $svchg) {
                    if (in_array($svchg, $hostgroups)) {
                        array_push($scrubbed, $svchg);
                    }
                }
                if (empty($scrubbed)) {
                    unset($services[$service]['hostgroup_name']);
                }
                else {
                    $services[$service]['hostgroup_name'] = $scrubbed;
                }
            }
        }
        return $services;
    }

    public function returnServiceDependencies($hostCache) {
        $servicedeps = $this->getCache()->getSvcDependencies();
        if (self::$enableSharding === false) return $servicedeps;
        $hosts = array_keys($hostCache);
        $hostgroups = $this->buildHostGroups($hosts, $hostCache);
        foreach ($servicedeps as $servicedep => $svcdepData) {
            if ((isset($svcdepData['host_name'])) && (!empty($svcdepData['host_name']))) {
                $scrubbed = array();
                foreach ($svcdepData['host_name'] as $host) {
                    if (preg_match("/!/", $host)) {
                        $host = trim("!");
                        if (in_array($host, $hosts)) {
                            array_push($scrubbed, "!" . $host);
                        }
                    }
                    elseif (in_array($host, $hosts)) {
                        array_push($scrubbed, $host);
                    }
                }
                if (empty($scrubbed)) {
                    unset($servicedeps[$servicedep]['host_name']);
                }
                else {
                    $servicedeps[$servicedep]['host_name'] = $scrubbed;
                }
            }

            if ((isset($svcdepData['hostgroup_name'])) && (!empty($svcdepData['hostgroup_name']))) {
                $scrubbed = array();
                foreach ($svcdepData['hostgroup_name'] as $svchg) {
                    if (in_array($svchg, $hostgroups)) {
                        array_push($scrubbed, $svchg);
                    }
                }
                if (empty($scrubbed)) {
                    unset($servicedeps[$servicedep]['hostgroup_name']);
                }
                else {
                    $servicedeps[$servicedep]['hostgroup_name'] = $scrubbed;
                }
            }
        }
        return $servicedeps;
    }

    public function returnServiceEscalations($hostCache) {
        $serviceescs = $this->getCache()->getSvcEscalations();
        if (self::$enableSharding === false) return $serviceescs;
        $hosts = array_keys($hostCache);
        $hostgroups = $this->buildHostGroups($hosts, $hostCache);
        foreach ($serviceescs as $serviceesc => $svcescData) {
            if ((isset($svcescData['host_name'])) && (!empty($svcescData['host_name']))) {
                $scrubbed = array();
                foreach ($svcescData['host_name'] as $host) {
                    if (preg_match("/!/", $host)) {
                        $host = trim("!");
                        if (in_array($host, $hosts)) {
                            array_push($scrubbed, "!" . $host);
                        }
                    }
                    elseif (in_array($host, $hosts)) {
                        array_push($scrubbed, $host);
                    }
                }
                if (empty($scrubbed)) {
                    unset($serviceescs[$serviceesc]['host_name']);
                }
                else {
                    $serviceescs[$serviceesc]['host_name'] = $scrubbed;
                }
            }

            if ((isset($svcescData['hostgroup_name'])) && (!empty($svcescData['hostgroup_name']))) {
                $scrubbed = array();
                foreach ($svcescData['hostgroup_name'] as $svchg) {
                    if (in_array($svchg, $hostgroups)) {
                        array_push($scrubbed, $svchg);
                    }
                }
                if (empty($scrubbed)) {
                    unset($serviceescs[$serviceesc]['hostgroup_name']);
                }
                else {
                    $serviceescs[$serviceesc]['hostgroup_name'] = $scrubbed;
                }
            }
        }
        return $serviceescs;
    }

    private function buildHostGroups($hosts, $hostCache) {
        $results = array();
        foreach ($hosts as $host) {
            if ((isset($hostCache[$host]['hostgroups'])) && (!empty($hostCache[$host]['hostgroups']))) {
                foreach ($hostCache[$host]['hostgroups'] as $hostgroup) {
                    array_push($results, $hostgroup);
                }
            }
        }
        return $results;
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

    public function addHostGroupToHost($host, $hostgroup) {
        if (!isset($this->m_cache['hosts'][$host]['hostgroups'])) {
            $this->m_cache['hosts'][$host]['hostgroups'] = array();
        }
        if (!in_array($hostgroup, $this->m_cache['hosts'][$host]['hostgroups'])) {
            array_push($this->m_cache['hosts'][$host]['hostgroups'], $hostgroup);
        }
    }

    public function addHostTemplateToHost($host, $hosttemplate) {
        $this->m_cache['hosts'][$host]['use'] = $hosttemplate;
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

    public function addHostGroupToSvc($svc, $hostgroup) {
        if (!isset($this->m_cache['services'][$svc]['hostgroup_name'])) {
            $this->m_cache['services'][$svc]['hostgroup_name'] = array();
        }
        if (!in_array($hostgroup, $this->m_cache['services'][$svc]['hostgroup_name'])) {
            array_push($this->m_cache['services'][$svc]['hostgroup_name'], $hostgroup);
        }
    }

    public function addHostToSvc($svc, $host) {
        if (!isset($this->m_cache['services'][$svc]['host_name'])) {
            $this->m_cache['services'][$svc]['host_name'] = array();
        }
        if (!in_array($host, $this->m_cache['services'][$svc]['host_name'])) {
            array_push($this->m_cache['services'][$svc]['host_name'], $host);
        }
    }

    public function addNegateHostToSvc($svc, $host) {
        if (!isset($this->m_cache['services'][$svc]['host_name'])) {
            $this->m_cache['services'][$svc]['host_name'] = array();
        }
        if (!in_array("!" . $host, $this->m_cache['services'][$svc]['host_name'])) {
            array_push($this->m_cache['services'][$svc]['host_name'], "!" . $host);
        }
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

