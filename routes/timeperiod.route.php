<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Timeperiod Routes
 */

function timeperiodBuildTimes($app, $deployment, array $timeperiodInfo, $use_enabled = false) {
    if ($use_enabled === false) {
        if ((!isset($timeperiodInfo['timeperiods'])) || (empty($timeperiodInfo['timeperiods']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect timeperiods parameter (expected array of hashes [{directive=>value,range=>value},..] )"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif (!is_array($timeperiodInfo['timeperiods'])) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to use timeperiods parameter (expected array of hashes [{directive=>value,range=>value},..] )"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        if ((!isset($timeperiodInfo['timeperiods'])) || (empty($timeperiodInfo['timeperiods']))) {
            return array();
        }
        elseif (!is_array($timeperiodInfo['timeperiods'])) {
            // Not ideal, but I'll think of some way to deal with this...
            return array();
        }
    }
    $results = array();
    foreach ($timeperiodInfo['timeperiods'] as $key => $dArray) {
        if ((!isset($dArray['directive'])) || (empty($dArray['directive']))) {
            continue;
        }
        elseif ((!isset($dArray['range'])) || (empty($dArray['range']))) {
            continue;
        }
        $results[md5($dArray['directive'])] = $dArray;
    }
    return $results;
}

$app->get('/sapi/timeperiodsmeta/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
    // Load up Current Timeperiods or Staged Timeperiods
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
        $apiResponse->setExtraResponseData('timeperiods',
            RevDeploy::getCommonMergedDeploymentTimeperiods($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('timeperiods',
            RevDeploy::getDeploymentTimeperiodswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-timeperiods');

$app->get('/sapi/timeperiods/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
    // Load up Current Timeperiods or Staged Timeperiods
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
        $apiResponse->setExtraResponseData('timeperiods',
            RevDeploy::getCommonMergedDeploymentTimeperiodswData($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('timeperiods',
            RevDeploy::getDeploymentTimeperiodswData($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-timeperiods');

$app->post('/sapi/timeperiod/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $timeperiodInfo = $request->getBody();
        $timeperiodInfo = json_decode($timeperiodInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $timeperiodInfo['timeperiod_name'] = $request->post('timeperiod_name');
        $timeperiodInfo['alias'] = $request->post('alias');
        $timeperiodInfo['use'] = $request->post('use');
        $timeperiodInfo['timeperiods'] = $request->post('timeperiods');
    }
    // A bit of param validation...
    if ((!isset($timeperiodInfo['timeperiod_name'])) || (empty($timeperiodInfo['timeperiod_name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect timeperiod_name parameter (timeperiod name)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $timeperiodInfo['timeperiod_name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use timeperiod name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($timeperiodInfo['alias'])) || (empty($timeperiodInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect alias parameter (timeperiod description)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $timeperiodInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use timeperiod alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($timeperiodInfo['use'])) || (empty($timeperiodInfo['use']))) {
        // use flag is undetected, need to exit if no timeperiod directives / times were provided
        $timeperiodData = timeperiodBuildTimes($app, $deployment, $timeperiodInfo);
    }
    else {
        // detected use flag, don't exit out if nothing is detected.
        $timeperiodData = timeperiodBuildTimes($app, $deployment, $timeperiodInfo, true);
    }
    unset($timeperiodInfo['timeperiods']);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentTimeperiod($deployment, $timeperiodInfo['timeperiod_name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentTimeperiod($deployment, $timeperiodInfo['timeperiod_name'], $timeperiodInfo, $timeperiodData, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, "Successfully Modified Timeperiod: " . $timeperiodInfo['timeperiod_name']);
    }
    else {
        RevDeploy::createDeploymentTimeperiod($deployment, $timeperiodInfo['timeperiod_name'], $timeperiodInfo, $timeperiodData, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, "Successfully Created Timeperiod: " . $timeperiodInfo['timeperiod_name']);
    }
    $apiResponse->printJson();
})->name('saigon-api-create-timeperiod');

$app->get('/sapi/timeperiod/:deployment/:timeperiod(/:staged)', function ($deployment, $timeperiod, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    // Load up Current Timeperiods or Staged Timeperiods
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
    if (preg_match('/,/', $timeperiod)) {
        $results = array();
        $timeperiods = preg_split('/\s?,\s?/', $timeperiod);
        foreach ($timeperiods as $tmptp) {
            $timeperiodData = RevDeploy::getDeploymentTimeperiod($deployment, $tmptp, $deployRev);
            if ($timeperiodData !== false) {
                $results[$tmptp] = $timeperiodData;
            }
        }
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('timeperiods', $results);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect timeperiod(s) specified: $timeperiod");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $timeperiodData = RevDeploy::getDeploymentTimeperiod($deployment, $timeperiod, $deployRev);
        if ($timeperiodData !== false) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('timeperiod', $timeperiodData);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect timeperiod specified: $timeperiod");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
})->name('saigon-api-get-timeperiod');

$app->delete('/sapi/timeperiod/:deployment/:timeperiod', function ($deployment, $timeperiod) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $timeperiod)) {
        $results = array();
        $tmptps = preg_split('/\s?,\s?/', $timeperiod);
        foreach ($tmptps as $tmptp) {
            RevDeploy::deleteDeploymentTimeperiod($deployment, $tmptp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentTimeperiod($deployment, $timeperiod, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Timeperiod(s): $timeperiod");
    $apiResponse->printJson();
})->name('saigon-api-delete-timeperiod');

