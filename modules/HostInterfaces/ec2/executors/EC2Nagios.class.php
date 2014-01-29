<?php

class EC2Nagios extends EC2Observer
{

    public function execute($response)
    {
        $results = array();
        foreach ($response as $hostData) {
            $tmpdata = array();
            if ((!isset($hostData['PrivateIpAddress'])) || (empty($hostData['PrivateIpAddress']))) continue;
            $tmpdata['address'] = $hostData['PrivateIpAddress'];
            if ((!isset($hostData['PublicDnsName'])) || (empty($hostData['PublicDnsName']))) continue;
            $tmpdata['alias'] = $hostData['PublicDnsName'];
            if ((!isset($hostData['Tags'])) || (empty($hostData['Tags']))) continue;
            foreach ($hostData['Tags'] as $tagData) {
                if ($tagData['Key'] == 'Name') {
                    $tmpdata['host_name'] = $tagData['Value'];
                }
            }
            if ((!isset($tmpdata['host_name'])) || (empty($tmpdata['host_name']))) continue;
            $results[$tmpdata['host_name']] = $tmpdata;
        }
        return $results;
    }
}

