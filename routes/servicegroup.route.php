<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Service Groups Routes
 */

function servicegroup_validate ($app, $deployment, $serviceGroupsInfo) {
    foreach ($serviceGroupsInfo as $key => $value) {
        switch ($key) {
            case "notes":
                validateForbiddenChars($app, $deployment, '/[^\w.-\s]/s', $key, $value); break;
            case "notes_url":
            case "action_url":
                validateUrl($app, $deployment, $key, $value); break;
            default:
                break;
        }
    }
    return $serviceGroupsInfo;
}

$app->get('/sapi/servicegroups/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('service_groups',
            RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('service_groups',
            RevDeploy::getDeploymentSvcGroupswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-groups');

$app->get('/sapi/servicegroup/:deployment/:servicegroup(/:staged)', function ($deployment, $servicegroup, $staged = false) use ($app) {
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
    if (preg_match('/,/', $servicegroup)) {
        $servicegroups = preg_split('/\s?,\s?/', $servicegroup);
        $results = array();
        foreach ($servicegroups as $stemp) {
            if (RevDeploy::existsDeploymentSvcGroup($deployment, $stemp, $deployRev) === true) {
                $results[$stemp] = RevDeploy::getDeploymentSvcGroup($deployment, $stemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service groups specified: $servicegroup");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('service_groups', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentSvcGroup($deployment, $servicegroup, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service groups specified: $servicegroup");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $serviceGroupsInfo = RevDeploy::getDeploymentSvcGroup($deployment, $servicegroup, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('service_group', $serviceGroupsInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-groups');

$app->post('/sapi/servicegroup/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $serviceGroupsInfo = $request->getBody();
        $serviceGroupsInfo = json_decode($serviceGroupsInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array('name','alias','notes','notes_url','action_url');
        $serviceGroupsInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $serviceGroupsInfo[$key] = $value;
            }
        }
    }
    if (empty($serviceGroupsInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any service groups information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($serviceGroupsInfo['name'])) || (empty($serviceGroupsInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $serviceGroupsInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service groups name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($serviceGroupsInfo['alias'])) || (empty($serviceGroupsInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about service, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $serviceGroupsInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service groups alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $serviceGroupsInfo = servicegroup_validate($app, $deployment, $serviceGroupsInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSvcGroup($deployment, $serviceGroupsInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentSvcGroup($deployment, $serviceGroupsInfo['name'], $serviceGroupsInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Service Group " . $serviceGroupsInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentSvcGroup($deployment, $serviceGroupsInfo['name'], $serviceGroupsInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Service Group " . $serviceGroupsInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-service-groups');

$app->delete('/sapi/servicegroup/:deployment/:servicegroup', function($deployment, $servicegroup) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $servicegroup)) {
        $servicegroups = preg_split('/\s?,\s?/', $servicegroup);
        foreach ($servicegroups as $stemp) {
            RevDeploy::deleteDeploymentSvcGroup($deployment, $stemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentSvcGroup($deployment, $servicegroup, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Service Groups: $servicegroup");
    $apiResponse->printJson();
})->name('saigon-api-delete-service-groups');

