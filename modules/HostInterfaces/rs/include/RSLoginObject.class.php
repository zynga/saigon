<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSLoginObjException extends Exception {}

class RSLoginObj {

    private static $m_postData = array('grant_type'=>'refresh_token');
    private static $m_queryUrl = 'https://my.rightscale.com/api/oauth2';

    /**
     * getQueryUrl 
     * 
     * @access public
     * @return void
     */
    public function getQueryUrl() {
        return self::$m_queryUrl;
    }

    /**
     * getPostData 
     * 
     * @access public
     * @return void
     */
    public function getPostData() {
        $results = self::$m_postData;
        $results['refresh_token'] = RSArgParser::getRegionToken();
        return $results;
    }
}

