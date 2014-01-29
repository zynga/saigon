<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSQueryObj {
    private $m_executorName;
    private $m_queryUrl;
    private $m_loop = array();
    private $m_aloop = array();
    private $m_dloop = array();

    /**
     * setQueryUrl 
     * 
     * @param mixed $string 
     * @access public
     * @return void
     */
    public function setQueryUrl($string) {
        $this->m_queryUrl = $string;
    }

    /**
     * getQueryUrl 
     * 
     * @access public
     * @return void
     */
    public function getQueryUrl() {
        return $this->m_queryUrl;
    }

    /**
     * setExecutorName 
     * 
     * @param mixed $n 
     * @access public
     * @return void
     */
    public function setExecutorName($n) {
        if (!preg_match('/RS/i', $n)) {
            $n = 'rs' . $n;
        }
        $this->m_executorName = $n;
    }

    /**
     * getExecutorName 
     * 
     * @access public
     * @return void
     */
    public function getExecutorName() {
        return $this->m_executorName;
    }

    /**
     * setLoop 
     * 
     * @param mixed $loopItems 
     * @access public
     * @return void
     */
    public function setLoop($loopItems) {
        if (!is_array($loopItems)) {
            array_push($this->m_loop, $loopItems);
        } else {
            $this->m_loop = $loopItems;
        }
    }

    /**
     * getLoop 
     * 
     * @access public
     * @return void
     */
    public function getLoop() {
        return $this->m_loop;
    }

    /**
     * setArrayLoop 
     * 
     * @param mixed $loopItems 
     * @access public
     * @return void
     */
    public function setArrayLoop($loopItems) {
        if (!is_array($loopItems)) {
            array_push($this->m_aloop, $loopItems);
        } else {
            $this->m_aloop = $loopItems;
        }
    }

    /**
     * getArrayLoop 
     * 
     * @access public
     * @return void
     */
    public function getArrayLoop() {
        return $this->m_aloop;
    }

    /**
     * setDeploymentLoop 
     * 
     * @param mixed $loopItems 
     * @access public
     * @return void
     */
    public function setDeploymentLoop($loopItems) {
        if (!is_array($loopItems)) {
            array_push($this->m_dloop, $loopItems);
        } else {
            $this->m_dloop = $loopItems;
        }
    }

    /**
     * getDeploymentLoop 
     * 
     * @access public
     * @return void
     */
    public function getDeploymentLoop() {
        return $this->m_dloop;
    }

}

