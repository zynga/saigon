<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Contact Groups Routes
 */

$app->get('/sapi/contactgroups/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('contact_groups',
            RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('contact_groups',
            RevDeploy::getDeploymentContactGroupswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-contact-groups');

$app->get('/sapi/contactgroup/:deployment/:contactgroup(/:staged)', function ($deployment, $contactgroup, $staged = false) use ($app) {
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
    if (preg_match('/,/', $contactgroup)) {
        $contactgroups = preg_split('/\s?,\s?/', $contactgroup);
        $results = array();
        foreach ($contactgroups as $cgtemp) {
            if (RevDeploy::existsDeploymentContactGroup($deployment, $cgtemp, $deployRev) === true) {
                $results[$cgtemp] = RevDeploy::getDeploymentContactGroup($deployment, $cgtemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect contact groups specified: $contactgroup");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('contact_groups', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentContactGroup($deployment, $contactgroup, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect contact groups specified: $contactgroup");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $contactGroupsInfo = RevDeploy::getDeploymentContactGroup($deployment, $contactgroup, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('contact_group', $contactGroupsInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-contact-groups');

$app->post('/sapi/contactgroup/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $contactGroupsInfo = $request->getBody();
        $contactGroupsInfo = json_decode($contactGroupsInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array('name','alias');
        $contactGroupsInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $contactGroupsInfo[$key] = $value;
            }
        }
    }
    if (empty($contactGroupsInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any contact groups information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($contactGroupsInfo['name'])) || (empty($contactGroupsInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $contactGroupsInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use contact groups name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($contactGroupsInfo['alias'])) || (empty($contactGroupsInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about contact, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $contactGroupsInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use contact groups alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentContactGroup($deployment, $contactGroupsInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentContactGroup($deployment, $contactGroupsInfo['name'], $contactGroupsInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Contact Group " . $contactGroupsInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentContactGroup($deployment, $contactGroupsInfo['name'], $contactGroupsInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Contact Group " . $contactGroupsInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-contact-groups');

$app->delete('/sapi/contactgroup/:deployment/:contactgroup', function($deployment, $contactgroup) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $contactgroup)) {
        $contactgroups = preg_split('/\s?,\s?/', $contactgroup);
        foreach ($contactgroups as $cgtemp) {
            RevDeploy::deleteDeploymentContactGroup($deployment, $cgtemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentContactGroup($deployment, $contactgroup, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Contact Groups: $contactgroup");
    $apiResponse->printJson();
})->name('saigon-api-delete-contact-groups');

