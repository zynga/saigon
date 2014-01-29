<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagRedis {

    protected static $init = false;
    protected static $redis;
    protected static $redisHosts;
    protected static $timestamp;
    protected static $timeout = 60;

    public static function init($reinit = false) {
        /* Initial Redis Information */
        if (self::$init === false) {
            /* Consistent Hash Function */
            function getRedisHashFunc($key) {
                return substr($key, 0, 3);
            }
            self::$redisHosts = self::buildRedisHostArray(REDIS_CLUSTER);
            self::$redis = new RedisArray(self::$redisHosts, array("function" => "getRedisHashFunc"));
            self::$init = true;
            self::$timestamp = time();
            return;
        }
        if (($reinit === true) && (self::$init === true)) {
            if ((time() - self::$timestamp) > self::$timeout) {
                self::$redis = new RedisArray(self::$redisHosts, array("function" => "getRedisHashFunc"));
                self::$timestamp = time();
            }
        }
        return;
    }

    /**
     * buildRedisHostArray 
     * 
     * @param mixed $hostConfig 
     * @static
     * @access public
     * @return void
     */
    public static function buildRedisHostArray($hostConfig) {
        if ((!isset($hostConfig)) || (empty($hostConfig))) {
            return false;
        }
        $results = array();
        if (preg_match("/,/", $hostConfig)) {
            $hosts = preg_split("/,\s?/", $hostConfig);
            foreach ($hosts as $host) {
                array_push($results, $host);
            }
        } else {
                array_push($results, $hostConfig);
        }
        return $results;
    }

    /**
     * Bumps the last used Redis time stamp, used by consumers
     *  Internal mechanism, not a Redis based command
     *
     * @return void
     */

    public static function bumpTimeStamp() {
        self::$timestamp = time();
        return;
    }

    /**
     * Sets the client option
     *
     * @return bool
     */

    public static function setOption($option, $value) {
        if (self::$init === false) return false;
        return self::$redis->setOption($option, $value);
    }

    /**
     * Gets a client option
     *
     * @param string $option Client Option we are checking
     * @return string Option Value
     */

    public static function getOption($option) {
        if (self::$init === false) return false;
        return self::$redis->getOption($option);
    }

    /**
     * Gets a key's value, if the key's type is a REDIS_STRING
     *
     * @param string $key Key name to retrieve
     * @return string|int|bool Returns value of key if it exists, otherwise false
     */
    
    public static function get($key) {
        if (self::$init === false) return false;
        return self::$redis->get($key);
    }

    /**
     * Sets a key to a value
     *
     * @param string $key Key name to set
     * @param string|int $value Value to set Key to
     * @return bool
     */

    public static function set($key, $value) {
        if (self::$init === false) return false;
        return self::$redis->set($key, $value);
    }

    /**
     * Sets a key to a value, as well as setting a ttl on the key
     *
     * @param string $key Key name to set
     * @param string|int $value Value to set Key to
     * @param int $ttl Time to Live Value (key expiration)
     * @return bool
     */
    public static function setex($key, $value, $ttl = 86400) {
        if (self::$init === false) return false;
        return self::$redis->setex($key, $ttl, $value);
    }

    /**
     * Sets a Timeout / Time To Live on an existing key
     *
     * @param string $key Key name to set ttl on
     * @param int $ttl Value for TTL
     * @return bool
     */
    public static function setTimeout($key, $ttl = 86400) {
        if (self::$init === false) return false;
        return self::$redis->setTimeout($key, $ttl);
    }

    /**
     * Alias for setTimeout
     */
    public static function setTTL($key, $ttl = 86400) {
        if (self::$init === false) return false;
        return self::setTimeout($key, $ttl);
    }

    /**
     * Deletes a key or array of keys
     *
     * @param mixed[] $key Key to delete
     * @return int Number of keys deleted
     */
    public static function del($key) {
        if (self::$init === false) return false;
        if (is_array($key)) {
            foreach ($key as $subkey) {
                self::$redis->delete($subkey);
            }
            return;
        } else {
            return self::$redis->delete($key);
        }
    }

    /**
     * Checks to see if a Key Exists
     *
     * @param string $key Key to check for existence
     * @return bool
     */
    public static function exists($key) {
        if (self::$init === false) return false;
        return self::$redis->exists($key);
    }

    /**
     * Returns the REDIS type of the key, for key handling purposes
     *
     * @param string $key Key to get the type of
     * @return mixed[] Depends on type of Data
     *      string: Redis::REDIS_STRING
     *      set: Redis::REDIS_SET
     *      list: Redis::REDIS_LIST
     *      zset: Redis::REDIS_ZSET
     *      hash: Redis::REDIS_HASH
     *      other: Redis::REDIS_NOT_FOUND
     */
    public static function type($key) {
        if (self::$init === false) return false;
        return self::$redis->type($key);
    }

    /**
     * Sorts a redis set in alphabetical order
     *
     * @param string $key Key to sort and return
     * @return string[] array of sorted strings
     */
    public static function alphaSort($key) {
        if (self::$init === false) return false;
        return self::$redis->sort($key, array('alpha' => true));
    }

    /**
     * Increments a key by 1, creates the key
     *  and sets it to 1 if it doesn't exist
     *
     * @param string $key Key to increment
     * @return int Value returned by server after incremenet
     */
    public static function incr($key) {
        if (self::$init === false) return false;
        return self::$redis->incr($key);
    }

    /**
     * Increments a key by value specified
     *  creates the key if it doesn't exist
     *
     * @param string $key Key to increment
     * @param int $value Value to increment Key by
     * @return int Value returned by server after increment
     */
    public static function incrBy($key, $value) {
        if (self::$init === false) return false;
        if (!preg_match('/\d+/', $value)) return false;
        return self::$redis->incrBy($key, $value);
    }

    /**
     * Decrements a key by 1, creates the key
     *  and sets it to -1 if it doesn't exist
     *
     * @param string $key Key to decrement
     * @return int Value returned by server after decremenet
     */
    public static function decr($key) {
        if (self::$init === false) return false;
        return self::$redis->decr($key);
    }

    /**
     * Decrements a key by value specified creates the
     *  key as a negative value if it doesn't exist
     *
     * @param string $key Key to decrement
     * @param int $value Value to decrement Key by
     * @return int Value returned by server after decrement
     */
    public static function decrBy($key, $value) {
        if (self::$init === false) return false;
        if (!preg_match('/\d+/', $value)) return false;
        return self::$redis->decrBy($key, $value);
    }

    /**
     * Lists keys based on search key provided, as well
     *  as a greedy operator to help match groups of keys
     *
     * @param string $key Search Key Basename
     * @param bool $greedy Search for Basename* keys
     * @return string[] Keys that matched pattern
     */
    public static function keys($key, $greedy = true) {
        if (self::$init === false) return false;
        if ((isset($key)) && (!empty($key))) {
            if ($greedy === true) {
                if (preg_match('/\*$/', $key)) {
                    return self::$redis->keys($key);
                } else {
                    return self::$redis->keys($key.'*');
                }
            } else {
                return self::$redis->keys($key);
            }
        } else if ($greedy === true) {
            return self::$redis->keys('*');
        }
        return false;
    }

    /**
     * Returns the contents of a set
     *
     * @param string $key Key to fetch
     * @return string[] Contents of Key
     */
    public static function sMembers($key) {
        if (self::$init === false) return false;
        return self::$redis->sMembers($key);
    }

    /**
     * Checks if the value is a member of the key set
     *
     * @param string $key Key set to check against
     * @param mixed $value Value we are using for member verification
     * @return bool
     */
    public static function sIsMember($key, $value) {
        if (self::$init === false) return false;
        return self::$redis->sIsMember($key, $value);
    }

    /**
     * Adds a value to a set at the key specified
     *
     * @param string $key Key set to add value into
     * @param mixed[] $value Value(s) to add into the key set
     * @return bool[] True if added, False if already present
     */
    public static function sAdd($key, $value) {
        if (self::$init === false) return false;
        if (is_array($value)) {
            $ret = array();
            foreach ($value as $subValue) {
                $ret[] = self::$redis->sAdd($key, $subValue);
            }
            return $ret;
        } else {
            return self::$redis->sAdd($key, $value);
        }
    }

    /**
     * Removes a value from a set at the key specified
     *
     * @param string $key Key set to remove value from
     * @param mixed[] $value Value(s) to remove from the key set
     * @return bool[] True if removed, False if non-existent
     */
    public static function sRem($key, $value) {
        if (self::$init === false) return false;
        if (is_array($value)) {
            $ret = array();
            foreach ($value as $subValue) {
                $ret[] = self::$redis->sRem($key, $subValue);
            }
            return $ret;
        } else {
            return self::$redis->sRem($key, $value);
        }
    }

    /**
     * Returns the values of all elements that are present in any of
     * the keys specified
     *
     * @param string[] $keys Keys to perform union on
     * @return string[] Elements present in sets
     */
    public static function sUnion($keys) {
        if (self::$init === false) return false;
        return self::$redis->sUnion(implode(" ", $keys));
    }

    /**
     * Returns the difference of the sets between the
     *  master and the diffs
     *
     * @param string $master Set to compare all others against
     * @param string[] $diffs Sets used in compare against master
     * @param string[] Difference between master and diffs
     */
    public static function sDiff($master, $diffs) {
        if (self::$init === false) return false;
        if (is_array($diffs)) {
            return self::$redis->sDiff($master, implode(" ", $diffs));
        } else {
            return self::$redis->sDiff($master, $diffs);
        }
    }

    /**
     * Adds the value to the key specified to the head (left) of the list
     *  returns false if key exists and isn't list
     *
     * @param string $key Key to add value to
     * @param mixed[] $value Value(s) to add to key
     * @return int[] Length of list after addition, false for failures
     */
    public static function lPush($key, $value) {
        if (self::$init === false) return false;
        if (is_array($value)) {
            $ret = array();
            foreach ($value as $subValue) {
                $ret[] = self::$redis->lPush($key, $subValue);
            }
            return $ret;
        } else {
            return self::$redis->lPush($key, $value);
        }
    }

    /**
     * Adds the value to the key specified to the tail (right) of the list
     *  returns false if key exists and isn't list
     *
     * @param string $key Key to add value to
     * @param mixed[] $value Value(s) to add to key
     * @return int[] Length of list after addition, false for failures
     */
    public static function rPush($key, $value) {
        if (self::$init === false) return false;
        if (is_array($value)) {
            $ret = array();
            foreach ($value as $subValue) {
                $ret[] = self::$redis->rPush($key, $subValue);
            }
            return $ret;
        } else {
            self::$redis->rPush($key, $value);
        }
    }

    /**
     * Returns the specified elements of the list stored at the specified
     *  key in the range specified, start and stop, which are interpreted as indices
     *
     *  [0,1,2,..] numbers for searching from start of set, enumerating forwards
     *  [-1,-2,-3,..] numbers for search from the end of a set, enumerating backwards
     *
     * @param string $key Key to process
     * @param int $start Start index
     * @param int $end End index
     * @return string[] Elements returned from range search
     */
    public static function lRange($key, $start, $stop) {
        if (self::$init === false) return false;
        return self::$redis->lRange($key, $start, $stop);
    }

    /**
     * Add a value to an ordered set at the key specified with the weight specified
     * 
     * @param string $key Key to add value into
     * @param mixed $value Value to store in the ordered set
     * @param int $weight Weight to use for ranking the value in the ordered set
     * @return int 1 on success, 0 on failure
     */
    public static function zAdd($key, $value, $weight = 1) {
        if (self::$init === false) return false;
        return self::$redis->zAdd($key, $weight, $value);
    }

    /**
     * Removes a value from an ordered set
     * 
     * @param string $key Key to remove value from
     * @param mixed $value Value to remove from the ordered set
     * @return int 1 on success, 0 on failure
     */
    public static function zRem($key, $value) {
        if (self::$init === false) return false;
        return self::$redis->zRem($key, $value);
    }

    /**
     * Returns the elements of an ordered set at the specified key in the range,
     *  start and stop. Start and stop are indices of the array.
     *
     * @param string $key Key of the ordered set
     * @param int $start Start indice
     * @param int $end End indice
     * @param bool $withScores Return weights in data
     * @return mixed[] Values of specified range, possibly with weights
     */
    public static function zRange($key, $start, $end, $withScores = false) {
        if (self::$init === false) return false;
        return self::$redis->zRange($key, $start, $end, $withScores);
    }

    /**
     * Returns the elements of an ordered set at the specified key in the range,
     *  start and stop, in reverse order. Start and stop are indices of the array.
     *
     * @param string $key Key of the ordered set
     * @param int $start Start indice
     * @param int $end End indice
     * @param bool $withScores Return weights in data
     * @return mixed[] Values of specified range, possibly with weights
     */
    public static function zRevRange($key, $start, $end, $withScores = false) {
        if (self::$init === false) return false;
        return self::$redis->zRevRange($key, $start, $end, $withScores);
    }

    /**
     * Adds key/value to hash if it doesn't exist
     * replaces current value of key if it does exist.
     *
     * @param string $hashKey Name for Redis Hash
     * @param string $memberKey Key name for member of hash
     * @param string|int $value Internal Hash Value stored at the Key Name in the Redis Hash
     * @return int|bool
     *      1 Means value didn't exist and was added
     *      0 Means value did exist and was replaced
     *      false if there was an error
     `*/

    public static function hSet($hashKey, $memberKey, $value) {
        if (self::$init === false) return false;
        return self::$redis->hSet($hashKey, $memberKey, $value);
    }

    /** 
     * Adds key/value to hash if it doesn't exist
     * returns false if the key already exists.
     *
     * @param string $hashKey Key Name for Redis Hash
     * @param string $memberKey Key name for member of hash
     * @param string|int $value Value stored at the Key Name in the Redis Hash
     * @return bool True if successful, False otherwise
     */

    public static function hSetNx($hashKey, $memberKey, $value) {
        if (self::$init === false) return false;
        return self::$redis->hSetNx($hashKey, $memberKey, $value);
    }

    /**
     * Gets a value from the hash stored at the key specified
     *
     * @param string $hashKey Key Name for hash
     * @param string $memberKey Key name for member of hash
     * @return mixed Contents of key, or false incase of failure
     */
    public static function hGet($hashKey, $memberKey) {
        if (self::$init === false) return false;
        return self::$redis->hGet($hashKey, $memberKey);
    }

    /**
     * Deletes a value from the hash stored at the key specified
     *
     * @param string $hashKey Key Name for hash
     * @param string $memberKey Key name for member of hash
     * @return mixed Contents of key, or false incase of failure
     */
    public static function hDel($hashKey, $memberKey) {
        if (self::$init === false) return false;
        return self::$redis->hDel($hashKey, $memberKey);
    }

    /**
     * Returns the number of items in the hash
     *
     * @param string $hashKey Key name for hash
     * @return int|bool Number of items or false incase of failure
     */
    public static function hLen($hashKey) {
        if (self::$init === false) return false;
        return self::$redis->hLen($hashKey);
    }

    /**
     * Returns the keys of a hash
     *
     * @param string $hashKey Key name for hash
     * @return string[] Key names from hash, similar to array_keys();
    */
    public static function hKeys($hashKey) {
        if (self::$init === false) return false;
        return self::$redis->hKeys($hashKey);
    }

    /**
     * Returns the values of a hash
     *
     * @param string $hashKey Key name for hash
     * @return string[] Values from hash, similar to array_values();
    */
    public static function hVals($hashKey) {
        if (self::$init === false) return false;
        return self::$redis->hVals($hashKey);
    }

    /**
     * Returns the entire hash as an associative array
     *
     * @param string $hashKey Key name for hash
     * @return string[] Associative array
    */
    public static function hGetAll($hashKey) {
        if (self::$init === false) return false;
        return self::$redis->hGetAll($hashKey);
    }

    /**
     * Checks to see if the hash member key exists
     *
     * @param string $hashKey Key name for hash
     * @param string $memberKey Key name for member of hash
     * @return bool
    */
    public static function hExists($hashKey, $memberKey) {
        if (self::$init === false) return false;
        return self::$redis->hExists($hashKey, $memberKey);
    }

    /**
     * Sets Multiple Hash Members with values specified
     *
     * @param string $hashKey Key name for hash
     * @param mixed[] $inputArray Associative array of key => value pairs
     * @return bool
     */
    public static function hMSet($hashKey, array $inputArray) {
        if (self::$init === false) return false;
        return self::$redis->hMset($hashKey, $inputArray);
    }

    /**
     * Returns Multiple Hash Members from a hash
     *
     * @param string $hashKey Key name for hash
     * @param string[] $inputArray Key names for members of hash
     * @return string[] Associative array of key => value pairs
     */
    public static function hMGet($hashKey, array $inputArray) {
        if (self::$init === false) return false;
        return self::$redis->hMget($hashKey, $inputArray);
    }

    /**
     * hIncrBy 
     * 
     * @param mixed $hashKey 
     * @param mixed $memberKey 
     * @param int $value 
     * @access public 
     * @return void
     */
    public static function hIncrBy($hashKey, $memberKey, $value = 1) {
        if (self::$init === false) return false;
        return self::$redis->hIncrBy($hashKey, $memberKey, $value);
    }

}
