<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class QueryObj
{
    private $m_mode;
    private $m_state;
    private $m_searchParam;
    private $m_executorName;
    private $m_queryUrl;
    private $m_queryString;
    private $m_returnField;

    /**
     * setMode 
     * 
     * @param mixed $m 
     * @access public
     * @return void
     */
    public function setMode($m)
    {
        $this->m_mode = $m;
    }

    /**
     * getMode 
     * 
     * @access public
     * @return void
     */
    public function getMode()
    {
        return $this->m_mode;
    }

    /**
     * setState 
     * 
     * @param mixed $s 
     * @access public
     * @return void
     */
    public function setState($s)
    {
        $this->m_state = $s;
    }

    /**
     * getState 
     * 
     * @access public
     * @return void
     */
    public function getState()
    {
        return $this->m_state;
    }

    /**
     * setSearchParam 
     * 
     * @param mixed $s 
     * @access public
     * @return void
     */
    public function setSearchParam($s)
    {
        $this->m_searchParam = $s;
    }

    /**
     * getSearchParam 
     * 
     * @access public
     * @return void
     */
    public function getSearchParam()
    {
        return $this->m_searchParam;
    }

    /**
     * setReturnField 
     * 
     * @param mixed $s 
     * @access public
     * @return void
     */
    public function setReturnField($s)
    {
        $this->m_returnField = $s;
    }

    /**
     * getReturnField
     * 
     * @access public
     * @return void
     */
    public function getReturnField()
    {
        return $this->m_returnField;
    }

    public function setQueryUrl($string)
    {
        $this->m_queryUrl = $string;
    }

    public function getQueryUrl()
    {
        return $this->m_queryUrl;
    }

    /**
     * setQueryString 
     * 
     * @param mixed $string 
     * @access public
     * @return void
     */
    public function setQueryString($string)
    {
        $this->m_queryString = $string;
    }

    /**
     * getQueryString 
     * 
     * @access public
     * @return void
     */
    public function getQueryString()
    {
        return $this->m_queryString;
    }

    /**
     * setExecutorName 
     * 
     * @param mixed $n 
     * @access public
     * @return void
     */
    public function setExecutorName($n)
    {
        $this->m_executorName = $n;
    }

    /**
     * getExecutorName 
     * 
     * @access public
     * @return void
     */
    public function getExecutorName()
    {
        return $this->m_executorName;
    }
}

