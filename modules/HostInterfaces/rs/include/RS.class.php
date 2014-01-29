<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSException extends Exception {}

/**
 * RS 
 * 
 * class to fetch data from RightScale and call the observer of type
 */
class RS {
    /**
     * Exception error message constants
     */
    const BAD_QUERY_MSG     = " Query passed in from query object failed or was malformed";
    const BAD_QUERY_URL     = " Url passed in from query object failed or was malformed";
    const BAD_OBSERVER_MSG  = " Observer does not exist";
    const BAD_EXECUTOR_MSG  = " Executor failure.  Executor does not exist";
    const BAD_CURL_MSG      = " Curl returned error.  code: ";
    const PASSWORD_FAILURE  = " Unable to detect password to use for RS Authentication";

    /**
     * curl options for use by the curl to RightScale
     */
    private $m_curl_opt = array();
    private $authtoken;

    /**
     * __construct 
     * 
     * Builds any otions that need set on instantiation
     *
     * @param mixed $options 
     * @access public
     * @return void
     */
    public function __construct($options) {
        $this->m_curl_opt = array(
            "http_header"=>array("X-API-VERSION:1.5"),
            "header"=>false,
            "user_agent"=>'Saigon RightScale Fetcher/0.1',
            "return_transfer"=>true,
            "ssl_verify_peer"=>true,
            "ssl_verify_host"=>true,
            "timeout"=>0,
        );
    }

    /**
     * m_observers 
     * 
     * observers list
     *
     * @var array
     * @access private
     */
    private $m_observers = array();

    /**
     * addObserver 
     * 
     * add an observer class to be called by the fetch method
     *
     * @param RSQueryObj $type 
     * @param mixed $observer 
     * @access public
     * @return void
     */
    public function addObserver(RSQueryObj $obj) {
        $observerName = $obj->getExecutorName();
        $observer = new $observerName();
        $this->m_observers[$observerName] = $observer;
    }

    /**
     * getObserver 
     * 
     * get the observer for a certain key
     *
     * @param mixed $key 
     * @access private
     * @throws RSException
     * @return void
     */
    private function getObserver($key) {
        if(isset($this->m_observers[$key])) {
            return $this->m_observers[$key];
        }
        throw new RSException($key . self::BAD_OBSERVER_MSG);
    }

    /**
     * login 
     * 
     *  login to rightscale and store authcode for usage later
     *
     * @param RSLoginObj $loginObj 
     * @access public
     * @return void
     */
    public function login(RSLoginObj $loginObj) {
        if(!$loginUrl = $loginObj->getQueryUrl()) {
            throw new RSException(self::BAD_QUERY_URL);
        }
        $httpheader= array("X-API-VERSION:1.5");
        $postData = $loginObj->getPostData();

        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $loginUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, $this->m_curl_opt["header"]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->m_curl_opt["ssl_verify_peer"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->m_curl_opt["ssl_verify_host"]);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->m_curl_opt["user_agent"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->m_curl_opt["return_transfer"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new RSException(self::BAD_CURL_MSG.$errno." ".$errstr);
        }

        $jsonObj = json_decode($response);
        $this->authtoken = $jsonObj->access_token;
        return;
    }

    /**
     * fetch 
     * 
     * get the required data from RightScale by getting the query from
     * the query object
     *
     * @access public
     * @return void
     */
    public function fetch(RSQueryObj $queryObj) {

        if(!$queryUrl = $queryObj->getQueryUrl()) {
            throw new RSException(self::BAD_QUERY_URL);
        }
        $httpheader= array("X-API-VERSION:1.5", "Authorization: Bearer {$this->authtoken}");

        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $queryUrl);
        curl_setopt($ch, CURLOPT_HEADER, $this->m_curl_opt["header"]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->m_curl_opt["user_agent"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->m_curl_opt["return_transfer"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->m_curl_opt["ssl_verify_peer"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->m_curl_opt["ssl_verify_host"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->m_curl_opt["timeout"]);

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new RSException(self::BAD_CURL_MSG.$errno." ".$errstr);
        }

        $result = NULL;
        if($executor = $queryObj->getExecutorName()) {
            $result = $this->getObserver($executor)->execute($response);
        } else {
            throw new RSException(self::BAD_EXECUTOR_MSG);
        }
        return $result;
    }
}

