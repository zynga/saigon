<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Nagios Plugin Routes
 */

$app->get('/sapi/nagiospluginsmeta/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
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
            RevDeploy::getCommonMergedDeploymentNagiosPluginsMetaData($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('plugin_meta',
            RevDeploy::getDeploymentNagiosPluginsMetaData($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-nagios-plugins-meta');

$app->get('/sapi/nagiosplugins/:deployment(/:staged)', function($deployment, $staged = false) use ($app) {
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
            RevDeploy::getCommonMergedDeploymentNagiosPlugins($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('plugins',
            RevDeploy::getDeploymentNagiosPluginswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-nagios-plugins');

$app->post('/sapi/nagiosplugin/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $pluginInfo = $request->getBody();
        $pluginInfo = json_decode($pluginInfo,true);
        if ((isset($pluginInfo['file'])) && (!empty($pluginInfo['file']))) {
            $data = base64_decode($pluginInfo['file'], true);
            if ($data === false) {
                $pluginInfo['md5'] = md5($pluginInfo['file']);
            }
            else {
                $pluginInfo['md5'] = md5($data);
            }
        }
        if ((isset($pluginInfo['location'])) && (!empty($pluginInfo['location']))) {
            $pluginInfo['location'] = base64_encode($pluginInfo['location']);
        }
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $controller = new Controller();
        $pluginInfo['name'] = $request->post('name');
        $pluginInfo['file'] = $controller->fetchUploadedFile('file');
        $pluginInfo['desc'] = $request->post('desc');
        $pluginInfo['location'] = $request->post('location');
        if ((isset($pluginInfo['file'])) && (!empty($pluginInfo['file']))) {
            $pluginInfo['md5'] = md5($pluginInfo['file']);
            $pluginInfo['file'] = base64_encode($pluginInfo['file']);
        }
        if ((isset($pluginInfo['location'])) && (!empty($pluginInfo['location']))) {
            $pluginInfo['location'] = base64_encode($pluginInfo['location']);
        }
    }
    if ((!isset($pluginInfo['name'])) || (empty($pluginInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect name parameter (plugin name)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all("/[^\w.-]/", $pluginInfo['name'], $forbidden)) {
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
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNagiosPlugin($deployment, $pluginInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentNagiosPlugin($deployment, $pluginInfo['name'], $pluginInfo, $deployRev);
        $msg = "Successfully Modified Nagios Plugin: " . $pluginInfo['name'];
    }
    else {
        RevDeploy::createDeploymentNagiosPlugin($deployment, $pluginInfo['name'], $pluginInfo, $deployRev);
        $msg = "Successfully Created Nagios Plugin: " . $pluginInfo['name'];
    }
    $apiResponse = new APIViewData(0, $deployment, $msg); 
    $apiResponse->setExtraResponseData('md5', $pluginInfo['md5']);
    $apiResponse->printJson();
})->name('saigon-api-create-nagios-plugin');

$app->get('/sapi/nagiosplugin/:deployment/:nagiosplugin(/:staged)', function ($deployment, $nagiosPlugin, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
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
    if (preg_match('/,/', $nagiosPlugin)) {
        $results = array();
        $plugins = preg_split('/\s?,\s?/', $nagiosPlugin);
        foreach ($plugins as $plugin) {
            $pluginData = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $deployRev);
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
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect nagios plugin(s) specified: $nagiosPlugin");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $pluginData = RevDeploy::getDeploymentNagiosPlugin($deployment, $nagiosPlugin, $deployRev);
        if ($pluginData !== false) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('plugin', $pluginData);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect nagios plugin specified: $nagiosPlugin");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
})->name('saigon-api-get-nagios-plugin');

$app->delete('/sapi/nagiosplugin/:deployment/:nagiosplugin', function ($deployment, $nagiosPlugin) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $nagiosPlugin)) {
        $results = array();
        $plugins = preg_split('/\s?,\s?/', $nagiosPlugin);
        foreach ($plugins as $plugin) {
            RevDeploy::deleteDeploymentNagiosPlugin($deployment, $plugin, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentNagiosPlugin($deployment, $nagiosPlugin, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Nagios Plugin(s): $nagiosPlugin");
    $apiResponse->printJson();
})->name('saigon-api-delete-nagios-plugin');

