<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 *  This class is strictly for hosts that have dual nics, if the facts.ipaddress_eth0 and facts.ipaddress_eth1
 *      are not detected, the host will not be "detected" from this host api interface. 
 */

class CMDBDNGlobs implements HostAPI
{

    public function getList()
    {
        return null;
    }

    public function getInput()
    {
        return 'CMDB DualNic Glob';
    }

    public function getSearchResults($input)
    {
        $results = array();
        $param = $input->srchparam;
        $inputArray = array(
            'exec' => 'datareturner',
            'glob' => $param,
            'rf' => array('config.fqdn', 'facts.ipaddress_eth0', 'facts.ipaddress_eth1', 'facts.ilo_ipaddress', 'cloud.rightscale.links.self')
        );
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
            } elseif ((!isset($fields['facts.ipaddress_eth0'])) || (empty($fields['facts.ipaddress_eth0']))) {
                continue;
            } elseif ((!isset($fields['facts.ipaddress_eth1'])) || (empty($fields['facts.ipaddress_eth1']))) {
                continue;
            }
            // Build out Host based on eth0 address
            $eth0host = $fields['config.fqdn'];
            $results[$eth0host]['host_name'] = $fields['config.fqdn'];
            $results[$eth0host]['address'] = $fields['facts.ipaddress_eth0'];
            if ((isset($fields['facts.ilo_ipaddress'])) && (!empty($fields['facts.ilo_ipaddress']))) {
                $results[$eth0host]['action_url'] = 'https://' . $fields['facts.ilo_ipaddress'];
            } elseif ((isset($fields['cloud.rightscale.links.self'])) && (!empty($fields['cloud.rightscale.links.self']))) {
                $results[$eth0host]['action_url'] = CMDBHelperFuncs::buildCloudHostUrl($fields['cloud.rightscale.links.self']);
            }
            // Build out Host based on eth1 address
            $tmpfields = preg_split("/\./", $fields['config.fqdn']);
            $eth1host = array_shift($tmpfields);
            $eth1host .= "-eth1." . implode(".", $tmpfields);
            $results[$eth1host]['host_name'] = $eth1host;
            $results[$eth1host]['address'] = $fields['facts.ipaddress_eth1'];
            if ((isset($fields['facts.ilo_ipaddress'])) && (!empty($fields['facts.ilo_ipaddress']))) {
                $results[$eth1host]['action_url'] = 'https://' . $fields['facts.ilo_ipaddress'];
            } elseif ((isset($fields['cloud.rightscale.links.self'])) && (!empty($fields['cloud.rightscale.links.self']))) {
                $results[$eth1host]['action_url'] = CMDBHelperFuncs::buildCloudHostUrl($fields['cloud.rightscale.links.self']);
            }
        }
        return $results;
    }

}

