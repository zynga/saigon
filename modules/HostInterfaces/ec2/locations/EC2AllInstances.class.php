<?php

class EC2AllInstances
{

    /**
     * __construct
     *
     * @access public
     */
    public function __construct($queryObj)
    {
        $queryObj->setParam('MaxResults', 500);
    }

}
