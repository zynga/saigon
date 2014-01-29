<?php

class EC2FilterGroupName
{

    /**
     * __construct
     *
     * @access public
     */
    public function __construct($queryObj)
    {
        $filtervalue = EC2ArgParser::getKeyValue('filtergroupname');
        $filter = array(
            array(
                'Name' => 'group-name',
                'Values' => array($filtervalue),
            )
        );
        $queryObj->setParam('Filters', $filter);
    }

}
