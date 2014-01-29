<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Service Dependency Routes
 */
function servicedependency_rkeyMessage ($rkey) {
    switch($rkey) {
        case "service_description":
            return "(service description for the parent service)"; break;
        case "dependent_service_description";
            return "(service description for the child service)"; break;
        default:
            break;
    }
}

function servicedependency_validate ($app, $deployment, $serviceDependencyInfo) {
    foreach ($serviceDependencyInfo as $key => $value) {
        switch ($key) {
            case "inherits_parent":
                validateBinary($app, $deployment, $key, $value); break;
            case "service_description":
            case "dependent_service_description":
            case "dependency_period":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "execution_failure_criteria":
            case "notification_failure_criteria":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','u','c','p','n'), true);
                $serviceDependencyInfo[$key] = $opts;
                break;
            default:
                break;
        }
    }
    return $serviceDependencyInfo;
}

$app->get('/sapi/servicedependencies/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('service_dependencies',
            RevDeploy::getCommonMergedDeploymentSvcDependencies($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('service_dependencies',
            RevDeploy::getDeploymentSvcDependencieswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-dependencies');

$app->get('/sapi/servicedependency/:deployment/:servicedependency(/:staged)', function ($deployment, $servicedependency, $staged = false) use ($app) {
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
    if (preg_match('/,/', $servicedependency)) {
        $servicedependencys = preg_split('/\s?,\s?/', $servicedependency);
        $results = array();
        foreach ($servicedependencys as $stemp) {
            if (RevDeploy::existsDeploymentSvcDependency($deployment, $stemp, $deployRev) === true) {
                $results[$stemp] = RevDeploy::getDeploymentSvcDependency($deployment, $stemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service dependencies specified: $servicedependency");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('service_dependencies', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentSvcDependency($deployment, $servicedependency, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service dependency specified: $servicedependency");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $serviceDependencyInfo = RevDeploy::getDeploymentSvcDependency($deployment, $servicedependency, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('service_dependency', $serviceDependencyInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-dependency');

$app->post('/sapi/servicedependency/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $serviceDependencyInfo = $request->getBody();
        $serviceDependencyInfo = json_decode($serviceDependencyInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','dependent_service_description','service_description','inherits_parent',
            'execution_failure_criteria','notification_failure_criteria','dependency_period'
        );
        $serviceDependencyInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $serviceDependencyInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'service_description','dependent_service_description'
    );
    if (empty($serviceDependencyInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any service dependency information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($serviceDependencyInfo['name'])) || (empty($serviceDependencyInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $serviceDependencyInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service dependency name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // Lets make sure we have the minimum required keys for defining a service dependency
    foreach ($required_keys as $rkey) {
        if ((!isset($serviceDependencyInfo[$rkey])) || (empty($serviceDependencyInfo[$rkey]))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect required parameter $rkey " . servicedependency_rkeyMessage($rkey)
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    $serviceDependencyInfo = servicedependency_validate($app, $deployment, $serviceDependencyInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSvcDependency($deployment, $serviceDependencyInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentSvcDependency($deployment, $serviceDependencyInfo['name'], $serviceDependencyInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Service Dependency " . $serviceDependencyInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentSvcDependency($deployment, $serviceDependencyInfo['name'], $serviceDependencyInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Service Dependency " . $serviceDependencyInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-service-dependency');

$app->delete('/sapi/servicedependency/:deployment/:servicedependency', function($deployment, $servicedependency) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $servicedependency)) {
        $servicedependencys = preg_split('/\s?,\s?/', $servicedependency);
        foreach ($servicedependencys as $stemp) {
            RevDeploy::deleteDeploymentSvcDependency($deployment, $stemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentSvcDependency($deployment, $servicedependency, $deployRev);
    }
    if (preg_match('/,/', $servicedependency)) {
        $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Service Dependencies: $servicedependency");
    }
    else {
        $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Service Dependency: $servicedependency");
    }
    $apiResponse->printJson();
})->name('saigon-api-delete-service-dependency');

