<?php

class EC2QueryObj
{
    private $m_executorName;
    private $m_params = array();

    /**
     * setParam
     *
     * @param mixed $key
     * @param mixed $value
     * @access public
     * @return void
     */

    public function setParam($key, $value)
    {
        $this->m_params[$key] = $value;
    }

    /**
     * setParams
     *
     * @param array $params
     * @access public
     * @return void
     */

    public function setParams(array $params)
    {
        foreach ($params as $key => $value) {
            $this->m_params[$key] = $value;
        }
    }

    /**
     * getParams
     *
     * @access public
     * @return void
     */

    public function getParams()
    {
        return $this->m_params;
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
        if ((isset($this->m_executorName)) && (!empty($this->m_executorName))) {
            return $this->m_executorName;
        }
        else {
            return EC2ArgParser::getExecutor();
        }
    }

}

