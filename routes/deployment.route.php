<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Deployment Management Routes
 */

$app->get('/sapi/deployment/:deployment/revisions', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $apiResponse = new APIViewData(0, $deployment, false);
    $revs = RevDeploy::getDeploymentRevs($deployment);
    $apiResponse->setExtraResponseData('current_revision', $revs['currrev']);
    $apiResponse->setExtraResponseData('next_revision', $revs['nextrev']);
    if ($revs['prevrev'] !== false) {
        $apiResponse->setExtraResponseData('previous_revision', $revs['prevrev']);
    }
    $apiResponse->setExtraResponseData('revs', RevDeploy::getDeploymentAllRevs($deployment));
    $apiResponse->printJson();
})->name('saigon-api-get-revisions');

$app->post('/sapi/deployment/:deployment/revisions', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $revisionInfo = $request->getBody();
        $revisionInfo = json_decode($revisionInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $revisionInfo['revision'] = $request->post('revision');
        $revisionInfo['note'] = $request->post('note');
    }
    if ((!isset($revisionInfo['revision'])) || (empty($revisionInfo['revision']))) {
        $apiResponse = new APIViewData(
            1,
            $deployment,
            "Unable to detect revision parameter (revision number we should activate)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($revisionInfo['note'])) || (empty($revisionInfo['note']))) {
        $apiResponse = new APIViewData(
            1,
            $deployment,
            "Unable to detect note parameter (note about updates or changes)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $currentRev = RevDeploy::getDeploymentRev($deployment);
    RevDeploy::setDeploymentRevs($deployment, $currentRev, $revisionInfo['revision'], $revisionInfo['note']);
    VarnishCache::invalidate($deployment);
    Chat::messageByDeployment($deployment, "Activated Revision Change Detected. Deployment: $deployment / From Revision: $fromRev / To Revision: $toRev / Change Note: $note", 'green');
    NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
    NagPhean::addJob(
        BEANSTALKD_TUBE,
        json_encode(
            array(
                'deployment' => $deployment, 'type' => 'verify',
                'revision' => $revisionInfo['revision']
            )
        ),
        2048, 5, 900
    );
    $apiResponse = new APIViewData(
        0,
        $deployment,
        "Successfully Activated Revision [ " . $revisionInfo['revision'] . " ] Replacing Revision [ " . $currentRev . " ]"
    );
    $apiResponse->printJson();
})->name('saigon-api-change-revisions');

$app->delete('/sapi/deployment/:deployment/revisions/:revisions', function ($deployment, $revisions) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    if (preg_match('/,/', $revisions)) {
        $revisions = preg_split('/\s?,\s?/', $revisions);
    }
    $protected_revisions = array_values(RevDeploy::getDeploymentRevs($deployment));
    // Now lets see if our revision(s) are protected (prev,current,staged)
    if (is_array($revisions)) {
        foreach ($revisions as $revision) {
            if (in_array($revision, $protected_revisions)) {
                $apiResponse = new APIViewData(
                    1,
                    $deployment,
                    "Unable to delete the following revision as it is protected: [ $revision ]"
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    else {
        if (in_array($revisions, $protected_revisions)) {
            $apiResponse = new APIViewData(
                1,
                $deployment,
                "Unable to delete the following revision as it is protected: [ $revisions ]"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    RevDeploy::deleteDeploymentRev($deployment, $revisions);
    if (is_array($revisions)) {
        $msg = "Successfully Removed Revisions [ " . implode(",", $revisions) . " ]";
    }
    else {
        $msg = "Successfully Removed Revision [ $revisions ]";
    }
    $apiResponse = new APIViewData(0, $deployment, $msg);
    $apiResponse->printJson();
})->name('saigon-api-delete-revisions');

$app->map('/sapi/deployment/:deployment/resetstagedrevision', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $currRev = RevDeploy::getDeploymentRev($deployment);
    $nextRev = RevDeploy::getDeploymentNextRev($deployment);
    if ($currRev == $nextRev) {
        $apiResponse = new APIViewData(
            1,
            $deployment,
            "Unable to process request, no staged revision exists to reset"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    CopyDeploy::resetDeploymentRevision($deployment, $currRev, $nextRev);
    $apiResponse = new APIViewData(
        0,
        $deployment,
        "Successfully Reset Staged Revision [ $nextRev ] From The Current Revision [ $currRev ]"
    );
    $apiResponse->printJson();
})->via('GET', 'POST')->name('saigon-api-reset-revisions');

$app->map('/sapi/deployment/:deployment/revisionlog', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $revisionInfo = RevDeploy::getAuditLog($deployment);
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('log', $revisionInfo);
    $apiResponse->printJson();
})->via('GET', 'POST')->name('saigon-api-revisionlog');

$app->get('/sapi/deployment/:deployment/hosttypes', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $locations = HostInputs::fetchLocations();
    $inputs = HostInputs::fetchInputs();
    $merged = array_merge($locations, $inputs);
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('dynamic', $merged);
    $apiResponse->setExtraResponseData('static', array('host' => 'ip'));
    $apiResponse->printJson();
})->name('saigon-api-deployment-hosts-input-types');

$app->get('/sapi/deployment/:deployment/hosts', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $apiResponse = new APIViewData(0, $deployment, false);
    $dynamicSearches = RevDeploy::getDeploymentHostSearches($deployment);
    $dynamicResults = array();
    foreach ($dynamicSearches as $key => $tmpArray) {
        $dynamicResults[] = $tmpArray;
    }
    $staticSearches = RevDeploy::getDeploymentStaticHosts($deployment);
    $staticResults = array();
    foreach ($staticSearches as $key => $tmpArray) {
        $staticResults[] = $tmpArray;
    }
    $apiResponse->setExtraResponseData('dynamic', $dynamicResults);
    $apiResponse->setExtraResponseData('static', $staticResults);
    $apiResponse->printJson();
})->name('saigon-api-deployment-hosts');

$app->post('/sapi/deployment/:deployment/host/:type', function ($deployment, $type) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $hostInfo = json_decode($request->getBody(),true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        if ($type == 'static') {
            $hostInfo['host'] = $request->params('host');
            $hostInfo['ip'] = $request->params('ip');
        }
        else {
            $hostInfo['location'] = $request->params('location');
            $hostInfo['srchparam'] = $request->params('srchparam');
            $hostInfo['note'] = $request->params('note');
        }
    }
    if ($type == 'dynamic') {
        if ((!isset($hostInfo['location'])) || (empty($hostInfo['location']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect location parameter for module reference"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif ((!isset($hostInfo['srchparam'])) || (empty($hostInfo['srchparam']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect srchparam paramter for module search usage"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        $md5 = md5($hostInfo['location'].':'.$hostInfo['srchparam']);
        RevDeploy::addDeploymentDynamicHost($deployment, $md5, $hostInfo);
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('received', $hostInfo);
        $apiResponse->printJson();
    }
    elseif ($type == 'static') {
        if ((!isset($hostInfo['host'])) || (empty($hostInfo['host']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect host parameter (expecting hostname or fqdn)"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif ((!isset($hostInfo['ip'])) || (empty($hostInfo['ip']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect ip parameter (expecting to match /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/)"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $hostInfo['ip'])) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to use ip parameter (expecting to match /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/)"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        RevDeploy::addDeploymentStaticHost($deployment, NagMisc::encodeIP($hostInfo['ip']), $hostInfo);
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('received', $hostInfo);
        $apiResponse->printJson();
    }
    else {
        $apiResponse = new APIViewData(1, $deployment,
            "Unsure of how you got here, as a higher route should have blocked you, check your host type for processing"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
})->name('saigon-api-deployment-add-host')->conditions(array('type' => '(dynamic|static)'));

$app->delete('/sapi/deployment/:deployment/host/:type', function ($deployment, $type) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    $hostInfo = array();
    if ($contentType == 'application/json') {
        $hostInfo = $request->getBody();
        $hostInfo = json_decode($hostInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        if ($type == 'static') {
            $hostInfo['host'] = $request->params('host');
            $hostInfo['ip'] = $request->params('ip');
        }
        else {
            $hostInfo['location'] = $request->params('location');
            $hostInfo['srchparam'] = $request->params('srchparam');
            $hostInfo['note'] = $request->params('note');
        }
    }
    if ($type == 'dynamic') {
        if ((!isset($hostInfo['location'])) || (empty($hostInfo['location']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect location parameter for module reference"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif ((!isset($hostInfo['srchparam'])) || (empty($hostInfo['srchparam']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect srchparam paramter for module search usage"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        $md5 = md5($hostInfo['location'].':'.$hostInfo['srchparam']);
        $results = RevDeploy::delDeploymentDynamicHost($deployment, $md5, $hostInfo);
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('deleted', $results);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect dynamic host information specified in the data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    elseif ($type == 'static') {
        if ((!isset($hostInfo['host'])) || (empty($hostInfo['host']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect host parameter (expecting hostname or fqdn)"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif ((!isset($hostInfo['ip'])) || (empty($hostInfo['ip']))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect ip parameter (expecting to match /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/)"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        elseif (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $hostInfo['ip'])) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to use ip parameter (expecting to match /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/)"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        $results = RevDeploy::delDeploymentStaticHost($deployment, NagMisc::encodeIP($hostInfo['ip']));
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('deleted', $results);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect static host information specified in the data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $apiResponse = new APIViewData(1, $deployment,
            "Unsure of how you got here, as a higher route should have blocked you, check your host type for processing"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
})->name('saigon-api-deployment-delete-host')->conditions(array('type' => '(dynamic|static)'));

