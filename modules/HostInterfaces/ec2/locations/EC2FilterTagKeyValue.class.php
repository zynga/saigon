<?php

class EC2FilterTagKeyValue
{

    /**
     * __construct
     *
     * @access public
     */
    public function __construct($queryObj)
    {
        $filtervalue = EC2ArgParser::getKeyValue('filtertagkeyvalue');
        if (!preg_match('/:/', $filtervalue)) {
            throw new Exception('Unable to properly parse tag value, expecting : to split tag and value');
        }
        else {
            list($tag, $value) = preg_split('/:/', $filtervalue);
            $filter = array(
                array(
                    'Name' => "tag:{$tag}",
                    'Values' => array($value),
                )
            );
            $queryObj->setParam('Filters', $filter);
        }
    }

}
