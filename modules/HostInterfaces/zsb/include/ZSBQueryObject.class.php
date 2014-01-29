<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBQueryObj
{
    private $m_executorName;
    private $m_queryUrl;

    /**
     * setQueryUrl 
     * 
     * @param mixed $string 
     * @access public
     * @return void
     */
    public function setQueryUrl($string)
    {
        $this->m_queryUrl = $string;
    }

    /**
     * getQueryUrl 
     * 
     * @access public
     * @return void
     */
    public function getQueryUrl()
    {
        return $this->m_queryUrl;
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

