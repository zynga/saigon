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
    $request = $app->request();
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

