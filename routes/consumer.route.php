<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Consumer Routes
 *
 *      Updated the v1 api consumer routes to be more v2 compatible.
 */

/*
 * Saigon Meta Info Call
 */

$app->get('/sapi/consumer/saigoninfo/:deployment(/:subdeployment)', function($deployment, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    $deploymentData = RevDeploy::getDeploymentData($deployment, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, false);
    if ($subdeployment !== false) {
        $apiResponse->setExtraResponseData('subdeployment', $subdeployment);
        $hostsearchresults = array();
        foreach ($deploymentData['hostsearches'] as $key => $hostsearch) {
            if ($hostsearch['subdeployment'] == $subdeployment) {
                unset($hostsearch['subdeployment']);
                $hostsearchresults[] = $hostsearch;
            }
        }
        $deploymentData['hostsearches'] = $hostsearchresults;
        $nodetemplateresults = array();
        foreach ($deploymentData['nodetemplates'] as $key => $templateinfo) {
            if ($templateinfo['subdeployment'] == $subdeployment) {
                unset($templateinfo['subdeployment']);
                $nodetemplateresults[$key] = $templateinfo;
            }
        }
        $deploymentData['nodetemplates'] = $nodetemplateresults;
        $statichostsresults = array();
        foreach ($deploymentData['statichosts'] as $key => $statichostinfo) {
            if ($statichostinfo['subdeployment'] == $subdeployment) {
                unset($statichostinfo['subdeployment']);
                $statichostsresults[$key] = $statichostinfo;
            }
        }
        $deploymentData['statichosts'] = $statichostsresults;
    } else {
        foreach ($deploymentData['hostsearches'] as $key => $hostsearch) {
            unset($deploymentData['hostsearches'][$key]['subdeployment']);
        }
        foreach ($deploymentData['nodetemplates'] as $key => $nodetemplate) {
            unset($deploymentData['nodetemplates'][$key]['subdeployment']);
        }
        foreach ($deploymentData['statichosts'] as $key => $statichostinfo) {
            unset($deploymentData['statichosts'][$key]['subdeployment']);
        }
    }
    $apiResponse->setExtraResponseData('saigon', $deploymentData);
    $apiResponse->printJson();
});

/*
 * Saigon NRPE Configuration Routines
 */

$app->get('/sapi/consumer/nrpeconfig/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    $nrpeCfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $deployRev);
    $plugins = RevDeploy::getCommonMergedDeploymentNRPEPluginsMetaData($deployment, $deployRev);
    if ((empty($nrpeCfgInfo)) && (empty($plugins))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect nrpe config information for deployment: $deployment"
        );
        $app->halt(403, $apiResponse->returnJson());
    }
    $apiResponse = new APIViewData(0, $deployment, false);
    if ((empty($nrpeCfgInfo)) && (!empty($plugins))) {
        $apiResponse->setExtraResponseData('plugins', $plugins);
    } elseif ((!empty($nrpeCfgInfo)) && (empty($plugins))) {
        $config = NRPECreate::buildNRPEFile($deployment, $deployRev, $nrpeCfgInfo);
        $apiResponse->setExtraResponseData('contents', base64_encode($config));
        $apiResponse->setExtraResponseData('location', $nrpeCfgInfo['location']);
        $apiResponse->setExtraResponseData('md5', md5($config));
    } else {
        $config = NRPECreate::buildNRPEFile($deployment, $deployRev, $nrpeCfgInfo);
        $apiResponse->setExtraResponseData('contents', base64_encode($config));
        $apiResponse->setExtraResponseData('location', $nrpeCfgInfo['location']);
        $apiResponse->setExtraResponseData('md5', md5($config));
        $apiResponse->setExtraResponseData('plugins', $plugins);
    }
    $apiResponse->printJson();
})->name('saigon-api-consumer-nrpe-config');

$app->get('/sapi/consumer/supnrpeconfig/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    $nrpeCfgInfo = RevDeploy::getDeploymentSupNRPECfg($deployment, $deployRev);
    $plugins = RevDeploy::getCommonMergedDeploymentSupNRPEPluginsMetaData($deployment, $deployRev);
    if ((empty($nrpeCfgInfo)) && (empty($plugins))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect supplemental nrpe config information for deployment: $deployment"
        );
        $app->halt(403, $apiResponse->returnJson());
    }
    $apiResponse = new APIViewData(0, $deployment, false);
    if ((empty($nrpeCfgInfo)) && (!empty($plugins))) {
        $apiResponse->setExtraResponseData('plugins', $plugins);
    } elseif ((!empty($nrpeCfgInfo)) && (empty($plugins))) {
        $config = NRPECreate::buildSupNRPEFile($deployment, $deployRev, $nrpeCfgInfo);
        $apiResponse->setExtraResponseData('contents', base64_encode($config));
        $apiResponse->setExtraResponseData('location', $nrpeCfgInfo['location']);
        $apiResponse->setExtraResponseData('md5', md5($config));
    } else {
        $config = NRPECreate::buildSupNRPEFile($deployment, $deployRev, $nrpeCfgInfo);
        $apiResponse->setExtraResponseData('contents', base64_encode($config));
        $apiResponse->setExtraResponseData('location', $nrpeCfgInfo['location']);
        $apiResponse->setExtraResponseData('md5', md5($config));
        $apiResponse->setExtraResponseData('plugins', $plugins);
    }
    $apiResponse->printJson();
})->name('saigon-api-consumer-supplemental-nrpe-config');

/*
 * Saigon NRPE Plugin Routines
 */

$app->get('/sapi/consumer/nrpeplugin/:deployment/:plugins', function($deployment, $plugins) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    if (preg_match('/,/', $plugins)) {
        $tmpplugins = preg_split('/,/', $plugins);
        $results = array();
        foreach ($tmpplugins as $plugin) {
            $pluginData = RevDeploy::getCommonMergedDeploymentNRPEPlugin($deployment, $plugin, $deployRev);
            if (empty($pluginData)) continue;
            $results[$plugin] = $pluginData;
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified nrpe plugin: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('plugins', $results);
    } else {
        $pluginData = RevDeploy::getCommonMergedDeploymentNRPEPlugin($deployment, $plugins, $deployRev);
        if (empty($pluginData)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified nrpe plugin: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        $results[$plugins] = $pluginData;
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('plugins', $results);
    }
    $apiResponse->printJson();
})->name('saigon-api-consumer-nrpe-plugin');

$app->get('/sapi/consumer/supnrpeplugin/:deployment/:plugins', function($deployment, $plugins) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    if (preg_match('/,/', $plugins)) {
        $tmpplugins = preg_split('/,/', $plugins);
        $results = array();
        foreach ($tmpplugins as $plugin) {
            $pluginData = RevDeploy::getCommonMergedDeploymentSupNRPEPlugin($deployment, $plugin, $deployRev);
            if (empty($pluginData)) continue;
            $results[$plugin] = $pluginData;
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified supplemental nrpe plugins: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('plugins', $results);
    } else {
        $pluginData = RevDeploy::getCommonMergedDeploymentSupNRPEPlugin($deployment, $plugins, $deployRev);
        if (empty($pluginData)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified supplemental nrpe plugin: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        $results[$plugins] = $pluginData;
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('plugins', $results);
    }
    $apiResponse->printJson();
})->name('saigon-api-consumer-supplemental-nrpe-plugin');

/*
 * Saigon Nagios Plugin Routines
 */

$app->get('/sapi/consumer/nagiosplugin/:deployment/:plugins', function($deployment, $plugins) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    if (preg_match('/,/', $plugins)) {
        $tmpplugins = preg_split('/,\s?/', $plugins);
        $results = array();
        foreach ($tmpplugins as $plugin) {
            $pluginData = RevDeploy::getCommonMergedDeploymentNagiosPlugin($deployment, $plugin, $deployRev);
            if (empty($pluginData)) continue;
            $results[$plugin] = $pluginData;
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified nagios plugins: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('plugins', $results);
    } else {
        $pluginData = RevDeploy::getCommonMergedDeploymentNagiosPlugin($deployment, $plugins, $deployRev);
        if (empty($pluginData)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified nagios plugin: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        $results[$plugins] = $pluginData;
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('plugins', $results);
    }
    $apiResponse->printJson();
})->name('saigon-api-consumer-nagios-plugin');

$app->get('/sapi/consumer/nagiosplugins/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    $pluginData = RevDeploy::getCommonMergedDeploymentNagiosPluginsMetaData($deployment, $deployRev);
    if (empty($pluginData)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to fetch plugins for $deployment"
        );
        $app->halt(403, $apiResponse->returnJson());
    }
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('meta', $pluginData);
    $apiResponse->printJson();
})->name('saigon-api-consumer-nagios-plugins-meta');

/*
 * Mod-Gearman Config
 */

$app->get('/sapi/consumer/modgearmanconfig/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployRev = RevDeploy::getDeploymentRev($deployment);
    if (RevDeploy::existsDeploymentModgearmanCfg($deployment, $deployRev) === true) {
        $config = RevDeploy::getDeploymentModgearmanCfg($deployment, $deployRev);
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('config', $config);
        $apiResponse->printJson();
        return;
    } 
    $apiResponse = new APIViewData(1, $deployment,
        "Unable to detect modgearman config file for deployment: $deployment"
    );
    $app->halt(403, $apiResponse->returnJson());
})->name('saigon-api-consumer-modgearman-config');

/*
 * Cloud.com RouterVM Routine
 */

$app->get('/sapi/consumer/routervms/:zone', function($zone) use ($app) {
    $zone = strtoupper($zone);
    if (CDC_DS::isRouterZone($zone) === false) {
        $apiResponse = new APIViewData(1, false,
            "Unable to detect router vm zone specified: $zone"
        );
        $apiResponse->setExtraResponseData('zone', $zone);
        $app->halt(403, $apiResponse->returnJson());
    }
    $results = json_decode(CDC_DS::getRouterInfo($zone),true);
    $apiResponse = new APIViewData(0, $zone, false);
    $apiResponse->setExtraResponseData('routervms', $results);
    $apiResponse->printJson();
})->name('saigon-api-consumer-routervms');

