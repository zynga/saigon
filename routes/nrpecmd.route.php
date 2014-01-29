<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - NRPE Command Routes
 */

$app->get('/sapi/nrpecmds/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
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
            RevDeploy::getCommonMergedDeploymentNRPECmds($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('commands',
            RevDeploy::getDeploymentNRPECmdswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-nrpe-cmds');

$app->post('/sapi/nrpecmd/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $nrpeCmdInfo = $request->getBody();
        $nrpeCmdInfo = json_decode($nrpeCmdInfo,true);
        if ((isset($nrpeCmdInfo['cmd_line'])) && (!empty($nrpeCmdInfo['cmd_line']))) {
            if (($b64dec = base64_decode($nrpeCmdInfo['cmd_line'], true)) === false) {
                $nrpeCmdInfo['cmd_line'] = base64_encode($nrpeCmdInfo['cmd_line']);
            }
        }
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $nrpeCmdInfo['cmd_name'] = $request->post('cmd_name');
        $nrpeCmdInfo['cmd_desc'] = $request->post('cmd_desc');
        $nrpeCmdInfo['cmd_line'] = $request->post('cmd_line');
        if ((isset($nrpeCmdInfo['cmd_line'])) && (!empty($nrpeCmdInfo['cmd_line']))) {
            if (($b64dec = base64_decode($nrpeCmdInfo['cmd_line'], true)) === false) {
                $nrpeCmdInfo['cmd_line'] = base64_encode($nrpeCmdInfo['cmd_line']);
            }
        }
    }
    // A bit of param validation...
    if ((!isset($nrpeCmdInfo['cmd_name'])) || (empty($nrpeCmdInfo['cmd_name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect cmd_name parameter (command name)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $nrpeCmdInfo['cmd_name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use command name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCmdInfo['cmd_desc'])) || (empty($nrpeCmdInfo['cmd_desc']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect cmd_desc parameter (command description)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($nrpeCmdInfo['cmd_line'])) || (empty($nrpeCmdInfo['cmd_line']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect cmd_line parameter (command line for executing plugin)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNRPECmd($deployment, $nrpeCmdInfo['cmd_name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentNRPECmd($deployment, $nrpeCmdInfo['cmd_name'], $nrpeCmdInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, "Successfully Modified NRPE Command: " . $nrpeCmdInfo['cmd_name']);
    }
    else {
        RevDeploy::createDeploymentNRPECmd($deployment, $nrpeCmdInfo['cmd_name'], $nrpeCmdInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, "Successfully Created NRPE Command: " . $nrpeCmdInfo['cmd_name']);
    }
    $apiResponse->printJson();
})->name('saigon-api-create-nrpe-cmd');

$app->get('/sapi/nrpecmd/:deployment/:nrpecmd(/:staged)', function ($deployment, $nrpeCmd, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
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
    if (preg_match('/,/', $nrpeCmd)) {
        $results = array();
        $cmds = preg_split('/\s?,\s?/', $nrpeCmd);
        foreach ($cmds as $cmd) {
            $cmdData = RevDeploy::getDeploymentNRPECmd($deployment, $cmd, $deployRev);
            if ($cmdData !== false) {
                $results[$cmd] = $cmdData;
            }
        }
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('commands', $results);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect nrpe command(s) specified: $nrpeCmd");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $cmdData = RevDeploy::getDeploymentNRPECmd($deployment, $nrpeCmd, $deployRev);
        if ($cmdData !== false) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('command', $cmdData);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect nrpe command specified: $nrpeCmd");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
})->name('saigon-api-get-nrpe-cmd');

$app->delete('/sapi/nrpecmd/:deployment/:nrpecmd', function ($deployment, $nrpeCmd) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $nrpeCmd)) {
        $results = array();
        $cmds = preg_split('/\s?,\s?/', $nrpeCmd);
        foreach ($cmds as $cmd) {
            RevDeploy::deleteDeploymentNRPECmd($deployment, $cmd, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentNRPECmd($deployment, $nrpeCmd, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed NRPE Command(s): $nrpeCmd");
    $apiResponse->printJson();
})->name('saigon-api-delete-nrpe-cmd');

