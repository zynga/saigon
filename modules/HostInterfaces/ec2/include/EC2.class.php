<?php

use Aws\Common\Aws;

class EC2Exception extends Exception {}

class EC2
{

    /**
     * Exception error message constants
     */
    const BAD_OBSERVER_MSG  = " Observer does not exist";
    const BAD_EXECUTOR_MSG  = " Executor failure.  Executor does not exist";
    const ACCOUNT_FAILURE   = " Unable to detect appropriate account information for EC2 Authentication";

    private $m_observers = array();
    private $m_cache     = array();
    private $m_key       = false;
    private $m_secret    = false;
    private $m_region    = false;

    /**
     * __construct 
     * 
     * Builds any otions that need set on instantiation
     *
     * @param mixed $options 
     * @access public
     * @return void
     */
    public function __construct($options)
    {
        $accountinfo = EC2ArgParser::getAccountInfo();
        if ($accountinfo === false) {
            throw new EC2Exception(self::ACCOUNT_FAILURE);
        }
        $this->m_key = $accountinfo['key'];
        $this->m_secret = $accountinfo['secret'];
        $this->m_region = $accountinfo['region'];
        $this->m_cache = array();
    }

    /**
     * getKey
     *
     * returns the key specified for the account
     *
     * @access private
     */
    private function getKey()
    {
        if ($this->m_key !== false) {
            return $this->m_key;
        }
        throw new EC2Exception("Unable to determine account key to use");
    }

    /**
     * getSecret
     *
     * returns the secret specified for the account
     *
     * @access private
     */
    private function getSecret()
    {
        if ($this->m_secret !== false) {
            return $this->m_secret;
        }
        throw new EC2Exception("Unable to determine account secret to use");
    }

    /**
     * getRegion
     *
     * returns the region specified for the account
     *
     * @access private
     */
    private function getRegion()
    {
        if ($this->m_region !== false) {
            return $this->m_region;
        }
        throw new EC2Exception("Unable to determine account region to use");
    }

    /**
     * getCache
     *
     * returns the cached data that is stored by the fetch call
     *
     * @access private
     */
    private function getCache()
    {
        return $this->m_cache;
    }

    /**
     * addToCache
     *
     * adds data to the cache so it can be processed later, used for
     * calls requiring more than one call to fetch all of the hosts.
     *
     * @param mixed $data
     * @access private
     */
    private function addToCache($data)
    {
        $this->m_cache[] = $data;
    }

    /**
     * buildCache
     *
     * builds a cache of host data, since we may be making multiple requests for host data
     *
     * @param array $instanceInfo
     * @access private
     */
    private function buildCache(array $instanceInfo)
    {
        foreach ($instanceInfo as $index => $indexData) {
            if ((isset($indexData['Instances'])) && (!empty($indexData['Instances']))) {
                foreach ($indexData['Instances'] as $cindex => $cindexData) {
                    $this->addToCache($cindexData);
                }
            }
        }
    }

    /**
     * getObserver 
     * 
     * get the observer for a certain key
     *
     * @param mixed $key 
     * @access private
     * @throws EC2Exception
     * @return void
     */
    private function getObserver($key)
    {
        if(isset($this->m_observers[$key])) {
            return $this->m_observers[$key];
        }
        throw new EC2Exception($key . self::BAD_OBSERVER_MSG);
    }

    /**
     * addObserver 
     * 
     * add an observer class to be called by the fetch method
     *
     * @param EC2QueryObj $queryObj 
     * @access public
     * @return void
     */
    public function addObserver(EC2QueryObj $queryObj)
    {
        $observerName = $queryObj->getExecutorName();
        $observer = new $observerName();
        $this->m_observers[$observerName] = $observer;
    }

    /**
     * fetch 
     * 
     * get the required data from the cmdb by getting the query from
     * the query object
     *
     * @param EC2QueryObj $queryObj 
     * @access public
     * @return void
     */
    public function fetch(EC2QueryObj $queryObj)
    {
        $token = false;
        $aws = Aws::factory(
            array(
                'key'    => $this->getKey(),
                'secret' => $this->getSecret(),
                'region' => $this->getRegion(),
            )
        );
        $ec2Client = $aws->get('Ec2');
        $instanceResults = $ec2Client->describeInstances($queryObj->getParams())->toArray();

        if ((isset($instanceResults['Reservations'])) && (!empty($instanceResults['Reservations']))) {
            $this->buildCache($instanceResults['Reservations']);
        }

        if ((isset($instanceResults['NextToken'])) && (!empty($instanceResults['NextToken']))) {
            $token = $instanceResults['NextToken'];
            $queryObj->setParam('NextToken', $token);
        }

        unset($instanceResults);
        
        do {

            $instanceResults = $ec2Client->describeInstances($queryObj->getParams())->toArray();

            if ((isset($instanceResults['Reservations'])) && (!empty($instanceResults['Reservations']))) {
                $this->buildCache($instanceResults['Reservations']);
            }

            if ((isset($instanceResults['NextToken'])) && (!empty($instanceResults['NextToken']))) {
                $token = $instanceResults['NextToken'];
                $queryObj->setParam('NextToken', $token);
            }
            else {
                $token = false;
            }

            unset($instanceResults);

        } while ($token !== false);

        $result = NULL;
        if(!!$executor = $queryObj->getExecutorName()) {
            $result = $this->getObserver($executor)->execute($this->getCache());
        }
        else {
            throw new EC2Exception(self::BAD_EXECUTOR_MSG);
        }
        return $result;
    }

}

