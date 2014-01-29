<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Service Escalation Routes
 */
function serviceescalation_rkeyMessage ($rkey) {
    switch($rkey) {
        case "service_description":
            return "(service description for the parent service)"; break;
        case "first_notification";
            return "(the first number of the notifications this will activate on)"; break;
        case "last_notification":
            return "(the last number of the notifications this will deactivate on)"; break;
        case "notification_interval";
            return "(time interval between sending alerts in regards to this escalation)"; break;
        default:
            break;
    }
}

function serviceescalation_validate ($app, $deployment, $serviceEscalationInfo) {
    foreach ($serviceEscalationInfo as $key => $value) {
        switch ($key) {
            case "service_description":
            case "escalation_period":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "escalation_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('w','u','c','r'), true);
                $serviceEscalationInfo[$key] = $opts;
                break;
            case "first_notification":
                validateInterval($app, $deployment, $key, $value, 0, 25); break;
            case "last_notification":
                validateInterval($app, $deployment, $key, $value, 0, 100); break;
            case "notification_interval":
                validateInterval($app, $deployment, $key, $value, 0, 1440); break;
            default:
                break;
        }
    }
    return $serviceEscalationInfo;
}

$app->get('/sapi/serviceescalations/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('service_escalations', RevDeploy::getDeploymentSvcEscalationswInfo($deployment, $deployRev));
    $apiResponse->printJson();
})->name('saigon-api-get-service-escalations');

$app->get('/sapi/serviceescalation/:deployment/:serviceescalation(/:staged)', function ($deployment, $serviceescalation, $staged = false) use ($app) {
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
    if (preg_match('/,/', $serviceescalation)) {
        $serviceescalations = preg_split('/\s?,\s?/', $serviceescalation);
        $results = array();
        foreach ($serviceescalations as $stemp) {
            if (RevDeploy::existsDeploymentSvcEscalation($deployment, $stemp, $deployRev) === true) {
                $results[$stemp] = RevDeploy::getDeploymentSvcEscalation($deployment, $stemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service escalations specified: $serviceescalation");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('service_escalations', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentSvcEscalation($deployment, $serviceescalation, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service escalation specified: $serviceescalation");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $serviceEscalationInfo = RevDeploy::getDeploymentSvcEscalation($deployment, $serviceescalation, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('service_escalation', $serviceEscalationInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-escalation');

$app->post('/sapi/serviceescalation/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $serviceEscalationInfo = $request->getBody();
        $serviceEscalationInfo = json_decode($serviceEscalationInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','service_description','first_notification','last_notification',
            'notification_interval','escalation_period','escalation_options',
            'contacts','contact_groups'
        );
        $serviceEscalationInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $serviceEscalationInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'service_description','first_notification','last_notification','notification_interval'
    );
    if (empty($serviceEscalationInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any service escalation information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($serviceEscalationInfo['name'])) || (empty($serviceEscalationInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $serviceEscalationInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service escalation name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    validateContacts($app, $deployment, $serviceEscalationInfo);
    // Lets make sure we have the minimum required keys for defining a service escalation
    foreach ($required_keys as $rkey) {
        if ((!isset($serviceEscalationInfo[$rkey])) || (empty($serviceEscalationInfo[$rkey]))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect required parameter $rkey " . serviceescalation_rkeyMessage($rkey)
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    $serviceEscalationInfo = serviceescalation_validate($app, $deployment, $serviceEscalationInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSvcEscalation($deployment, $serviceEscalationInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentSvcEscalation($deployment, $serviceEscalationInfo['name'], $serviceEscalationInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Service Escalation " . $serviceEscalationInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentSvcEscalation($deployment, $serviceEscalationInfo['name'], $serviceEscalationInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Service Escalation " . $serviceEscalationInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-service-escalation');

$app->delete('/sapi/serviceescalation/:deployment/:serviceescalation', function($deployment, $serviceescalation) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $serviceescalation)) {
        $serviceescalations = preg_split('/\s?,\s?/', $serviceescalation);
        foreach ($serviceescalations as $stemp) {
            RevDeploy::deleteDeploymentSvcEscalation($deployment, $stemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentSvcEscalation($deployment, $serviceescalation, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Service Escalation(s): $serviceescalation");
    $apiResponse->printJson();
})->name('saigon-api-delete-service-escalation');

