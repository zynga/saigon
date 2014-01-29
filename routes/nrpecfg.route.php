<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - NRPE Config Routes
 */

$app->get('/sapi/nrpecfg/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
    if (RevDeploy::existsDeploymentNRPECfg($deployment, $deployRev) !== false) {
        $nrpeCfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $deployRev);
        $fileContents = NRPECreate::buildNRPEFile($deployment, $deployRev, $nrpeCfgInfo);
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('md5', md5($fileContents));
        $apiResponse->setExtraResponseData('location', $nrpeCfgInfo['location']);
        $apiResponse->setExtraResponseData('file', base64_encode($fileContents));
        $apiResponse->printJson();
    }
    else {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect nrpe config file");
        $app->halt(404, $apiResponse->returnJson());
    }
})->name('saigon-api-get-nrpe-cfg');

$app->delete('/sapi/nrpecfg/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    RevDeploy::deleteDeploymentNRPECfg($deployment, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed NRPE Config");
    $apiResponse->printJson();
})->name('saigon-api-delete-nrpe-cfg');

$app->post('/sapi/nrpecfg/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $nrpeCfgInfo = $request->getBody();
        $nrpeCfgInfo = json_decode($nrpeCfgInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $nrpeCfgInfo['location'] = $request->post('location');
        $nrpeCfgInfo['pid_file'] = $request->post('pid_file');
        $nrpeCfgInfo['server_port'] = $request->post('server_port');
        $nrpeCfgInfo['nrpe_user'] = $request->post('nrpe_user');
        $nrpeCfgInfo['nrpe_group'] = $request->post('nrpe_group');
        $nrpeCfgInfo['dont_blame_nrpe'] = $request->post('dont_blame_nrpe');
        $nrpeCfgInfo['debug'] = $request->post('debug');
        $nrpeCfgInfo['command_timeout'] = $request->post('command_timeout');
        $nrpeCfgInfo['connection_timeout'] = $request->post('connection_timeout');
        $nrpeCfgInfo['allowed_hosts'] = $request->post('allowed_hosts');
        $nrpeCfgInfo['include_dir'] = $request->post('include_dir');
        $nrpeCfgInfo['cmds'] = $request->post('cmds');
        // These two options can be "false" which is really just 0
        if ($nrpeCfgInfo['dont_blame_nrpe'] === false) $nrpeCfgInfo['dont_blame_nrpe'] = '0';
        if ($nrpeCfgInfo['debug'] === false) $nrpeCfgInfo['debug'] = '0';
    }
    // A bit of param validation
    if ((!isset($nrpeCfgInfo['location'])) || (empty($nrpeCfgInfo['location']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect location parameter (unix path including filename)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['pid_file'])) || (empty($nrpeCfgInfo['pid_file']))) {
        $apiResponse = new APIViewData(1, $deployment, 'Unable to detect pid_file parameter (unix path including filename)');
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['server_port'])) || (empty($nrpeCfgInfo['server_port']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect server_port parameter (service listening port)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (($nrpeCfgInfo['server_port'] < 1024) || ($nrpeCfgInfo['server_port'] > 65536)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use server_port parameter specified (port is either protected or nonexistent"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['nrpe_user'])) || (empty($nrpeCfgInfo['nrpe_user']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect nrpe_user parameter (user to run nrpe service as)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['nrpe_group'])) || (empty($nrpeCfgInfo['nrpe_group']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect nrpe_group parameter (group to run nrpe service as)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['command_timeout'])) || (empty($nrpeCfgInfo['command_timeout']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect command_timeout parameter (max time to wait for command to finish)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (($nrpeCfgInfo['command_timeout'] < 10) || ($nrpeCfgInfo['command_timeout'] > 300)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use command_timeout value specified (must be between 10 and 300)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['connection_timeout'])) || (empty($nrpeCfgInfo['connection_timeout']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect connection_timeout parameter (max time to wait for connection to be established before exiting)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (($nrpeCfgInfo['connection_timeout'] < 60) || ($nrpeCfgInfo['connection_timeout'] > 600)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use command_timeout value specified (must be between 60 and 600)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['allowed_hosts'])) || (empty($nrpeCfgInfo['allowed_hosts']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect allowed_hosts parameter (hosts allowed to connect to service)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCfgInfo['cmds'])) || (empty($nrpeCfgInfo['cmds']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect cmds parameter (commands to activate in nrpe config)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // Param manipulation depending on what is detected
    if (is_array($nrpeCfgInfo['cmds'])) {
        $nrpeCfgInfo['cmds'] = implode(',', $nrpeCfgInfo['cmds']);
    }
    if ((!isset($nrpeCfgInfo['include_dir'])) || (empty($nrpeCfgInfo['include_dir']))) {
        unset($nrpeCfgInfo['include_dir']);
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNRPECfg($deployment, $deployRev) === true) {
        RevDeploy::modifyDeploymentNRPECfg($deployment, $nrpeCfgInfo, $deployRev);
        $msg = "Successfully Modified NRPE Config";
    }
    else {
        RevDeploy::createDeploymentNRPECfg($deployment, $nrpeCfgInfo, $deployRev);
        $msg = "Successfully Created NRPE Config";
    }
    unset($nrpeCfgInfo);
    $nrpeCfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $deployRev);
    $fileContents = NRPECreate::buildNRPEFile($deployment, $deployRev, $nrpeCfgInfo);
    $apiResponse = new APIViewData(0, $deployment, $msg);
    $apiResponse->setExtraResponseData('md5', md5($fileContents));
    $apiResponse->printJson();
})->name('saigon-api-create-nrpe-cfg');

