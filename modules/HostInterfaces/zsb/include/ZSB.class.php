<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//
class ZSBException extends Exception {}

class ZSB
{
    /**
     * Exception error message constants
     */
    const BAD_QUERY_MSG     = " Query passed in from query object failed or was malformed";
    const BAD_QUERY_URL     = " Url passed in from query object failed or was malformed";
    const BAD_OBSERVER_MSG  = " Observer does not exist";
    const BAD_EXECUTOR_MSG  = " Executor failure.  Executor does not exist";
    const BAD_CURL_MSG      = " Curl returned error.  code: ";
    const PASSWORD_FAILURE  = " Unable to detect password to use for ZSB Authentication";

    /**
     * curl options for use by the curl to the zsb
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
        $user = ZSBArgParser::getUser();
        $pass = ZSBArgParser::getPassword();
        if ($pass == null) {
            throw new ZSBException(self::PASSWORD_FAILURE);
        }
        $userpwd = $user.':'.$pass;
        $this->m_curl_opt = array(
            "http_header"=>array('Content-Type: application/json'),
            "user_pwd"=>$userpwd,
            "user_agent"=>'Saigon ZSB Fetcher/1.0',
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
     * @param ZSBQueryObj $type 
     * @param mixed $observer 
     * @access public
     * @return void
     */
    public function addObserver(ZSBQueryObj $obj) {
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
     * @throws ZSBException
     * @return void
     */
    private function getObserver($key) {
        if(isset($this->m_observers[$key])) {
            return $this->m_observers[$key];
        }
        throw new ZSBException($key . self::BAD_OBSERVER_MSG);
    }

    /**
     * fetch 
     * 
     * get the required data from the zsb by getting the query from
     * the query object
     *
     * @access public
     * @return void
     */
    public function fetch(ZSBQueryObj $queryObj) {

        if(!$queryUrl = $queryObj->getQueryUrl()) {
            throw new ZSBException(self::BAD_QUERY_URL);
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

        /* Response or No Response ? */
        $response   = curl_exec($ch);
        $errno      = curl_errno($ch);
        $errstr     = curl_error($ch);
        curl_close($ch);

        if ($errno) {
            throw new ZSBException(self::BAD_CURL_MSG.$errno." ".$errstr);
        }

        $result = NULL;
        if($executor = $queryObj->getExecutorName()) {
            $result = $this->getObserver($executor)->execute($response);
        } else {
            throw new ZSBException(self::BAD_EXECUTOR_MSG);
        }
        return $result;
    }
}

