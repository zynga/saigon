<?php
//
// Copyright (c) 2015, Pinterest.
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Clustered Commands Routes
 */

function cc_rkeyMessage ($rkey) {
    switch($rkey) {
        case "max_check_attempts":
            return "(max number of check attempts before taking some action)"; break;
        case "check_interval":
            return "(max amount of time between checks)"; break;
        case "retry_interval":
            return "(max amount of time between checks in a non-OK state)"; break;
        case "check_period":
            return "(timeperiod to use for checking this host)"; break;
        case "notification_interval":
            return "(max amount of time between sending of notifications)"; break;
        case "notification_period":
            return "(timeperiod to use for sending notifications about this host)"; break;
        default:
            break;
    }
}

function cc_validate ($app, $deployment, $clusteredcommandInfo) {
    foreach ($clusteredcommandInfo as $key => $value) {
        switch ($key) {
            case "use":
            case "check_period":
            case "notification_period":
            case "icon_image":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "contacts":
            case "contact_groups":
            case "servicegroups":
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $subvalue);
                    }
                    break;
                }
                else {
                    validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
                }
            case "is_volatile":
            case "active_checks_enabled":
            case "passive_checks_enabled":
            case "obsess_over_service":
            case "check_freshness":
            case "event_handler_enabled":
            case "flap_detection_enabled":
            case "parallelize_check":
            case "process_perf_data":
            case "retain_status_information":
            case "retain_nonstatus_information":
            case "notifications_enabled":
                validateBinary($app, $deployment, $key, $value); break;
            case "check_command":
            case "event_handler":
                validateForbiddenChars($app, $deployment, '/[^\w.-$\/]/s', $key, $value); break;
            case "initial_state":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','u','c'), true);
                $clusteredcommandInfo[$key] = $opts;
                break;
            case "max_check_attempts":
                validateInterval($app, $deployment, $key, $value, 1, 20); break;
            case "check_interval":
                validateInterval($app, $deployment, $key, $value, 1, 1440); break;
            case "retry_interval":
                validateInterval($app, $deployment, $key, $value, 1, 720); break;
            case "freshness_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 86400); break;
            case "low_flap_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 99); break;
            case "high_flap_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 100); break;
            case "flap_detection_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','c','u'), true);
                $clusteredcommandInfo[$key] = $opts;
                break;
            case "notification_interval":
            case "first_notification_delay":
                validateInterval($app, $deployment, $key, $value, 0, 1440); break;
            case "notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('w','u','c','r','f','s'), true);
                $clusteredcommandInfo[$key] = $opts;
                break;
            case "stalking_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','u','c'), true);
                $clusteredcommandInfo[$key] = $opts;
                break;
            case "icon_image_alt":
            case "notes":
                validateForbiddenChars($app, $deployment, '/[^\w.-\s]/s', $key, $value); break;
            case "notes_url":
            case "action_url":
                validateUrl($app, $deployment, $key, $value); break;
            default:
                break;
        }
    }
    // We never want to see single threaded checks running, force this...
    if ((!isset($clusteredcommandInfo['parallelize_check'])) || (empty($clusteredcommandInfo['parallelize_check']))) {
        $clusteredcommandInfo['parallelize_check'] = 1;
    }
    return $clusteredcommandInfo;
}

$app->get('/sapi/clusteredcommands/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    if ($staged == 1) {
        $revs = RevDeploy::getDeploymentRevs($deployment);
        if ($revs['currrev'] == $revs['nextrev']) { 
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect staged revision to reference");
            $app->halt(404, $apiResponse->returnJson());
        }
        $deployRev = $revs['nextrev'];
    }
    else {
        $deployRev = RevDeploy::getDeploymentRev($deployment);
    }
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('clusteredcommands',
        RevDeploy::getDeploymentClusterCmdswInfo($deployment, $deployRev)
    );
    $apiResponse->printJson();
})->name('saigon-api-get-clusteredcommands');

$app->get('/sapi/clusteredcommand/:deployment/:clusteredcommand(/:staged)', function ($deployment, $clusteredcommand, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    if ($staged == 1) {
        $revs = RevDeploy::getDeploymentRevs($deployment);
        if ($revs['currrev'] == $revs['nextrev']) { 
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect staged revision to reference");
            $app->halt(404, $apiResponse->returnJson());
        }
        $deployRev = $revs['nextrev'];
    }
    else {
        $deployRev = RevDeploy::getDeploymentRev($deployment);
    }
    if (preg_match('/,/', $clusteredcommand)) {
        $clusteredcommands = preg_split('/\s?,\s?/', $clusteredcommand);
        $results = array();
        foreach ($clusteredcommands as $stemp) {
            if (RevDeploy::existsDeploymentClusterCmd($deployment, $stemp, $deployRev) === true) {
                $results[$stemp] = RevDeploy::getDeploymentClusterCmd($deployment, $stemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect clusteredcommands specified: $clusteredcommand");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('clusteredcommands', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentClusterCmd($deployment, $clusteredcommand, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect clusteredcommand specified: $clusteredcommand");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $clusteredcommandInfo = RevDeploy::getDeploymentClusterCmd($deployment, $clusteredcommand, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('clusteredcommand', $clusteredcommandInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-clusteredcommand');

$app->post('/sapi/clusteredcommand/:deployment/view', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $tmpccInfo = $request->getBody();
        $tmpccInfo = json_decode($tmpccInfo,true);
        $query = $tmpccInfo['query'];
        $server = $tmpccInfo['server'];
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $query = $request->post('query');
        $server = $request->post('server');
    }
    if ($query === false) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect query parameter");
        $app->halt(404, $apiResponse->returnJson());
    }
    $url = CLUSTER_COMMANDS_URL . '?query=' . urlencode(str_replace('\n', "\n", $query)) . '&ui=json';
    if ($server !== false) {
        $url .= "&server=" . $server;
    }
    /* Initialize Curl and Issue Request */
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    /* Response or No Response ? */
    $response   = curl_exec($ch);
    $errno      = curl_errno($ch);
    $errstr     = curl_error($ch);
    curl_close($ch);
    if ($errno) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to process request: $errno : $errstr");
        $app->halt(404, $apiResponse->returnJson());
    }
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('results', $response);
    $apiResponse->printJson();
})->name('saigon-api-view-clusteredcommand');

$app->post('/sapi/clusteredcommand/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $tmpccInfo = $request->getBody();
        $tmpccInfo = json_decode($tmpccInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','type','cctype','server','query','warnmin','warnmax','crit','warnmode','critmode','quorum',
            'use','service_description','servicegroups','is_volatile','check_command','initial_state',
            'max_check_attempts','check_interval','retry_interval','active_checks_enabled','passive_checks_enabled',
            'check_period', 'obsess_over_service','check_freshness','freshness_threshold','event_handler',
            'event_handler_enabled','low_flap_threshold','high_flap_threshold','flap_detection_enabled',
            'flap_detection_options','process_perf_data','retain_status_information','retain_nonstatus_information',
            'notification_interval','first_notification_delay','notification_period','notification_options',
            'notifications_enabled','contacts','contact_groups','stalking_options','icon_image','icon_image_alt',
            'parallelize_check','notes','notes_url','action_url'
        );
        $tmpccInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $tmpccInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'max_check_attempts','check_interval', 'retry_interval',
        'check_period','notification_interval','notification_period'
    );
    if (empty($tmpccInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any clusteredcommand information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($tmpccInfo['name'])) || (empty($tmpccInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $tmpccInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use clusteredcommand name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($tmpccInfo['service_description'])) || (empty($tmpccInfo['service_description']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect service_description parameter (longer human readable information about service, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $tmpccInfo['service_description'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use clusteredcommand service_description specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($tmpccInfo['type'])) || (empty($tmpccInfo['type']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect type parameter ( service or host expected as type)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        validateOptions($app, $deployment, 'type', $tmpccInfo['type'], array('service', 'host'), true);
    }
    if ((!isset($tmpccInfo['cctype'])) || (empty($tmpccInfo['cctype']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect cctype paramter (basic or quorum expected as cctype)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        validateOptions($app, $deployment, 'cctype', $tmpccInfo['cctype'], array('basic','quorum'), true);
    }
    if ((isset($tmpccInfo['server'])) || (!empty($tmpccInfo['server']))) {
        if (is_array($tmpccInfo['server'])) {
            $tmpccInfo['server'] = implode(',', $tmpccInfo['server']);
        }
    }
    else {
        unset($tmpccInfo['server']);
    }
    if ((!isset($tmpccInfo['query'])) || (empty($tmpccInfo['query']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect query parameter. This is the query used to extract data from the Livestatus API"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        $tmpccInfo['query'] = base64_encode(str_replace('\n', "\n", $tmpccInfo['query']));
    }
    if ((!isset($tmpccInfo['warnmin'])) || (empty($tmpccInfo['warnmin']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect warnmin parameter. ( minimum number of host or service warnings to cause a warning alert )"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        validateForbiddenChars($app, $deployment, '/[^0-9]/s', 'warnmin', $tmpccInfo['warnmin']);
    }
    if ((!isset($tmpccInfo['warnmax'])) || (empty($tmpccInfo['warnmax']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect warnmax parameter. ( maximum number of host or service warnings to cause a critical alert )"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        validateForbiddenChars($app, $deployment, '/[^0-9]/s', 'warnmax', $tmpccInfo['warnmax']);
    }
    if ((!isset($tmpccInfo['crit'])) || (empty($tmpccInfo['crit']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect crit parameter. ( number of host or service criticals to cause a critical alert )"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        validateForbiddenChars($app, $deployment, '/[^0-9]/s', 'crit', $tmpccInfo['crit']);
    }
    if ($tmpccInfo == 'basic') {
        if ((!isset($tmpccInfo['warnmode'])) || (empty($tmpccInfo['warnmode']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect warnmode parameter. ( integer or percentage expected as warnmode )"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            validateOptions($app, $deployment, 'warnmode', $tmpccInfo['warnmode'], array('integer','percentage'), true);
        }
        if ((!isset($tmpccInfo['critmode'])) || (empty($tmpccInfo['critmode']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect critmode parameter. ( integer or percentage expected as critmode )"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            validateOptions($app, $deployment, 'critmode', $tmpccInfo['critmode'], array('integer','percentage'), true);
        }
    }
    elseif ($tmpccInfo == 'quorum') {
        if ((!isset($tmpccInfo['quorum'])) || (empty($tmpccInfo['quorum']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect quorum paramter. ( number of host or service responses we are expecting )"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            validateForbiddenChars($app, $deployment, '/[0-9]/s', 'quorum', $tmpccInfo['quorum']);
        }
    }
    // You get a pass if you have the use template key defined...
    if ((!isset($tmpccInfo['use'])) || (empty($tmpccInfo['use']))) {
        // Lets make sure we have the minimum required keys for defining a clusteredcommand
        foreach ($required_keys as $rkey) {
            if ((!isset($tmpccInfo[$rkey])) || (empty($tmpccInfo[$rkey]))) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect required parameter $rkey " . cc_rkeyMessage($rkey)
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    $clusteredcommandInfo = cc_validate($app, $deployment, $tmpccInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentClusterCmd($deployment, $clusteredcommandInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentClusterCmd($deployment, $clusteredcommandInfo['name'], $clusteredcommandInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Cluster Command " . $clusteredcommandInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentClusterCmd($deployment, $clusteredcommandInfo['name'], $clusteredcommandInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Cluster Command " . $clusteredcommandInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-clusteredcommand');

$app->delete('/sapi/clusteredcommand/:deployment/:clusteredcommand', function($deployment, $clusteredcommand) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $clusteredcommand)) {
        $clusteredcommands = preg_split('/\s?,\s?/', $clusteredcommand);
        foreach ($clusteredcommands as $stemp) {
            RevDeploy::deleteDeploymentClusterCmd($deployment, $stemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentClusterCmd($deployment, $clusteredcommand, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Cluster Command(s): $clusteredcommand");
    $apiResponse->printJson();
})->name('saigon-api-delete-clusteredcommand');
