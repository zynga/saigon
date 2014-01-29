<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Supplemental NRPE Config Routes
 */

$app->get('/sapi/supnrpecfg/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    // Load up Current Config or Staged Config
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
    if (RevDeploy::existsDeploymentSupNRPECfg($deployment, $deployRev) !== false) {
        $supNRPECfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $deployRev);
        $fileContents = NRPECreate::buildSupNRPEFile($deployment, $deployRev, $supNRPECfgInfo);
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('md5', md5($fileContents));
        $apiResponse->setExtraResponseData('location', $supNRPECfgInfo['location']);
        $apiResponse->setExtraResponseData('file', base64_encode($fileContents));
        $apiResponse->printJson();
    }
    else {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect supplemental nrpe config file");
        $app->halt(404, $apiResponse->returnJson());
    }
})->name('saigon-api-get-sup-nrpe-cfg');

$app->delete('/sapi/supnrpecfg/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    RevDeploy::deleteDeploymentSupNRPECfg($deployment, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Supplemental NRPE Config");
    $apiResponse->printJson();
})->name('saigon-api-delete-sup-nrpe-cfg');

$app->post('/sapi/supnrpecfg/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $supNRPECfgInfo = $request->getBody();
        $supNRPECfgInfo = json_decode($supNRPECfgInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $supNRPECfgInfo['location'] = $request->post('location');
        $supNRPECfgInfo['cmds'] = $request->post('cmds');
    }
    // A bit of param validation
    if ((!isset($supNRPECfgInfo['location'])) || (empty($supNRPECfgInfo['location']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect location parameter (unix path including filename)");
        $app->halt(404, $apiResponse->returnJson());
    }
    // Param manipulation depending on what is detected
    if (is_array($supNRPECfgInfo['cmds'])) {
        $supNRPECfgInfo['cmds'] = implode(',', $supNRPECfgInfo['cmds']);
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSupNRPECfg($deployment, $deployRev) === true) {
        RevDeploy::modifyDeploymentSupNRPECfg($deployment, $supNRPECfgInfo, $deployRev);
        $msg = "Successfully Modified NRPE Config";
    }
    else {
        RevDeploy::createDeploymentSupNRPECfg($deployment, $supNRPECfgInfo, $deployRev);
        $msg = "Successfully Created NRPE Config";
    }
    $supNRPECfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $deployRev);
    $fileContents = NRPECreate::buildSupNRPEFile($deployment, $deployRev, $supNRPECfgInfo);
    $apiResponse = new APIViewData(0, $deployment, $msg);
    $apiResponse->setExtraResponseData('md5', md5($fileContents));
    $apiResponse->printJson();
})->name('saigon-api-create-sup-nrpe-cfg');

