<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Host Groups Routes
 */

$app->get('/sapi/hostgroups/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('host_groups',
            RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('host_groups',
            RevDeploy::getDeploymentHostGroupswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-host-groups');

$app->get('/sapi/hostgroup/:deployment/:hostgroup(/:staged)', function ($deployment, $hostgroup, $staged = false) use ($app) {
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
    if (preg_match('/,/', $hostgroup)) {
        $hostgroups = preg_split('/\s?,\s?/', $hostgroup);
        $results = array();
        foreach ($hostgroups as $hgtemp) {
            if (RevDeploy::existsDeploymentHostGroup($deployment, $hgtemp, $deployRev) === true) {
                $results[$hgtemp] = RevDeploy::getDeploymentHostGroup($deployment, $hgtemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect host groups specified: $hostgroup");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('host_groups', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentHostGroup($deployment, $hostgroup, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect host groups specified: $hostgroup");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $hostGroupsInfo = RevDeploy::getDeploymentHostGroup($deployment, $hostgroup, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('host_group', $hostGroupsInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-host-groups');

$app->post('/sapi/hostgroup/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $hostGroupsInfo = $request->getBody();
        $hostGroupsInfo = json_decode($hostGroupsInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array('name','alias');
        $hostGroupsInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $hostGroupsInfo[$key] = $value;
            }
        }
    }
    if (empty($hostGroupsInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any host groups information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($hostGroupsInfo['name'])) || (empty($hostGroupsInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $hostGroupsInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use host groups name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($hostGroupsInfo['alias'])) || (empty($hostGroupsInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about host, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $hostGroupsInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use host groups alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentHostGroup($deployment, $hostGroupsInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentHostGroup($deployment, $hostGroupsInfo['name'], $hostGroupsInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Host Group " . $hostGroupsInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentHostGroup($deployment, $hostGroupsInfo['name'], $hostGroupsInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Host Group " . $hostGroupsInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-host-groups');

$app->delete('/sapi/hostgroup/:deployment/:hostgroup', function($deployment, $hostgroup) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $hostgroup)) {
        $hostgroups = preg_split('/\s?,\s?/', $hostgroup);
        foreach ($hostgroups as $hgtemp) {
            RevDeploy::deleteDeploymentHostGroup($deployment, $hgtemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentHostGroup($deployment, $hostgroup, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Host Groups: $hostgroup");
    $apiResponse->printJson();
})->name('saigon-api-delete-host-groups');

