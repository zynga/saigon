<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CMDBException extends Exception {}

/**
 * CMDB 
 * 
 * class to fetch data from the cmdb and call the observer of type
 */
class CMDB
{
    /**
     * Exception error message constants
     */
    const BAD_QUERY_MSG     = " Query passed in from query object failed or was malformed";
    const BAD_QUERY_URL     = " Url passed in from query object failed or was malformed";
    const BAD_OBSERVER_MSG  = " Observer does not exist";
    const BAD_EXECUTOR_MSG  = " Executor failure.  Executor does not exist";
    const BAD_CURL_MSG      = " Curl returned error.  code: ";
    const PASSWORD_FAILURE  = " Unable to detect password to use for CMDB Authentication";

    /**
     * curl options for use by the curl to the cmdb
     */
    private $m_curl_opt = array();

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
        $user = GlobalArgParser::getUser();
        $pass = GlobalArgParser::getPassword();
        if ($pass == null) {
            throw new CMDBException(self::PASSWORD_FAILURE);
        }
        $userpwd = $user.':'.$pass;
        $this->m_curl_opt = array(
            "http_header"=>array('Content-Type: application/json'),
            "user_pwd"=>$userpwd,
            "user_agent"=>'Saigon CMDB Fetcher/' . VERSION,
            "return_transfer"=>true,
            "ssl_verify_peer"=>false,
            "ssl_verify_host"=>false,
            "timeout"=>45,
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
     * @param QueryObj $type 
     * @param mixed $observer 
     * @access public
     * @return void
     */
    public function addObserver(QueryObj $obj) {
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
     * @throws CMDBException
     * @return void
     */
    private function getObserver($key) {
        if(isset($this->m_observers[$key])) {
            return $this->m_observers[$key];
        }
        throw new CMDBException($key . self::BAD_OBSERVER_MSG);
    }

    /**
     * fetch 
     * 
     * get the required data from the cmdb by getting the query from
     * the query object
     *
     * @access public
     * @return void
     */
    public function fetch(QueryObj $queryObj) {

        /* check return value from query */
        if(!$query = $queryObj->getQueryString()) {
            throw new CMDBException(self::BAD_QUERY_MSG);
        }

        if(!$queryUrl = $queryObj->getQueryUrl()) {
            throw new CMDBException(self::BAD_QUERY_URL);
        }

        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->m_curl_opt["http_header"]);
        curl_setopt($ch, CURLOPT_URL, $queryUrl);
        curl_setopt($ch, CURLOPT_USERPWD, $this->m_curl_opt["user_pwd"]);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->m_curl_opt["user_agent"]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, $this->m_curl_opt["return_transfer"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->m_curl_opt["ssl_verify_peer"]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->m_curl_opt["ssl_verify_host"]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->m_curl_opt["timeout"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new CMDBException(self::BAD_CURL_MSG.$errno." ".$errstr);
        }

        $result = NULL;
        if($executor = $queryObj->getExecutorName()) {
            $result = $this->getObserver($executor)->execute($response);
        } else {
            throw new CMDBException(self::BAD_EXECUTOR_MSG);
        }
        return $result;
    }
}

