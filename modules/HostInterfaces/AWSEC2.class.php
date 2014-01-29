<?php

class AWSEC2 implements HostAPI
{

    public function getList()
    {
        $results = array();
        $accounts = EC2ArgParser::getAccounts();
        foreach ($accounts as $account) {
            $key = "AWSEC2-{$account}-allinstances";
            $val = "All Instances";
            $results[$key][$val] = true;
        }
        return $results;
    }

    public function getInput()
    {
        $results = array();
        $accounts = EC2ArgParser::getAccounts();
        foreach ($accounts as $account) {
            $ftkey = "AWSEC2-{$account}-filtertagkeyvalue";
            $ftval = "AWSEC2 {$account} Filter Tag Key:Value";
            $results[$ftkey] = $ftval;
            $fgkey = "AWSEC2-{$account}-filtergroupname";
            $fgval = "AWSEC2 {$account} Filter Group Name";
            $results[$fgkey] = $fgval;
        }
        return $results;
    }

    public function getSearchResults($input)
    {
        $results = array();
        if (preg_match("/^AWSEC2-(\w+)-(\w+)/", $input->location, $matches)) {
            if ($matches[2] == 'allinstances') {
                $inputArray = array(
                    'exec' => 'ec2nagios',
                    'account' => $matches[1],
                    'allinstances' => true
                );
            }
            elseif ($matches[2] == 'filtertagkeyvalue') {
                $inputArray = array(
                    'exec' => 'ec2nagios',
                    'account' => $matches[1],
                    'filtertagkeyvalue' => $input->srchparam
                );
            } 
            elseif ($matches[2] == 'filtergroupname') {
                $inputArray = array(
                    'exec' => 'ec2nagios',
                    'account' => $matches[1],
                    'filtergroupname' => $input->srchparam
                );
            }
            else {
                return $results;
            }
            EC2ArgParser::setGlobalArgs($inputArray);
            $results = EC2QueryWrapper::execute(EC2ArgParser::getQueryLocation());
        }
        return $results;
    }

}

