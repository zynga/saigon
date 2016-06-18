<?php
//
// Copyright (c) 2014, Pinterest
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * Datastore Interface Module Information
 **/

define('DSMODULE', 'SaigonES');

/* Redis Integration Information */
if (strtolower(MODE) == 'prod') {
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
}
else {
    define('REDIS_CLUSTER', '127.0.0.1:6379');
    define('REDIS_PREFIX', null);
}

/* Elasticsearch Integration Information */
if (strtolower(MODE) == 'prod') {
    define('ES_LOGGING', false);
    define('ES_LOG', '/var/log/saigon/es.log');
    define('ES_LOGLEVEL', 'Psr\Log\LogLevel::ERROR');
    define('ES_CLUSTER', 'cloudeng-saigon-es-a-master-1:9200,cloudeng-saigon-es-d-master-1:9200,cloudeng-saigon-es-e-master-1:9200');
    define('ES_SHARDS', 5);
    define('ES_REPLICAS', 2);
}
else {
    define('ES_LOGGING', true);
    define('ES_LOG', '/tmp/saigon-es.out');
    define('ES_LOGLEVEL', 'Psr\Log\LogLevel::INFO');
    define('ES_CLUSTER', '127.0.0.1:9200');
    define('ES_SHARDS', 5);
    define('ES_REPLICAS', 0);
}

