<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBGlobs implements HostAPI
{

    public function getList()
    {
        return null;
    }

    public function getInput()
    {
        if (strtolower(MODE) == 'secure') {
            return null;
        }
        return "ZSB Glob";
    }

    public function getSearchResults($input)
    {
        $results = array();
        $inputArray = array('exec' => 'zsbnagios', 'glob' => $input->srchparam);
        ZSBArgParser::setZSBArgs($inputArray);
        ZSBArgParser::setUser(ZSB_USER);
        ZSBArgParser::setPassword(ZSB_PASS);
        $results = ZSBQueryWrapper::execute(ZSBArgParser::getQueryLocation());
        return $results;
    }

}

