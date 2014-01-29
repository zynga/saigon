<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBDeployments implements HostAPI
{

    public function getList()
    {
        if (strtolower(MODE) == 'secure') {
            return null;
        }
        $array = array('exec' => 'ZSBReturn');
        ZSBArgParser::setZSBArgs($array);
        ZSBArgParser::setUser(ZSB_USER);
        ZSBArgParser::setPassword(ZSB_PASS);
        $output = ZSBQueryWrapper::execute('zsbdeploymentlist');
        sort($output);
        /** Need the values to be keys, for the view to output correctly **/
        $results = array_flip($output);
        return $results;
    }

    public function getInput()
    {
        return null;
    }

    public function getSearchResults($input)
    {
        $results = array();
        $inputArray = array('exec' => 'zsbnagios', 'deployment' => $input->srchparam);
        ZSBArgParser::setZSBArgs($inputArray);
        ZSBArgParser::setUser(ZSB_USER);
        ZSBArgParser::setPassword(ZSB_PASS);
        $results = ZSBQueryWrapper::execute(ZSBArgParser::getQueryLocation());
        return $results;
    }

}

