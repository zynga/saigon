<?php

abstract class EC2Observer
{
    /**
     * execute 
     * 
     * @abstract
     * @access public
     * @return void
     */
    abstract public function execute($response);
}

