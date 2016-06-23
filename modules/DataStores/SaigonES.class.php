<?php
//
// Copyright (c) 2014, Pinterest
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

use \Elasticsearch;

class SaigonES implements DataStoreAPI
{

    protected $es = null;

    public function __construct()
    {
        $esParams = array();
        $esParams['hosts'] = $this->buildESHostArray(ES_CLUSTER);
        $esParams['retries'] = true;
        $esParams['selectorClass'] =
            '\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector';
        $esParams['logging'] = ES_LOGGING;
        $esParams['logPath'] = ES_LOG;
        $esParams['logLevel'] = ES_LOGLEVEL;
        $this->es = new Elasticsearch\Client($esParams);
    }

    private function buildESHostArray($hostConfig)
    {
        if ((!isset($hostConfig)) || (empty($hostConfig))) {
            return array('localhost:9200');
        }
        $results = array();
        if (preg_match("/,/", $hostConfig)) {
            $hosts = preg_split("/\s?,\s?/", $hostConfig);
            foreach ($hosts as $host) {
                array_push($results, $host);
            }
        } else {
                array_push($results, $hostConfig);
        }
        return $results;
    }

    private function createBaseParams($namespace, $type = false, $subtype = false)
    {
        if ( $namespace != 'deployment' ) {
            $params = array(
                'index' => $namespace
            );
            if ( $type !== false ) {
                $params['type'] = $type;
            }
            if ( $subtype !== false) {
                $params['id'] = $subtype;
            }
        }
        else {
            $params = array(
                'index' => $type
            );
            if ( $subtype !== false ) {
                $params['type'] = $subtype;
            }
        }
        return $params;
    }

    private function getDocIDs(array $params)
    {
        $results = array();
        if (!isset($params['fields'])) {
            $params['fields'] = array();
        }
        if (!isset($params['search_type'])) {
            $params['search_type'] = 'scan';
        }
        if (!isset($params['scroll'])) {
            $params['scroll'] = '1m';
        }
        if (!isset($params['size'])) {
            $params['size'] = 250;
        }
        $init_response = $this->es->search($params);
        $scroll_id = $init_response['_scroll_id'];
        while(true) {
            $response = $this->es->scroll(
                array(
                    'scroll_id' => $scroll_id,
                    'scroll' => $params['scroll']
                )
            );
            if (count($response['hits']['hits']) > 0) {
                foreach ($response['hits']['hits'] as $hit) {
                    if (!in_array($hit['_id'], $results)) {
                        array_push($results, $hit['_id']);
                    }
                }
                if ((isset($response['_scroll_id'])) && (!empty($response['_scroll_id']))) {
                    $scroll_id = $response['_scroll_id'];
                }
                else {
                    break;
                }
            }
            else {
                break;
            }
        }
        return $results;
    }

    public function addAuditUserLog($deployment, $revision, $user)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment-audit', $deployment, $revision);
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            $params['body'] = array(
                'users' => array($user),
            );
            $this->es->index($params);
        }
        else {
            $params['body'] = array(
                'script' => "if (ctx._source.users) { if(ctx._source.users.contains(user)) {ctx.op = 'none'} else {ctx._source.users += user} } else { ctx._source.users += user }",
                'params' => array(
                    'user' => $user,
                )
            );
            $this->es->update($params);
        }
    }

    public function getAuditLog($deployment)
    {
        $deployment = strtolower($deployment);
        $revisions = $this->getDeploymentAllRevs($deployment);
        $results = array();
        $params = $this->createBaseParams('deployment-audit', $deployment);
        if (is_array($revisions)) {
            foreach ($revisions as $revision) {
                $params['id'] = $revision;
                $init_data = $this->es->get($params);
                $revdata = array();
                if ( (isset($init_data['_source']['revnote'])) && (!empty($init_data['_source']['revnote'])) ) {
                    $revdata['revnote'] = base64_decode($init_data['_source']['revnote']);
                }
                else {
                    $revdata['revnote'] = 'Not Available';
                }
                if ( (isset($init_data['_source']['revtime'])) && (!empty($init_data['_source']['revtime'])) ) {
                    $revdata['revtime'] = $init_data['_source']['revtime'];
                }
                else {
                    $revdata['revtime'] = 'Not Available';
                }
                if ( (isset($init_data['_source']['users'])) && (!empty($init_data['_source']['users'])) ) {
                    $revdata['users'] = implode(',', $init_data['_source']['users']);
                }
                else {
                    $revdata['users'] = 'Not Available';
                }
                $results[$revision] = $revdata;
            }
        } else {
            $params['id'] = $revisions;
            $init_data = $this->es->get($params);
            if ( (isset($init_data['_source']['revnote'])) && (!empty($init_data['_source']['revnote'])) ) {
                $revdata['revnote'] = base64_decode($init_data['_source']['revnote']);
            }
            else {
                $revdata['revnote'] = 'Not Available';
            }
            if ( (isset($init_data['_source']['revtime'])) && (!empty($init_data['_source']['revtime'])) ) {
                $revdata['revtime'] = $init_data['_source']['revtime'];
            }
            else {
                $revdata['revtime'] = 'Not Available';
            }
            if ( (isset($init_data['_source']['users'])) && (!empty($init_data['_source']['users'])) ) {
                $revdata['users'] = implode(',', $init_data['_source']['users']);
            }
            else {
                $revdata['users'] = 'Not Available';
            }
            $results[$revisions] = $revdata;
        }
        return $results;
    }

    public function setAuditLog($deployment, $revision, array $revisionData)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment-audit', $deployment, $revision);
        $revisionData['users'] = explode(',', $revisionData['users']);
        $revisionData['revnote'] = base64_encode($revisionData['revnote']);
        $revisionData['revtime'] = $revisionData['revtime'];
        $params['body'] = $revisionData;
        $this->es->index($params);
        return true;
    }

    public function getCommonRepos()
    {
        $params = $this->createBaseParams('saigon', 'meta', 'global');
        $params['_source'] = array('commonrepos');
        $response = $this->es->get($params);
        return array_values($response['_source']['commonrepos']);
    }

    public function addCommonRepo($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon', 'meta', 'global');
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            $params['body'] = array(
                'commonrepos' => array( $deployment ),
            );
            return $this->es->index($params);
        }
        else {
            $params['body'] = array(
                'script' => "if(ctx._source.commonrepos) { if(ctx._source.commonrepos.contains(repo)) {ctx.op = 'none'} else {ctx._source.commonrepos += repo} } else { ctx._source.commonrepos += repo }",
                'params' => array(
                    'repo' => $deployment,
                )
            );
            return $this->es->update($params);
        }
    }

    public function delCommonRepo($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon', 'meta', 'global');
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            return false;
        }
        else {
            $params['body'] = array(
                'script' => "if(ctx._source.commonrepos) { if(ctx._source.commonrepos.contains(repo)) {ctx._source.commonrepos.remove(repo)} else { ctx.op = 'none' } } else { ctx.op = 'none' }",
                'params' => array(
                    'repo' => $deployment,
                )
            );
            return $this->es->update($params);
        }
    }

    public function getDeploymentRev($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            return false;
        }
        $params['fields'] = array( 'current' );
        $response = $this->es->get($params);
        return $response['fields']['current'][0];
    }

    public function getDeploymentNextRev($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            return false;
        }
        $params['fields'] = array( 'next' );
        $response = $this->es->get($params);
        return $response['fields']['next'][0];
    }

    public function getDeploymentPrevRev($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            return false;
        }
        $params['fields'] = array( 'previous' );
        $response = $this->es->get($params);
        if ((isset($response['fields']['previous'])) && (!empty($response['fields']['previous'][0]))) {
            return $response['fields']['previous'][0];
        }
        return 1;
    }

    public function getDeploymentRevs($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            return false;
        }
        $params['fields'] = array( 'current', 'next', 'previous' );
        $response = $this->es->get($params);
        $results = array();
        $results['currrev'] = $response['fields']['current'][0];
        $results['nextrev'] = $response['fields']['next'][0];
        $results['prevrev'] = $response['fields']['previous'][0];
        return $results;
    }

    public function getDeploymentAllRevs($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment);
        $results = array();
        $response = $this->es->indices()->getmapping($params);
        if ((isset($response)) && (!empty($response))) {
            foreach ( $response[$deployment]['mappings'] as $key => $mapping ) {
                if ( ( $key == 'mgmt' ) || ( $key == 'meta' ) || ( $key == '_default_' ) ) continue;
                $payload = explode('-', $key);
                if ( !in_array( $payload[0], $results ) ) {
                    array_push( $results, $payload[0] );
                }
            }
        }
        sort($results);
        return $results;
    }

    public function setDeploymentAllRevs($deployment, $prev, $curr, $next)
    {
        $deployment = strtolower($deployment);
        $prev = (int) $prev;
        $curr = (int) $curr;
        $next = (int) $next;
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        $params['body'] = array(
            'next' => (int) $next,
            'current' => (int) $curr,
            'previous' => (int) $prev
        );
        $this->es->index($params);
        return true;
    }

    public function setDeploymentRevs($deployment, $from, $to, $note) 
    {
        $deployment = strtolower($deployment);
        $to = (int) $to;
        $from = (int) $from;
        $revisions = $this->getDeploymentRevs($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        if ( $revisions === false ) {
            $params['body'] = array(
                'next' => $to,
                'current' => $to,
                'previous' => $from,
            );
            $this->es->index($params);
            $note_params = $this->createBaseParams('deployment-audit', $deployment, $to);
            $note_params['body']['doc'] = array(
                'revnote' => base64_encode($note),
                'revtime' => time(),
            );
            $this->es->update($note_params);
            return true;
        }
        else {
            $params['body'] = array(
                'next' => $revisions['nextrev'],
                'current' => $to,
                'previous' => $from,
            );
            $this->es->index($params);
            $note_params = $this->createBaseParams('deployment-audit', $deployment, $to);
            $note_params['body']['doc'] = array(
                'revnote' => base64_encode($note),
                'revtime' => time(),
            );
            $this->es->update($note_params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentRev($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment);
        $response = $this->es->indices()->getMapping($params);
        $locations = array();
        foreach ($response[$deployment]['mappings'] as $key => $value) {
            if ($key == 'meta') continue;
            $payload = explode('-', $key);
            if ((!isset($locations[$payload[0]])) || (!is_array($locations[$payload[0]]))) {
                $locations[$payload[0]] = array();
            }
            array_push($locations[$payload[0]], $payload[1]);
        }
        if (is_array($revision)) {
            foreach ($revision as $subrevision) {
                foreach ($locations[$subrevision] as $location) {
                    $params['type'] = $subrevision.'-'.$location;
                    $this->es->indices()->deleteMapping($params);
                }
            }
        } else {
            foreach ($locations[$revision] as $location) {
                $params['type'] = $revision.'-'.$location;
                $this->es->indices()->deleteMapping($params);
            }
        }
    }

    public function incrDeploymentNextRev($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'revisions';
        $exists = $this->es->exists($params);
        if ( $exists === false ) {
            return false;
        }
        $params['body'] = array(
            'script' => "ctx._source.next += incr",
            'params' => array(
                'incr' => 1
            )
        );
        $this->es->update($params);
        unset($params['body']);
        $params['fields'] = array( 'next' );
        $response = $this->es->get($params);
        return $response['fields']['next'][0];
    }

    public function existsDeploymentRev($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $revisions = $this->getDeploymentAllRevs($deployment);
        if (in_array($revision, $revisions)) {
            return true;
        }
        return false;
    }

    public function deleteDeployment($deployment)
    {
        $deployment = strtolower($deployment);
        $this->delCommonRepo($deployment);
        $params = $this->createBaseParams('deployment', $deployment);
        $this->es->indices()->delete($params);
        $params = $this->createBaseParams('deployment-audit', $deployment);
        $this->es->indices()->deleteMapping($params);
        $params = $this->createBaseParams('saigon', 'meta', 'global');
        $params['body'] = array(
            'script' => "if (ctx._source.repos) { if(ctx._source.repos.contains(repo)) {ctx._source.repos.remove(repo)} else { ctx.op = 'none' } } else { ctx.op = 'none' }",
            'params' => array(
                'repo' => $deployment,
            )
        );
        $this->es->update($params);
    }

    public function createDeployment(
        $deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts
    ) {
        $deployment = strtolower($deployment);
        $sparams = $this->createBaseParams('deployment', 'saigon');
        $siexists = $this->es->indices()->exists(array('index' => 'saigon'));
        $mapping = array();
        $mapping['body']['settings']['number_of_shards'] = ES_SHARDS;
        $mapping['body']['settings']['number_of_replicas'] = ES_REPLICAS;
        $mapping['body']['settings']['analyzer']['default']['type'] = 'standard';
        $mapping['body']['settings']['tokenizer']['default']['type'] = 'standard';
        if ($siexists === false) {
            $mapping['index'] = 'saigon';
            $this->es->indices()->create($mapping);
            $mapping['index'] = 'deployment-audit';
            $this->es->indices()->create($mapping);
            $mapping['index'] = 'saigon-temporal';
            $this->es->indices()->create($mapping);
        }
        $ttl_mapping = array();
        $ttl_mapping['index'] = 'saigon-temporal';
        $ttl_mapping['type'] = $deployment;
        $ttl_mapping['body'][$deployment]['_ttl']['enabled'] = true;
        $this->es->indices()->putMapping($ttl_mapping);
        $dparams = $this->createBaseParams('deployment', $deployment, 'meta');
        $dparams['id'] = 'mgmt';
        if (($exists = $this->existsDeployment($deployment)) === false) {
            $sparams['type'] = 'meta';
            $stexists = $this->es->indices()->existsType(array('index' => 'saigon', 'type' => 'meta'));
            if ($stexists === false) {
                $sparams['id'] = 'global';
                $sparams['body']['repos'] = array( $deployment );
                $sparams['body']['commonrepos'] = array();
                $this->es->index($sparams);
            }
            else {
                $sparams['id'] = 'global';
                $sexists = $this->es->exists($sparams);
                if ($sexists === false) {
                    $sparams['body']['repos'] = $deployment;
                    $sparams['body']['commonrepos'] = array();
                    $this->es->index($sparams);
                }
                else {
                    $sparams['body'] = array(
                        'script' => "if(ctx._source.repos) { if(ctx._source.repos.contains(repo)) { ctx.op = 'none' } else {ctx._source.repos += repo} } else { ctx._source.repos += repo }",
                        'params' => array(
                            'repo' => $deployment,
                        )
                    );
                    $this->es->update($sparams);
                }
            }
            if (isset($deployInfo['revision'])) unset($deployInfo['revision']);
            if (isset($deployInfo['nextrevision'])) unset($deployInfo['nextrevision']);
            if (isset($deployInfo['prevrevision'])) unset($deployInfo['prevrevision']);
            $dparams['body'] = $deployInfo;
            if (!empty($deployHostSearch)) {
                $dparams['body']['dynamic'] = $deployHostSearch;
            }
            if (!empty($deployStaticHosts)) {
                $dparams['body']['static'] = $deployStaticHosts;
            }
            $mapping['index'] = $deployment;
            $this->es->indices()->create($mapping);
            $this->es->index($dparams);
            $drparams = $this->createBaseParams('deployment', $deployment, 'meta');
            $drparams['id'] = 'revisions';
            $drparams['body']['current'] = 1;
            $drparams['body']['next'] = 2;
            $this->es->index($drparams);
            return true;
        }
        return false;
    }

    public function modifyDeployment(
        $deployment, array $deployInfo, array $deployHostSearch, array $deployStaticHosts
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        if (($exists = $this->existsDeployment($deployment)) === true) {
            $params['body'] = $deployInfo;
            if (!empty($deployHostSearch)) {
                $params['body']['dynamic'] = $deployHostSearch;
            }
            if (!empty($deployStaticHosts)) {
                $params['body']['static'] = $deployStaticHosts;
            }
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function addDeploymentDynamicHost($deployment, $md5Key, array $hostInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        if (($exists = $this->es->exists($params)) === true) {
            $params['_source'] = array( 'dynamic' );
            $response = $this->es->get($params);
            if ((isset($response['_source']['dynamic'])) && (!empty($response['_source']['dynamic']))) {
                $dynamic = $response['_source']['dynamic'];
                $dynamic_keys = array_keys($dynamic);
            }
            else {
                $dynamic = array();
                $dynamic_keys = array();
            }
            if (!in_array($md5Key, $dynamic_keys)) {
                $dynamic[$md5Key] = $hostInfo;
                unset($params['_source']);
                $params['body']['doc']['dynamic'] = $dynamic;
                $this->es->update($params);
                return true;
            }
            return true;
        }
        return false;
    }

    public function delDeploymentDynamicHost($deployment, $md5Key)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        if (($exists = $this->es->exists($params)) === true) {
            $params['_source'] = array( 'dynamic' );
            $response = $this->es->get($params);
            if (
                (!isset($response['_source']['dynamic']))
                || (empty($response['_source']['dynamic']))
            ) {
                return array();
            }
            $dynamic = $response['_source']['dynamic'];
            $dynamic_keys = array_keys($dynamic);
            if (in_array($md5Key, $dynamic_keys)) {
                $oldHostSearchInfo = $dynamic[$md5Key];
                unset($params['_source']);
                $params['body']['script'] = 'ctx._source.dynamic.remove(search)';
                $params['body']['params'] = array(
                    'search' => $md5Key,
                );
                $this->es->update($params);
                return $oldHostSearchInfo;
            }
        }
        return array();
    }

    public function addDeploymentStaticHost($deployment, $ip, array $hostInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        if (($exists = $this->es->exists($params)) === true) {
            $params['_source'] = array( 'static' );
            $response = $this->es->get($params);
            if ((isset($response['_source']['static'])) && (!empty($response['_source']['static']))) {
                $static = $response['_source']['static'];
                $static_keys = array_keys($static);
            }
            else {
                $static = array();
                $static_keys = array();
            }
            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
                $ip = NagMisc::encodeIP($ip);
            }
            if (!in_array($ip, $static_keys)) {
                $static[$ip] = $hostInfo;
                unset($params['_source']);
                $params['body']['doc']['static'] = $static;
                $this->es->update($params);
                return true;
            }
            return true;
        }
        return false;
    }

    public function delDeploymentStaticHost($deployment, $ip)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        if (($exists = $this->es->exists($params)) === true) {
            $params['_source'] = array( 'static' );
            $response = $this->es->get($params);
            if (
                (!isset($response['_source']['static']))
                || (empty($response['_source']['static']))
            ) {
                return array();
            }
            $static = $response['_source']['static'];
            $static_keys = array_keys($static);
            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $ip)) {
                $ip = NagMisc::encodeIP($ip);
            }
            if (in_array($ip, $static_keys)) {
                $oldHostSearchInfo = $static[$ip];
                unset($params['_source']);
                $params['body']['script'] = 'ctx._source.static.remove(search)';
                $params['body']['params'] = array(
                    'search' => $ip,
                );
                $this->es->update($params);
                return $oldHostSearchInfo;
            }
        }
        return array();
    }

    public function getDeployments()
    {
        $params = $this->createBaseParams('saigon', 'meta', 'global');
        if (($exists = $this->es->exists($params)) === true) {
            $params['fields'] = 'repos';
            $response = $this->es->get($params);
            if ((isset($response['fields']['repos'])) && (!empty($response['fields']['repos']))) {
                return $response['fields']['repos'];
            }
            return array();
        }
        return array();
    }

    public function existsDeployment($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment);
        return $this->es->indices()->exists(array('index' => $deployment));
    }

    public function getDeploymentInfo($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $response = $this->es->get($params);
        $results = $response['_source'];
        $revisions = $this->getDeploymentRevs($deployment);
        $results['revision'] = $revisions['currrev'];
        $results['nextrevision'] = $revisions['nextrev'];
        if ((isset($revisions['prevrev'])) && (!empty($revisions['prevrev']))) {
            $results['prevrevison'] = $revisions['prevrev'];
        }
        return $results;
    }

    public function getDeploymentCommonRepo($deployment)
    {
        $deployment = strtolower($deployment);
        if ($deployment == 'common') return 'undefined';
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array( 'commonrepo' );
        $response = $this->es->get($params);
        return $response['fields']['commonrepo'][0];
    }

    public function getDeploymentHostSearches($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['_source'] = array( 'dynamic' );
        $response = $this->es->get($params);
        if ((isset($response['_source']['dynamic'])) && (!empty($response['_source']['dynamic']))) {
            return $response['_source']['dynamic'];
        }
        return array();
    }

    public function getDeploymentStaticHosts($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['_source'] = array( 'static' );
        $response = $this->es->get($params);
        if ((isset($response['_source']['static'])) && (!empty($response['_source']['static']))) {
            return $response['_source']['static'];
        }
        return array();
    }

    public function getDeploymentAuthGroup($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array( 'authgroups' );
        $response = $this->es->get($params);
        return $response['fields']['authgroups'][0];
    }

    public function getDeploymentLdapGroup($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array( 'ldapgroups' );
        $response = $this->es->get($params);
        return $response['fields']['ldapgroups'][0];
    }

    public function getDeploymentAliasTemplate($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array( 'aliastemplate' );
        $response = $this->es->get($params);
        return $response['fields']['aliastemplate'][0];
    }

    public function getDeploymentGlobalNegate($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array( 'deploynegate' );
        $response = $this->es->get($params);
        return $response['fields']['deploynegate'][0];
    }

    public function getDeploymentStyle($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array( 'deploystyle' );
        $response = $this->es->get($params);
        return $response['fields']['deploystyle'][0];
    }

    public function getDeploymentMiscSettings($deployment)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, 'meta');
        $params['id'] = 'mgmt';
        $params['fields'] = array(
            'aliastemplate', 'deploystyle', 'deploynegate', 'ensharding',
            'shardkey', 'shardcount', 'chat_rooms'
        );
        $response = $this->es->get($params);
        $results = array();
        if ((isset($response['fields']['aliastemplate'])) && (!empty($response['fields']['aliastemplate'][0]))) {
            $results['aliastemplate'] = $response['fields']['aliastemplate'][0];
        }
        if ((isset($response['fields']['deploystyle'])) && (!empty($response['fields']['deploystyle'][0]))) {
            $results['deploystyle'] = $response['fields']['deploystyle'][0];
        }
        if ((isset($response['fields']['deploynegate'])) && (!empty($response['fields']['deploynegate'][0]))) {
            $results['deploynegate'] = $response['fields']['deploynegate'][0];
        }
        else {
            $results['deploynegate'] = "";
        }
        if ((isset($response['fields']['ensharding'])) && (!empty($response['fields']['ensharding'][0]))) {
            $results['ensharding'] = $response['fields']['ensharding'][0];
        }
        else {
            $results['ensharding'] = 'off';
        }
        if ($results['ensharding'] == 'on') {
            $results['shardkey'] = $response['fields']['shardkey'][0];
            $results['shardcount'] = $response['fields']['shardcount'][0];
        }
        if (CHAT_INTEGRATION === true) {
            if ((isset($response['fields']['chat_rooms'])) && (!empty($response['fields']['chat_rooms'][0]))) {
                $results['chat_rooms'] = $response['fields']['chat_rooms'][0];
            }
        }
        return $results;
    }

    public function getDeploymentCommands($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentCommand($deployment, $revision, $command)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        $params['id'] = $command;
        return $this->es->exists($params);
    }

    public function getDeploymentCommand($deployment, $revision, $command)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        $params['id'] = $command;
        if (($exists = $this->existsDeploymentCommand($deployment, $revision, $command)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentCommandExec($deployment, $revision, $command)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        $params['id'] = $command;
        if (($exists = $this->existsDeploymentCommand($deployment, $revision, $command)) === true) {
            $params['fields'] = 'command_line';
            $response = $this->es->get($params);
            return $response['fields']['command_line'][0];
        }
        return array();
    }

    public function createDeploymentCommand($deployment, $revision, $command, array $commandInput)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        $params['id'] = $command;
        if (($exists = $this->existsDeploymentCommand($deployment, $revision, $command)) === false) {
            $params['body'] = $commandInput;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentCommand($deployment, $revision, $command, array $commandInput)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        $params['id'] = $command;
        if (($exists = $this->existsDeploymentCommand($deployment, $revision, $command)) === true) {
            $params['body'] = $commandInput;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentCommand($deployment, $revision, $command)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-commands');
        $params['id'] = $command;
        if (($exists = $this->existsDeploymentCommand($deployment, $revision, $command)) === true) {
            $commandInfo = $this->es->get($params);
            $this->es->delete($params);
            return $commandInfo['_source'];
        }
        return array();
    }

    public function getDeploymentTimeperiods($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        return $this->getDocIDs($params);
    }


    public function existsDeploymentTimeperiod($deployment, $revision, $timePeriod)
	{
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        return $this->es->exists($params);
    }

    public function existsDeploymentTimeperiodData($deployment, $revision, $timePeriod)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        return $this->es->exists($params);
    }

    public function getDeploymentTimeperiod($deployment, $revision, $timePeriod)
	{
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        if (($exists = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentTimeperiodInfo($deployment, $revision, $timePeriod)
	{
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        if (($exists = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentTimeperiodData($deployment, $revision, $timePeriod)
	{
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        if (($exists = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === true) {
            $params['_source'] = array( 'times' );
            $response = $this->es->get($params);
            return $response['_source']['times'];
        }
        return array();
    }

    public function createDeploymentTimeperiod(
        $deployment, $revision, $timePeriod, array $timePeriodInfo, array $timePeriodData
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        if (($exists = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === false) {
            $params['body'] = $timePeriodInfo;
            $params['body']['times'] = $timePeriodData;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentTimeperiod(
        $deployment, $revision, $timePeriod, array $timePeriodInfo, array $timePeriodData
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        if (($exists = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === true) {
            $params['body'] = $timePeriodInfo;
            $params['body']['times'] = $timePeriodData;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentTimeperiod($deployment, $revision, $timePeriod)
	{
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-timeperiods');
        $params['id'] = $timePeriod;
        if (($exists = $this->existsDeploymentTimeperiod($deployment, $revision, $timePeriod)) === true) {
            $response = $this->getDeploymentTimeperiod($deployment, $revision, $timePeriod);
            $this->es->delete($params);
            return $response;
        }
        return array();
    }

    public function existsDeploymentContact($deployment, $revision, $contact)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacts');
        $params['id'] = $contact;
        return $this->es->exists($params);
    }

    public function getDeploymentContacts($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacts');
        return $this->getDocIDs($params);
    }

    public function getDeploymentContact($deployment, $revision, $contact)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacts');
        $params['id'] = $contact;
        if (($exists = $this->existsDeploymentContact($deployment, $revision, $contact)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentContact($deployment, $revision, $contact, array $contactInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacts');
        $params['id'] = $contact;
        if (($exists = $this->existsDeploymentContact($deployment, $revision, $contact)) === false) {
            $params['body'] = $contactInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentContact($deployment, $revision, $contact, array $contactInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacts');
        $params['id'] = $contact;
        if (($exists = $this->existsDeploymentContact($deployment, $revision, $contact)) === true) {
            $params['body'] = $contactInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentContact($deployment, $revision, $contact)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacts');
        $params['id'] = $contact;
        if (($exists = $this->existsDeploymentContact($deployment, $revision, $contact)) === true) {
            $response = $this->getDeploymentContact($deployment, $revision, $contact);
            $this->es->delete($params);
            return $response;
        }
        return array();
    }

    public function existsDeploymentContactGroup($deployment, $revision, $contactGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contactgroups');
        $params['id'] = $contactGroup;
        return $this->es->exists($params);
    }

    public function getDeploymentContactGroups($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contactgroups');
        return $this->getDocIDs($params);
    }

    public function getDeploymentContactGroup($deployment, $revision, $contactGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contactgroups');
        $params['id'] = $contactGroup;
        if (($exists = $this->existsDeploymentContactGroup($deployment, $revision, $contactGroup)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentContactGroup(
        $deployment, $revision, $contactGroup, array $contactGroupInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contactgroups');
        $params['id'] = $contactGroup;
        if (($exists = $this->existsDeploymentContactGroup($deployment, $revision, $contactGroup)) === false) {
            $params['body'] = $contactGroupInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentContactGroup(
        $deployment, $revision, $contactGroup, array $contactGroupInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contactgroups');
        $params['id'] = $contactGroup;
        if (($exists = $this->existsDeploymentContactGroup($deployment, $revision, $contactGroup)) === true) {
            $params['body'] = $contactGroupInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentContactGroup($deployment, $revision, $contactGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contactgroups');
        $params['id'] = $contactGroup;
        if (($exists = $this->existsDeploymentContactGroup($deployment, $revision, $contactGroup)) === true) {
            $oldContactGroupInfo = $this->getDeploymentContactGroup($deployment, $revision, $contactGroup);
            $this->es->delete($params);
            return $oldContactGroupInfo;
        }
        return array();
    }

    public function getDeploymentContactTemplates($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacttemplates');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacttemplates');
        $params['id'] = $contactTemplate;
        return $this->es->exists($params);
    }

    public function getDeploymentContactTemplate($deployment, $revision, $contactTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacttemplates');
        $params['id'] = $contactTemplate;
        if (($exists = $this->existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentContactTemplate(
        $deployment, $revision, $contactTemplate, array $contactTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacttemplates');
        $params['id'] = $contactTemplate;
        if (($exists = $this->existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)) === false) {
            $params['body'] = $contactTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentContactTemplate(
        $deployment, $revision, $contactTemplate, array $contactTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacttemplates');
        $params['id'] = $contactTemplate;
        if (($exists = $this->existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)) === true) {
            $params['body'] = $contactTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentContactTemplate($deployment, $revision, $contactTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-contacttemplates');
        $params['id'] = $contactTemplate;
        if (($exists = $this->existsDeploymentContactTemplate($deployment, $revision, $contactTemplate)) === true) {
            $oldContactInfo = $this->getDeploymentContactTemplate($deployment, $revision, $contactTemplate);
            $this->es->delete($params);
            return $oldContactInfo;
        }
        return array();
    }

    public function getDeploymentHostTemplates($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hosttemplates');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentHostTemplate($deployment, $revision, $hostTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hosttemplates');
        $params['id'] = $hostTemplate;
        return $this->es->exists($params);
    }

    public function getDeploymentHostTemplate($deployment, $revision, $hostTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hosttemplates');
        $params['id'] = $hostTemplate;
        if (($exists = $this->existsDeploymentHostTemplate($deployment, $revision, $hostTemplate)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentHostTemplate(
        $deployment, $revision, $hostTemplate, array $hostTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hosttemplates');
        $params['id'] = $hostTemplate;
        if (($exists = $this->existsDeploymentHostTemplate($deployment, $revision, $hostTemplate)) === false) {
            $params['body'] = $hostTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentHostTemplate(
        $deployment, $revision, $hostTemplate, array $hostTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hosttemplates');
        $params['id'] = $hostTemplate;
        if (($exists = $this->existsDeploymentHostTemplate($deployment, $revision, $hostTemplate)) === true) {
            $params['body'] = $hostTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentHostTemplate($deployment, $revision, $hostTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hosttemplates');
        $params['id'] = $hostTemplate;
        if (($exists = $this->existsDeploymentHostTemplate($deployment, $revision, $hostTemplate)) === true) {
            $oldHostTemplateInfo = $this->getDeploymentHostTemplate($deployment, $revision, $hostTemplate);
            $this->es->delete($params);
            return $oldHostTemplateInfo;
        }
        return array();
    }

    public function getDeploymentHostGroups($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hostgroups');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentHostGroup($deployment, $revision, $hostGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hostgroups');
        $params['id'] = $hostGroup;
        return $this->es->exists($params);
    }

    public function getDeploymentHostGroup($deployment, $revision, $hostGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hostgroups');
        $params['id'] = $hostGroup;
        if (($exists = $this->existsDeploymentHostGroup($deployment, $revision, $hostGroup)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentHostGroup(
        $deployment, $revision, $hostGroup, array $hostGroupInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hostgroups');
        $params['id'] = $hostGroup;
        if (($exists = $this->existsDeploymentHostGroup($deployment, $revision, $hostGroup)) === false) {
            $params['body'] = $hostGroupInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentHostGroup(
        $deployment, $revision, $hostGroup, array $hostGroupInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hostgroups');
        $params['id'] = $hostGroup;
        if (($exists = $this->existsDeploymentHostGroup($deployment, $revision, $hostGroup)) === false) {
            $params['body'] = $hostGroupInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentHostGroup($deployment, $revision, $hostGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-hostgroups');
        $params['id'] = $hostGroup;
        if (($exists = $this->existsDeploymentHostGroup($deployment, $revision, $hostGroup)) === true) {
            $oldHostGroupInfo = $this->getDeploymentHostGroup($deployment, $revision, $hostGroup);
            $this->es->delete($params);
            return $oldHostGroupInfo;
        }
        return array();
    }

    public function getDeploymentSvcTemplates($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicetemplates');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicetemplates');
        $params['id'] = $svcTemplate;
        return $this->es->exists($params);
    }

    public function getDeploymentSvcTemplate($deployment, $revision, $svcTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicetemplates');
        $params['id'] = $svcTemplate;
        if (($exists = $this->existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentSvcTemplate(
        $deployment, $revision, $svcTemplate, array $svcTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicetemplates');
        $params['id'] = $svcTemplate;
        if (($exists = $this->existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)) === false) {
            $params['body'] = $svcTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSvcTemplate(
        $deployment, $revision, $svcTemplate, array $svcTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicetemplates');
        $params['id'] = $svcTemplate;
        if (($exists = $this->existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)) === true) {
            $params['body'] = $svcTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSvcTemplate($deployment, $revision, $svcTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicetemplates');
        $params['id'] = $svcTemplate;
        if (($exists = $this->existsDeploymentSvcTemplate($deployment, $revision, $svcTemplate)) === true) {
            $oldServiceTemplateInfo = $this->getDeploymentSvcTemplate($deployment, $revision, $svcTemplate);
            $this->es->delete($params);
            return $oldServiceTemplateInfo;
        }
        return array();
    }

    public function getDeploymentSvcGroups($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicegroups');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentSvcGroup($deployment, $revision, $svcGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicegroups');
        $params['id'] = $svcGroup;
        return $this->es->exists($params);
    }

    public function getDeploymentSvcGroup($deployment, $revision, $svcGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicegroups');
        $params['id'] = $svcGroup;
        if (($exists = $this->existsDeploymentSvcGroup($deployment, $revision, $svcGroup)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentSvcGroup($deployment, $revision, $svcGroup, array $svcGrpInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicegroups');
        $params['id'] = $svcGroup;
        if (($exists = $this->existsDeploymentSvcGroup($deployment, $revision, $svcGroup)) === false) {
            $params['body'] = $svcGrpInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSvcGroup($deployment, $revision, $svcGroup, array $svcGrpInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicegroups');
        $params['id'] = $svcGroup;
        if (($exists = $this->existsDeploymentSvcGroup($deployment, $revision, $svcGroup)) === true) {
            $params['body'] = $svcGrpInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSvcGroup($deployment, $revision, $svcGroup)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicegroups');
        $params['id'] = $svcGroup;
        if (($exists = $this->existsDeploymentSvcGroup($deployment, $revision, $svcGroup)) === true) {
            $oldServiceGroupInfo = $this->getDeploymentSvcGroup($deployment, $revision, $svcGroup);
            $this->es->delete($params);
            return $oldServiceGroupInfo;
        }
        return array();
    }

    public function getDeploymentSvcs($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-services');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentSvc($deployment, $revision, $svc)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-services');
        $params['id'] = $svc;
        return $this->es->exists($params);
    }

    public function getDeploymentSvc($deployment, $revision, $svc)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-services');
        $params['id'] = $svc;
        if (($exists = $this->existsDeploymentSvc($deployment, $revision, $svc)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentSvc($deployment, $revision, $svc, array $svcInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-services');
        $params['id'] = $svc;
        if (($exists = $this->existsDeploymentSvc($deployment, $revision, $svc)) === false) {
            $params['body'] = $svcInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSvc($deployment, $revision, $svc, array $svcInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-services');
        $params['id'] = $svc;
        if (($exists = $this->existsDeploymentSvc($deployment, $revision, $svc)) === true) {
            $params['body'] = $svcInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSvc($deployment, $revision, $svc)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-services');
        $params['id'] = $svc;
        if (($exists = $this->existsDeploymentSvc($deployment, $revision, $svc)) === true) {
            $oldServiceInfo = $this->getDeploymentSvc($deployment, $revision, $svc);
            $this->es->delete($params);
            return $oldServiceInfo;
        }
        return array();
    }

    public function getDeploymentSvcDependencies($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicedependencies');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentSvcDependency($deployment, $revision, $svcDep)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicedependencies');
        $params['id'] = $svcDep;
        return $this->es->exists($params);
    }

    public function getDeploymentSvcDependency($deployment, $revision, $svcDep)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicedependencies');
        $params['id'] = $svcDep;
        if (($exists = $this->existsDeploymentSvcDependency($deployment, $revision, $svcDep)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentSvcDependency(
        $deployment, $revision, $svcDep, array $svcDepInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicedependencies');
        $params['id'] = $svcDep;
        if (($exists = $this->existsDeploymentSvcDependency($deployment, $revision, $svcDep)) === false) {
            $params['body'] = $svcDepInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSvcDependency(
        $deployment, $revision, $svcDep, array $svcDepInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicedependencies');
        $params['id'] = $svcDep;
        if (($exists = $this->existsDeploymentSvcDependency($deployment, $revision, $svcDep)) === true) {
            $params['body'] = $svcDepInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSvcDependency($deployment, $revision, $svcDep)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-servicedependencies');
        $params['id'] = $svcDep;
        if (($exists = $this->existsDeploymentSvcDependency($deployment, $revision, $svcDep)) === true) {
            $oldServiceDepInfo = $this->getDeploymentSvcDependency($deployment, $revision, $svcDep);
            $this->es->delete($params);
            return $oldServiceDepInfo;
        }
        return array();
    }

    public function getDeploymentSvcEscalations($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-serviceescalations');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-serviceescalations');
        $params['id'] = $svcEsc;
        return $this->es->exists($params);
    }

    public function getDeploymentSvcEscalation($deployment, $revision, $svcEsc)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-serviceescalations');
        $params['id'] = $svcEsc;
        if (($exists = $this->existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentSvcEscalation(
        $deployment, $revision, $svcEsc, array $svcEscInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-serviceescalations');
        $params['id'] = $svcEsc;
        if (($exists = $this->existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)) === false) {
            $params['body'] = $svcEscInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSvcEscalation(
        $deployment, $revision, $svcEsc, array $svcEscInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-serviceescalations');
        $params['id'] = $svcEsc;
        if (($exists = $this->existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)) === true) {
            $params['body'] = $svcEscInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSvcEscalation($deployment, $revision, $svcEsc)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-serviceescalations');
        $params['id'] = $svcEsc;
        if (($exists = $this->existsDeploymentSvcEscalation($deployment, $revision, $svcEsc)) === true) {
            $oldServiceEscInfo = $this->getDeploymentSvcEscalation($deployment, $revision, $svcEsc);
            $this->es->delete($params);
            return $oldServiceEscInfo;
        }
        return array();
    }

    public function getDeploymentNodeTemplates($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        return $this->es->exists($params);
    }

    public function getDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        if (($exists = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentNodeTemplate(
        $deployment, $revision, $nodeTemplate, array $nodeTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        if (($exists = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === false) {
            $params['body'] = $nodeTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentNodeTemplate(
        $deployment, $revision, $nodeTemplate, array $nodeTemplateInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        if (($exists = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === true) {
            $params['body'] = $nodeTemplateInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        if (($exists = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === true) {
            $oldNodeTemplateInfo = $this->getDeploymentNodeTemplate($deployment, $revision, $nodeTemplate);
            $this->es->delete($params);
            return $oldNodeTemplateInfo;
        }
        return array();
    }

    public function getDeploymentNodeTemplateType($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        if (($exists = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === true) {
            $params['fields'] = array( 'type' );
            $response = $this->es->get($params);
            return $response['fields']['type'];
        }
        return null;
    }

    public function addDeploymentUnclassifiedTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        return true;
    }

    public function deleteDeploymentUnclassifiedTemplate($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        return true;
    }

    public function existsDeploymentUnclassifiedTemplate($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['body']['filter']['term']['type'] = 'unclassified';
        $results = $this->getDocIDs($params);
        if (count($results) > 0) {
            return true;
        }
        return false;
    }

    public function addDeploymentStandardTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        return true;
    }

    public function deleteDeploymentStandardTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        return true;
    }

    public function existsDeploymentStandardTemplate($deployment, $revision, $nodeTemplate)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['id'] = $nodeTemplate;
        if (($exists = $this->existsDeploymentNodeTemplate($deployment, $revision, $nodeTemplate)) === true) {
            $type = $this->getDeploymentNodeTemplateType($deployment, $revision, $nodeTemplate);
            if ($type == 'standard') {
                return true;
            }
            return false;
        }
        return false;
    }

    public function getDeploymentStandardTemplates($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nodetemplates');
        $params['body']['filter']['term']['type'] = 'standard';
        return $this->getDocIDs($params);
    }

    public function existsDeploymentResourceCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'resource';
        return $this->es->exists($params);
    }

    public function getDeploymentResourceCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'resource';
        if (($exists = $this->existsDeploymentResourceCfg($deployment, $revision)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentResourceCfg($deployment, $revision, array $resources)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'resource';
        if (($exists = $this->existsDeploymentResourceCfg($deployment, $revision)) === false) {
            $params['body'] = $resources;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentResourceCfg($deployment, $revision, array $resources)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'resource';
        if (($exists = $this->existsDeploymentResourceCfg($deployment, $revision)) === true) {
            $params['body'] = $resources;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentResourceCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'resource';
        if (($exists = $this->existsDeploymentResourceCfg($deployment, $revision)) === true) {
            $oldResourceCfgInfo = $this->getDeploymentResourceCfg($deployment, $revision);
            $this->es->delete($params);
            return $oldResourceCfgInfo;
        }
        return array();
    }

    public function existsDeploymentModgearmanCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'modgearman';
        return $this->es->exists($params);
    }

    public function getDeploymentModgearmanCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'modgearman';
        if (($exists = $this->existsDeploymentModgearmanCfg($deployment, $revision)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentModgearmanCfg($deployment, $revision, array $cfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'modgearman';
        if (($exists = $this->existsDeploymentModgearmanCfg($deployment, $revision)) === false) {
            $params['body'] = $cfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentModgearmanCfg($deployment, $revision, array $cfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'modgearman';
        if (($exists = $this->existsDeploymentModgearmanCfg($deployment, $revision)) === true) {
            $params['body'] = $cfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentModgearmanCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'modgearman';
        if (($exists = $this->existsDeploymentModgearmanCfg($deployment, $revision)) === true) {
            $oldModgearmanCfgInfo = $this->getDeploymentModgearmanCfg($deployment, $revision);
            $this->es->delete($params);
            return $oldModgearmanCfgInfo;
        }
        return array();
    }

    public function existsDeploymentCgiCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'cgi';
        return $this->es->exists($params);
    }

    public function getDeploymentCgiCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'cgi';
        if (($exists = $this->existsDeploymentCgiCfg($deployment, $revision)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentCgiCfg($deployment, $revision, $cfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'cgi';
        if (($exists = $this->existsDeploymentCgiCfg($deployment, $revision)) === false) {
            $params['body'] = $cfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentCgiCfg($deployment, $revision, $cfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'cgi';
        if (($exists = $this->existsDeploymentCgiCfg($deployment, $revision)) === true) {
            $params['body'] = $cfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentCgiCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'cgi';
        if (($exists = $this->existsDeploymentCgiCfg($deployment, $revision)) === true) {
            $oldCgiCfgInfo = $this->getDeploymentCgiCfg($deployment, $revision);
            $this->es->delete($params);
            return $oldCgiCfgInfo;
        }
        return array();
    }

    public function existsDeploymentNagiosCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nagios';
        return $this->es->exists($params);
    }

    public function getDeploymentNagiosCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nagios';
        if (($exists = $this->existsDeploymentNagiosCfg($deployment, $revision)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentNagiosCfg($deployment, $revision, $nagiosInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nagios';
        if (($exists = $this->existsDeploymentNagiosCfg($deployment, $revision)) === false) {
            $params['body'] = $nagiosInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentNagiosCfg($deployment, $revision, $nagiosInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nagios';
        if (($exists = $this->existsDeploymentNagiosCfg($deployment, $revision)) === true) {
            $params['body'] = $nagiosInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentNagiosCfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nagios';
        if (($exists = $this->existsDeploymentNagiosCfg($deployment, $revision)) === true) {
            $oldNagiosCfgInfo = $this->getDeploymentNagiosCfg($deployment, $revision);
            $this->es->delete($params);
            return $oldNagiosCfgInfo;
        }
        return array();
    }

    public function getDeploymentNRPECmds($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        $params['id'] = $nrpeCmd;
        return $this->es->exists($params);
    }

    public function getDeploymentNRPECmd($deployment, $revision, $nrpeCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        $params['id'] = $nrpeCmd;
        if (($exists = $this->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentNRPECmdLine($deployment, $revision, $nrpeCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        $params['id'] = $nrpeCmd;
        if (($exists = $this->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)) === true) {
            $params['fields'] = array( 'cmd_line' );
            $response = $this->es->get($params);
            return $response['fields']['cmd_line'];
        }
        return null;
    }

    public function createDeploymentNRPECmd($deployment, $revision, $nrpeCmd, array $nrpeCmdInput)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        $params['id'] = $nrpeCmd;
        if (($exists = $this->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)) === false) {
            $params['body'] = $nrpeCmdInput;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentNRPECmd($deployment, $revision, $nrpeCmd, array $nrpeCmdInput)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        $params['id'] = $nrpeCmd;
        if (($exists = $this->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)) === true) {
            $params['body'] = $nrpeCmdInput;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentNRPECmd($deployment, $revision, $nrpeCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpecommands');
        $params['id'] = $nrpeCmd;
        if (($exists = $this->existsDeploymentNRPECmd($deployment, $revision, $nrpeCmd)) === true) {
            $oldNRPECmdInfo = $this->getDeploymentNRPECmd($deployment, $revision, $nrpeCmd);
            $this->es->delete($params);
            return $oldNRPECmdInfo;
        }
        return array();
    }

    public function existsDeploymentNRPECfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nrpe';
        return $this->es->exists($params);
    }

    public function getDeploymentNRPECfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nrpe';
        if (($exists = $this->existsDeploymentNRPECfg($deployment, $revision)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentNRPECfg($deployment, $revision, array $nrpeCfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nrpe';
        if (($exists = $this->existsDeploymentNRPECfg($deployment, $revision)) === false) {
            $params['body'] = $nrpeCfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentNRPECfg($deployment, $revision, array $nrpeCfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nrpe';
        if (($exists = $this->existsDeploymentNRPECfg($deployment, $revision)) === true) {
            $params['body'] = $nrpeCfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentNRPECfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'nrpe';
        if (($exists = $this->existsDeploymentNRPECfg($deployment, $revision)) === true) {
            $oldNRPECfgInfo = $this->getDeploymentNRPECfg($deployment, $revision);
            $this->es->delete($params);
            return $oldNRPECfgInfo;
        }
        return array();
    }

    public function existsDeploymentSupNRPECfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'supplementalnrpe';
        return $this->es->exists($params);
    }

    public function getDeploymentSupNRPECfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'supplementalnrpe';
        if (($exists = $this->existsDeploymentSupNRPECfg($deployment, $revision)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function createDeploymentSupNRPECfg($deployment, $revision, array $supNRPECfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'supplementalnrpe';
        if (($exists = $this->existsDeploymentSupNRPECfg($deployment, $revision)) === false) {
            $params['body'] = $supNRPECfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSupNRPECfg($deployment, $revision, array $supNRPECfgInfo)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'supplementalnrpe';
        if (($exists = $this->existsDeploymentSupNRPECfg($deployment, $revision)) === true) {
            $params['body'] = $supNRPECfgInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSupNRPECfg($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-configs');
        $params['id'] = 'supplementalnrpe';
        if (($exists = $this->existsDeploymentSupNRPECfg($deployment, $revision)) === true) {
            $oldSupNRPECfgInfo = $this->getDeploymentSupNRPECfg($deployment, $revision);
            $this->es->delete($params);
            return $oldSupNRPECfgInfo;
        }
        return array();
    }

    public function getDeploymentNRPEPlugins($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        return $this->getDocIDs($params);
    }
    
    public function existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        $params['id'] = $nrpePlugin;
        return $this->es->exists($params);
    }

    public function getDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        $params['id'] = $nrpePlugin;
        if (($exists = $this->existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentNRPEPluginFileContents($deployment, $revision, $nrpePlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        $params['id'] = $nrpePlugin;
        if (($exists = $this->existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)) === true) {
            $params['fields'] = array( 'file' );
            $response = $this->es->get($params);
            return $response['fields']['file'][0];
        }
        return null;
    }

    public function createDeploymentNRPEPlugin(
        $deployment, $revision, $nrpePlugin, array $nrpePluginInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        $params['id'] = $nrpePlugin;
        if (($exists = $this->existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)) === false) {
            $params['body'] = $nrpePluginInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentNRPEPlugin(
        $deployment, $revision, $nrpePlugin, array $nrpePluginInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        $params['id'] = $nrpePlugin;
        if (($exists = $this->existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)) === true) {
            $params['body'] = $nrpePluginInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nrpeplugins');
        $params['id'] = $nrpePlugin;
        if (($exists = $this->existsDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin)) === true) {
            $oldNPREPluginInfo = $this->getDeploymentNRPEPlugin($deployment, $revision, $nrpePlugin);
            $this->es->delete($params);
            return $oldNPREPluginInfo;
        }
        return array();
    }

    public function getDeploymentSupNRPEPlugins($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        return $this->getDocIDs($params);
    }
    
    public function existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        $params['id'] = $supNRPEPlugin;
        return $this->es->exists($params);
    }

    public function getDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        $params['id'] = $supNRPEPlugin;
        if (($exists = $this->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentSupNRPEPluginFileContents($deployment, $revision, $supNRPEPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        $params['id'] = $supNRPEPlugin;
        if (($exists = $this->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)) === true) {
            $params['fields'] = array( 'file' );
            $response = $this->es->get($params);
            return $response['fields']['file'][0];
        }
        return null;
    }

    public function createDeploymentSupNRPEPlugin(
        $deployment, $revision, $supNRPEPlugin, array $supNRPEPluginInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        $params['id'] = $supNRPEPlugin;
        if (($exists = $this->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)) === false) {
            $params['body'] = $supNRPEPluginInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentSupNRPEPlugin(
        $deployment, $revision, $supNRPEPlugin, array $supNRPEPluginInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        $params['id'] = $supNRPEPlugin;
        if (($exists = $this->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)) === true) {
            $params['body'] = $supNRPEPluginInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-supplementalnrpeplugins');
        $params['id'] = $supNRPEPlugin;
        if (($exists = $this->existsDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin)) === true) {
            $oldSupNRPEPluginInfo =
                $this->getDeploymentSupNRPEPlugin($deployment, $revision, $supNRPEPlugin);
            $this->es->delete($params);
            return $oldSupNRPEPluginInfo;
        }
        return array();
    }

    public function getDeploymentNagiosPlugins($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        return $this->getDocIDs($params);
    }
    
    public function existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        $params['id'] = $nagiosPlugin;
        return $this->es->exists($params);
    }

    public function getDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        $params['id'] = $nagiosPlugin;
        if (($exists = $this->existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentNagiosPluginFileContents($deployment, $revision, $nagiosPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        $params['id'] = $nagiosPlugin;
        if (($exists = $this->existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)) === true) {
            $params['fields'] = array( 'file' );
            $response = $this->es->get($params);
            return $response['fields']['file'][0];
        }
        return null;
    }

    public function createDeploymentNagiosPlugin(
        $deployment, $revision, $nagiosPlugin, array $nagiosPluginInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        $params['id'] = $nagiosPlugin;
        if (($exists = $this->existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)) === false) {
            $params['body'] = $nagiosPluginInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentNagiosPlugin(
        $deployment, $revision, $nagiosPlugin, array $nagiosPluginInfo
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        $params['id'] = $nagiosPlugin;
        if (($exists = $this->existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)) === true) {
            $params['body'] = $nagiosPluginInfo;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-nagiosplugins');
        $params['id'] = $nagiosPlugin;
        if (($exists = $this->existsDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin)) === true) {
            $oldNagiosPluginInfo =
                $this->getDeploymentNagiosPlugin($deployment, $revision, $nagiosPlugin);
            $this->es->delete($params);
            return $oldNagiosPluginInfo;
        }
        return array();
    }

    public function getDeploymentClusterCmds($deployment, $revision)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        return $this->getDocIDs($params);
    }

    public function existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        $params['id'] = $clusterCmd;
        return $this->es->exists($params);
    }

    public function getDeploymentClusterCmd($deployment, $revision, $clusterCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        $params['id'] = $clusterCmd;
        if (($exists = $this->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function getDeploymentClusterCmdLine($deployment, $revision, $clusterCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        $params['id'] = $clusterCmd;
        if (($exists = $this->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)) === true) {
            $params['fields'] = array( 'cmd_line' );
            $response = $this->es->get($params);
            return $response['fields']['cmd_line'];
        }
        return null;
    }

    public function createDeploymentClusterCmd(
        $deployment, $revision, $clusterCmd, array $clusterCmdInput
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        $params['id'] = $clusterCmd;
        if (($exists = $this->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)) === false) {
            $params['body'] = $clusterCmdInput;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function modifyDeploymentClusterCmd(
        $deployment, $revision, $clusterCmd, array $clusterCmdInput
    ) {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        $params['id'] = $clusterCmd;
        if (($exists = $this->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)) === true) {
            $params['body'] = $clusterCmdInput;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteDeploymentClusterCmd($deployment, $revision, $clusterCmd)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('deployment', $deployment, $revision.'-clustercommands');
        $params['id'] = $clusterCmd;
        if (($exists = $this->existsDeploymentClusterCmd($deployment, $revision, $clusterCmd)) === true) {
            $oldClusterCommandInfo =
                $this->getDeploymentClusterCmd($deployment, $revision, $clusterCmd);
            $this->es->delete($params);
            return $oldClusterCommandInfo;
        }
        return array();
    }

    public function existsConsumerDeploymentLock($deployment, $revision, $lockType)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon-temporal', $deployment);
        if ($lockType == 'diff') $params['id'] = 'diff-lock';
        else $params['id'] = $lockType.'-lock';
        return $this->es->exists($params);
    }

    public function createConsumerDeploymentLock($deployment, $revision, $lockType, $ttl)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon-temporal', $deployment);
        if ($lockType == 'diff') $params['id'] = 'diff-lock';
        else $params['id'] = $lockType.'-lock';
        if (($exists = $this->existsConsumerDeploymentLock($deployment, $revision, $lockType)) === false) {
            $params['body']['active'] = true;
            $params['ttl'] = $ttl;
            $this->es->index($params);
            return true;
        }
        return false;
    }

    public function deleteConsumerDeploymentLock($deployment, $revision, $lockType)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon-temporal', $deployment);
        if ($lockType == 'diff') $params['id'] = 'diff-lock';
        else $params['id'] = $lockType.'-lock';
        if (($exists = $this->existsConsumerDeploymentLock($deployment, $revision, $lockType)) === true) {
            $this->es->delete($params);
            return true;
        }
        return false;
    }

    public function setConsumerDeploymentInfo($deployment, $revision, $infoType, $info)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon-temporal', $deployment, $revision);
        if ($infoType == 'diff') {
            $params['id'] = 'diff-output';
            if (($exists = $this->es->exists($params)) === false) {
                $params['body'] = $info;
                $this->es->index($params);
            }
            else {
                $params['body'] = $info;
                $this->es->index($params);
            }
        }
        elseif ($infoType == 'hostaudit') {
            $params['id'] = 'host-audit';
            if (($exists = $this->es->exists($params)) === false) {
                $params['body'] = $info;
                $this->es->index($params);
            }
            else {
                $params['body'] = $info;
                $this->es->index($params);
            }
        }
        else {
            $params['id'] = $infoType.'-output';
            if (($exists = $this->es->exists($params)) === false) {
                $params['body'] = $info;
                $this->es->index($params);
            }
            else {
                $params['body'] = $info;
                $this->es->index($params);
            }
        }
        return true;
    }

    public function getConsumerDeploymentInfo($deployment, $revision, $infoType)
    {
        $deployment = strtolower($deployment);
        $params = $this->createBaseParams('saigon-temporal', $deployment, $revision);
        if ($infoType == 'diff') {
            $params['id'] = 'diff-output';
            if (($exists = $this->es->exists($params)) === true) {
                $response = $this->es->get($params);
                return $response['_source'];
            }
            return array();
        }
        elseif ($infoType == 'hostaudit') {
            $params['id'] = 'host-audit';
            if (($exists = $this->es->exists($params)) === true) {
                $response = $this->es->get($params);
                return $response['_source'];
            }
            return array();
        }
        else {
            $params['id'] = $infoType.'-output';
            if (($exists = $this->es->exists($params)) === true) {
                $response = $this->es->get($params);
                return $response['_source'];
            }
            return array();
        }
    }

    public function getCDCRouterZones()
    {
        $params = $this->createBaseParams('deployment', 'saigon', 'cdc');
        return $this->getDocIDs($params);
    }

    public function existsCDCRouterZone($zone)
    {
        $params = $this->createBaseParams('deployment', 'saigon', 'cdc');
        $params['id'] = $zone;
        return $this->es->exists($params);
    }

    public function getCDCRouterZone($zone)
    {
        $params = $this->createBaseParams('deployment', 'saigon', 'cdc');
        $params['id'] = $zone;
        if (($exists = $this->existsCDCRouterZone($zone)) === true) {
            $response = $this->es->get($params);
            return $response['_source'];
        }
        return array();
    }

    public function writeCDCRouterZones(array $zoneData)
    {
        $params = $this->createBaseParams('deployment', 'saigon', 'cdc');
        $oldZones = $this->getCDCRouterZones();
        foreach ($oldZones as $oldZone) {
            $params['id'] = $oldZone;
            $this->es->delete($params);
        }
        foreach ($zoneData as $zone => $zoneInfo) {
            $params['id'] = $zone;
            $params['body'] = $zoneInfo;
            $this->es->index($params);
        }
        return true;
    }

}
