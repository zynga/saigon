<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Command Routes
 */

$app->get('/sapi/commands/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
    // Load up Current Commands or Staged Commands
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
        $apiResponse->setExtraResponseData('commands',
            RevDeploy::getCommonMergedDeploymentCommands($deployment, $deployRev, false)
        );
    }
    else {
        $apiResponse->setExtraResponseData('commands',
            RevDeploy::getDeploymentCommandswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-commands');

$app->post('/sapi/command/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $commandInfo = $request->getBody();
        $commandInfo = json_decode($commandInfo,true);
        if ((isset($commandInfo['command_line'])) && (!empty($commandInfo['command_line']))) {
            if (($b64dec = base64_decode($commandInfo['command_line'], true)) === false) {
                $commandInfo['command_line'] = base64_encode($commandInfo['command_line']);
            }
        }
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $commandInfo['command_name'] = $request->post('command_name');
        $commandInfo['command_desc'] = $request->post('command_desc');
        $commandInfo['command_line'] = $request->post('command_line');
        if ((isset($commandInfo['command_line'])) && (!empty($commandInfo['command_line']))) {
            if (($b64dec = base64_decode($commandInfo['command_line'], true)) === false) {
                $commandInfo['command_line'] = base64_encode($commandInfo['command_line']);
            }
        }
    }
    // A bit of param validation...
    if ((!isset($commandInfo['command_name'])) || (empty($commandInfo['command_name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect command_name parameter (command name)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $commandInfo['command_name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use command name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($commandInfo['command_desc'])) || (empty($commandInfo['command_desc']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect command_desc parameter (command description)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($commandInfo['command_line'])) || (empty($commandInfo['command_line']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect command_line parameter (command line for executing plugin)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentCommand($deployment, $commandInfo['command_name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentCommand($deployment, $commandInfo['command_name'], $commandInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, "Successfully Modified Command: " . $commandInfo['command_name']);
    }
    else {
        RevDeploy::createDeploymentCommand($deployment, $commandInfo['command_name'], $commandInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, "Successfully Created Command: " . $commandInfo['command_name']);
    }
    $apiResponse->printJson();
})->name('saigon-api-create-command');

$app->get('/sapi/command/:deployment/:command(/:staged)', function ($deployment, $command, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
    // Load up Current Commands or Staged Commands
    if ($staged == 1) {
        $deployRev = RevDeploy::getDeploymentNextRev($deployment);
        if ($deployRev === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect staged revision to reference");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $deployRev = RevDeploy::getDeploymentRev($deployment);
    }
    if (preg_match('/,/', $command)) {
        $results = array();
        $commands = preg_split('/\s?,\s?/', $command);
        foreach ($commands as $cmd) {
            if ($commonMerge == 1) {
                $commandData = RevDeploy::getCommonMergedDeploymentCommand($deployment, $cmd, $deployRev);
            }
            else {
                $commandData = RevDeploy::getDeploymentCommand($deployment, $cmd, $deployRev);
            }
            if ($commandData !== false) {
                $results[$cmd] = $commandData;
            }
        }
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('commands', $results);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect command(s) specified: $command");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        if ($commonMerge == 1) {
            $commandData = RevDeploy::getCommonMergedDeploymentCommand($deployment, $command, $deployRev);
        }
        else {
            $commandData = RevDeploy::getDeploymentCommand($deployment, $command, $deployRev);
        }
        if ($commandData !== false) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('command', $commandData);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect command specified: $command");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
})->name('saigon-api-get-command');

$app->delete('/sapi/command/:deployment/:command', function ($deployment, $command) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $command)) {
        $results = array();
        $cmds = preg_split('/\s?,\s?/', $command);
        foreach ($cmds as $cmd) {
            RevDeploy::deleteDeploymentCommand($deployment, $cmd, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentCommand($deployment, $command, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Command(s): $command");
    $apiResponse->printJson();
})->name('saigon-api-delete-command');

