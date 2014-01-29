<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class DataGrouper extends Observer
{
    const EXECUTOR_NAME = "DataGrouperScroll";
    const SCROLL_URL    = "https://cmdb/_search/scroll?scroll=1m";

    private static $m_static_cache;

    public function __construct()
    {
        if(!self::$m_static_cache) {
            self::$m_static_cache = new DataGrouperCache();
        }
    }

    public function execute($response) {
        $this->getCache()->resetCache();
        $result     = null;
        $jsonObj    = json_decode($response);
        $scrollId   = $jsonObj->_scroll_id;
        $total      = $jsonObj->hits->total;

        foreach ($jsonObj->hits->hits as $index => $tmpObj) {
                $this->getCache()->add($tmpObj);
        }

        if ($this->getCache()->getCount() != $total) {

            $cmdb = new CMDB(null);

            $queryObj = new QueryObj();
            $queryObj->setQueryUrl(self::SCROLL_URL);
            $queryObj->setQueryString('{"scroll_id":"'.$scrollId.'"}');
            $queryObj->setExecutorName(self::EXECUTOR_NAME);

            $cmdb->addObserver($queryObj);

            do {
                $results = $cmdb->fetch($queryObj);
                $tmpjsonObj = json_decode($results);

                foreach ($tmpjsonObj->hits->hits as $index => $tmpObj) {
                    $this->getCache()->add($tmpObj);
                }
                unset($tmpjsonObj);

            } while($this->getCache()->getCount() != $total);
        }

        if (!!$executor = GlobalArgParser::getExecutor()) {
            $executorObj = new $executor();
            $result = $executorObj->execute($this->getCache()->get());
        }
        return $result;
    }

    protected function getCache() {
        if(isset(self::$m_static_cache) && self::$m_static_cache instanceof DataGrouperCache) {
            return self::$m_static_cache;
        }
        throw new Exception(__METHOD__."  Tried to access cache but it is not initialized correctly");
    }
}

class DataGrouperCache
{
    private $m_cache;
    private $m_count;

    public function __construct()
    {
        $this->m_cache = array();
        $this->m_count = 0;
    }

    public function add($data)
    {
        $this->m_cache[] = $data;
        $this->m_count++;
    }

    public function get()
    {
        return $this->m_cache;
    }

    public function getCount()
    {
        return $this->m_count;
    }

    public function resetCache()
    {
        $this->m_cache = array();
        $this->m_count = 0;
    }
}

class DataGrouperScroll extends DataGrouper
{
    public function execute($response) {
        return $response;
    }

}

