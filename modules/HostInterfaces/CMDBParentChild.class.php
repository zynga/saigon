<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBParentChild implements HostAPI
{

    public function getList()
    {
        return array('DC1-VC' => 1, 'DC2-VC' => 1);
    }

    public function getInput()
    {
        return null;
    }

    public function getSearchResults($input)
    {
        $results = array();
        list($physical, $virtual) = preg_split("/-/", strtoupper($input->srchparam));
        $hosts = $this->getHosts('physical', $physical);
        foreach ($hosts as $host => $hostData) {
            $results[$host] = $hostData;
        }
        $hosts = $this->getHosts('physical', $physical, 'active_repair');
        foreach ($hosts as $host => $hostData) {
            $results[$host] = $hostData;
        }
        $hv = array_keys($results);
        $hosts = $this->getHosts('virtual', $virtual);
        foreach ($hosts as $host => $hostData) {
            if ((isset($hostData['parents'])) && (!empty($hostData['parents']))) {
                if (in_array($hostData['parents'], $hv)) {
                    $results[$host] = $hostData;
                }
            }
        }
        return $results;
    }

    private function getHosts($mode, $param, $state = 'active')
    {
        $results = array();
        if ($mode == 'physical') {
            $inputArray = array(
                'exec' => 'datareturner',
                'state' => $state,
                'glob' => '*.' . strtolower($param) . '.host.com',
                'rf' => array('config.fqdn', 'config.ipaddress', 'facts.ilo_ipaddress')
            );
        }
        elseif ($mode == 'virtual') {
            $inputArray = array(
                'exec' => 'datareturner',
                'state' => $state,
                'glob' => '*.' . strtolower($param) . '.host.com',
                'rf' => array('config.fqdn', 'config.ipaddress', 'cfacts.parent', 'cloud.rightscale.links.self')
            );
        }
        else {
            return array();
        }
        GlobalArgParser::setGlobalArgs($inputArray);
        GlobalArgParser::setUser(CMDB_USER);
        GlobalArgParser::setPassword(CMDB_PASS);
        $output = QueryWrapper::execute(GlobalArgParser::getQueryLocation());
        foreach ($output as $idx => $hostObj) {
            if ((!isset($hostObj->fields)) || (empty($hostObj->fields))) {
                continue;
            }
            $fields = get_object_vars($hostObj->fields);
            if (empty($fields)) {
                continue;
            }
            $host = $fields['config.fqdn'];
            $results[$host]['host_name'] = $fields['config.fqdn'];
            $results[$host]['address'] = $fields['config.ipaddress'];
            if ($mode == 'physical') {
               if ((isset($fields['facts.ilo_ipaddress'])) && (!empty($fields['facts.ilo_ipaddress']))) {
                    $results[$host]['action_url'] = 'https://' . $fields['facts.ilo_ipaddress'];
                }
            }
            elseif ($mode == 'virtual') {
                if ((isset($fields['cloud.rightscale.links.self'])) && (!empty($fields['cloud.rightscale.links.self']))) {
                    $results[$host]['action_url'] = CMDBHelperFuncs::buildCloudHostUrl($fields['cloud.rightscale.links.self']);
                }
            }

            if ((isset($fields['cfacts.parent'])) && (!empty($fields['cfacts.parent']))) {
                $results[$host]['parents'] = $fields['cfacts.parent'];
            }

            if ($state == 'active') {
                $results[$host]['icon_image'] = 'linux40.png';
                $results[$host]['icon_image_alt'] = 'Active / Operating';
            }
            elseif ($state == 'active_repair') {
                $results[$host]['icon_image'] = 'ignore_me.png';
                $results[$host]['icon_image_alt'] = 'Active Repair / Ignore Me!!';
            }
        }
        return $results;
    }

}

