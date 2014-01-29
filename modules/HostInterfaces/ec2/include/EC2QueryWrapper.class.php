<?php

class EC2QueryWrapperException extends Exception {}

/**
 * EC2QueryWrapper 
 *      Creates a EC2QueryObj before passing it off to
 *      a Class for further processing before submitting
 *      the request to EC2.
 */
class EC2QueryWrapper {

    const CLASSNAME_REGEX   = "/EC2/i";
    
    const MISSING_CLASS     = " class does not exist";
    const COLLISION    = "Unable to detect appropriate class, system was fed only Prefix: ";

    /**
     * execute 
     *      Main wrapper command for executing ec2 fetches
     *
     * @param mixed $className 
     * @static
     * @access public
     * @return void
     */
    public static function execute($className, $forceRelogin = false) {
        if (!preg_match(self::CLASSNAME_REGEX, $className)) {
            $className = 'EC2'.$className;
        }

        if (!class_exists($className)) {
            throw new EC2QueryWrapperException($className.self::MISSING_CLASS);
        } else if ($className == 'EC2') {
            throw new EC2QueryWrapperException(self::COLLISION.EC2_LOC_PREFIX);
        }

        $queryObj = new EC2QueryObj();
        new $className($queryObj);

        $ec2 = new EC2(NULL);
        $ec2->addObserver($queryObj);
        return $ec2->fetch($queryObj);
    }
}

