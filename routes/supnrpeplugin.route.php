<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Supplemental NRPE Plugin Routes
 */

$app->get('/sapi/supnrpepluginsmeta/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
    // Load up Current Plugins or Staged Plugins
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
        $apiResponse->setExtraResponseData('plugin_meta',
            RevDeploy::getCommonMergedDeploymentSupNRPEPluginsMetaData($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('plugin_meta',
            RevDeploy::getDeploymentSupNRPEPluginsMetaData($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-supplemental-nrpe-plugins-meta');

$app->get('/sapi/supnrpeplugins/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
    // Load up Current Plugins or Staged Plugins
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
        $apiResponse->setExtraResponseData('plugins',
            RevDeploy::getCommonMergedDeploymentSupNRPEPlugins($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('plugins',
            RevDeploy::getDeploymentSupNRPEPluginswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-supplemental-nrpe-plugins');

$app->post('/sapi/supnrpeplugin/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $pluginInfo = $request->getBody();
        $pluginInfo = json_decode($pluginInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $controller = new Controller();
        $pluginInfo['name'] = $request->post('name');
        $pluginInfo['file'] = $controller->fetchUploadedFile('file');
        $pluginInfo['desc'] = $request->post('desc');
        $pluginInfo['location'] = $request->post('location');
    }
    // A bit of param validation
    if ((!isset($pluginInfo['name'])) || (empty($pluginInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect name parameter (plugin name)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $pluginInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use plugin name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($pluginInfo['file'])) || (empty($pluginInfo['file']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect file parameter (expecting base64 encoded file contents if Content-Type is application/json)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($pluginInfo['desc'])) || (empty($pluginInfo['desc']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect desc parameter (plugin description)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($pluginInfo['location'])) || (empty($pluginInfo['location']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect location parameter (plugin location expecting unix path)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param manipulation for storage / verification purposes
    if ((isset($pluginInfo['file'])) && (!empty($pluginInfo['file']))) {
        if (($data = base64_decode($pluginInfo['file'], true)) === false) {
            $pluginInfo['md5'] = md5($pluginInfo['file']);
            $pluginInfo['file'] = base64_encode($pluginInfo['file']);
        }
        else {
            $pluginInfo['md5'] = md5($data);
        }
    }
    if ((isset($pluginInfo['location'])) && (!empty($pluginInfo['location']))) {
        $pluginInfo['location'] = base64_encode($pluginInfo['location']);
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSupNRPEPlugin($deployment, $pluginInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentSupNRPEPlugin($deployment, $pluginInfo['name'], $pluginInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Supplemental NRPE Plugin: " . $pluginInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentSupNRPEPlugin($deployment, $pluginInfo['name'], $pluginInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Supplemental NRPE Plugin: " . $pluginInfo['name']
        );
    }
    $apiResponse->setExtraResponseData('md5', $pluginInfo['md5']);
    $apiResponse->printJson();
})->name('saigon-api-create-supplemental-nrpe-plugin');

$app->get('/sapi/supnrpeplugin/:deployment/:supnrpeplugin(/:staged)', function ($deployment, $supNRPEPlugin, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    // Load up Current Plugins or Staged Plugins
    if ($staged == 1) {
        $revs = RevDeploy::getDeploymentRevs($deployment);
        if ($revs['currrev'] == $revs['nextrev']) { 
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect staged revision to reference");
            $app->halt(404, $apiResponse->returnJson());
        }
        $deployRev = $revs['nextrev'];
    } else {
        $deployRev = RevDeploy::getDeploymentRev($deployment);
    }
    if (preg_match('/,/', $supNRPEPlugin)) {
        $results = array();
        $plugins = preg_split('/\s?,\s?/', $supNRPEPlugin);
        foreach ($plugins as $plugin) {
            $pluginData = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $plugin, $deployRev);
            if ($pluginData !== false) {
                $results[$plugin] = $pluginData;
            }
        }
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('plugins', $results);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect supplemental nrpe plugin(s) specified: $supNRPEPlugin"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    } else {
        $pluginData = RevDeploy::getDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $deployRev);
        if ($pluginData !== false) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('plugin', $pluginData);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect supplemental nrpe plugin specified: $supNRPEPlugin"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
})->name('saigon-api-get-supplemental-nrpe-plugin');

$app->delete('/sapi/supnrpeplugin/:deployment/:supnrpeplugin', function ($deployment, $supNRPEPlugin) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $supNRPEPlugin)) {
        $results = array();
        $plugins = preg_split('/\s?,\s?/', $supNRPEPlugin);
        foreach ($plugins as $plugin) {
            RevDeploy::deleteDeploymentSupNRPEPlugin($deployment, $plugin, $deployRev);
        }
    } else {
        RevDeploy::deleteDeploymentSupNRPEPlugin($deployment, $supNRPEPlugin, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment,
        "Successfully Removed Supplemental NRPE Plugin(s): $supNRPEPlugin"
    );
    $apiResponse->printJson();
})->name('saigon-api-delete-supplemental-nrpe-plugin');

