<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Service Routes
 */

function service_rkeyMessage ($rkey) {
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

function service_validate ($app, $deployment, $serviceInfo) {
    foreach ($serviceInfo as $key => $value) {
        switch ($key) {
            case "use":
            case "check_period":
            case "notification_period":
            case "icon_image":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "contacts":
            case "contact_groups":
            case "servicegroups":
                if (is_array($value)) $value = implode(',', $value);
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
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
                $serviceInfo[$key] = $opts;
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
                $serviceInfo[$key] = $opts;
                break;
            case "notification_interval":
            case "first_notification_delay":
                validateInterval($app, $deployment, $key, $value, 0, 1440); break;
            case "notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('w','u','c','r','f','s'), true);
                $serviceInfo[$key] = $opts;
                break;
            case "stalking_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','u','c'), true);
                $serviceInfo[$key] = $opts;
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
    if ((!isset($serviceInfo['parallelize_check'])) || (empty($serviceInfo['parallelize_check']))) {
        $serviceInfo['parallelize_check'] = 1;
    }
    return $serviceInfo;
}

$app->get('/sapi/services/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
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
    if ($commonMerge == 1) {
        $apiResponse->setExtraResponseData('services',
            RevDeploy::getCommonMergedDeploymentSvcs($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('services',
            RevDeploy::getDeploymentSvcswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-services');

$app->get('/sapi/service/:deployment/:service(/:staged)', function ($deployment, $service, $staged = false) use ($app) {
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
    if (preg_match('/,/', $service)) {
        $services = preg_split('/\s?,\s?/', $service);
        $results = array();
        foreach ($services as $stemp) {
            if (RevDeploy::existsDeploymentSvc($deployment, $stemp, $deployRev) === true) {
                $results[$stemp] = RevDeploy::getDeploymentSvc($deployment, $stemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect services specified: $service");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('services', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentSvc($deployment, $service, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service specified: $service");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $serviceInfo = RevDeploy::getDeploymentSvc($deployment, $service, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('service', $serviceInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service');

$app->post('/sapi/service/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $serviceInfo = $request->getBody();
        $serviceInfo = json_decode($serviceInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','use','service_description','servicegroups','is_volatile','check_command','initial_state',
            'max_check_attempts','check_interval','retry_interval','active_checks_enabled','passive_checks_enabled',
            'check_period', 'obsess_over_service','check_freshness','freshness_threshold','event_handler',
            'event_handler_enabled','low_flap_threshold','high_flap_threshold','flap_detection_enabled',
            'flap_detection_options','process_perf_data','retain_status_information','retain_nonstatus_information',
            'notification_interval','first_notification_delay','notification_period','notification_options',
            'notifications_enabled','contacts','contact_groups','stalking_options','icon_image','icon_image_alt',
            'parallelize_check','notes','notes_url','action_url'
        );
        $serviceInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $serviceInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'max_check_attempts','check_interval', 'retry_interval',
        'check_period','notification_interval','notification_period'
    );
    if (empty($serviceInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any service information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($serviceInfo['name'])) || (empty($serviceInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $serviceInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($serviceInfo['service_description'])) || (empty($serviceInfo['service_description']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect service_description parameter (longer human readable information about service, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $serviceInfo['service_description'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service service_description specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // You get a pass if you have the use template key defined...
    if ((!isset($serviceInfo['use'])) || (empty($serviceInfo['use']))) {
        // Lets make sure we have the minimum required keys for defining a service
        foreach ($required_keys as $rkey) {
            if ((!isset($serviceInfo[$rkey])) || (empty($serviceInfo[$rkey]))) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect required parameter $rkey " . service_rkeyMessage($rkey)
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    $serviceInfo = service_validate($app, $deployment, $serviceInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSvc($deployment, $serviceInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentSvc($deployment, $serviceInfo['name'], $serviceInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Service " . $serviceInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentSvc($deployment, $serviceInfo['name'], $serviceInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Service " . $serviceInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-service');

$app->delete('/sapi/service/:deployment/:service', function($deployment, $service) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $service)) {
        $services = preg_split('/\s?,\s?/', $service);
        foreach ($services as $stemp) {
            RevDeploy::deleteDeploymentSvc($deployment, $stemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentSvc($deployment, $service, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Service(s): $service");
    $apiResponse->printJson();
})->name('saigon-api-delete-service');

