<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Original v1 API Commands
 *      Here for backwards compatibility, will remove when possible
 *      Modifications were made from APIController to make it function in Slim
 */

$app->map('/api/getMGCfg/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    if (RevDeploy::existsDeploymentModgearmanCfg($deployment, $deployrev) === true) {
        $results = RevDeploy::getDeploymentModgearmanCfg($deployment, $deployrev);
        echo json_encode($results);
        return;
    } 
    $apiResponse = new APIViewData(1, $deployment,
        "Unable to detect modgearman config file for deployment: $deployment"
    );
    $app->halt(403, $apiResponse->returnJson());
})->via('GET', 'POST');

$app->get('/api/getNagiosCfg/:deployment(/:subdeployment)', function($deployment, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    $viewData = RevDeploy::getDeploymentData($deployment, $deployrev);
    if ($subdeployment !== false) {
        $hostsearchresults = array();
        foreach ($viewData['hostsearches'] as $key => $hostsearch) {
            if ($hostsearch['subdeployment'] == $subdeployment) {
                unset($hostsearch['subdeployment']);
                $hostsearchresults[] = $hostsearch;
            }
        }
        $viewData['hostsearches'] = $hostsearchresults;
        $nodetemplateresults = array();
        foreach ($viewData['nodetemplates'] as $key => $templateinfo) {
            if ($templateinfo['subdeployment'] == $subdeployment) {
                unset($templateinfo['subdeployment']);
                $nodetemplateresults[$key] = $templateinfo;
            }
        }
        $viewData['nodetemplates'] = $nodetemplateresults;
        $statichostsresults = array();
        foreach ($viewData['statichosts'] as $key => $statichostinfo) {
            if ($statichostinfo['subdeployment'] == $subdeployment) {
                unset($statichostinfo['subdeployment']);
                $statichostsresults[$key] = $statichostinfo;
            }
        }
        $viewData['statichosts'] = $statichostsresults;
    } else {
        foreach ($viewData['hostsearches'] as $key => $hostsearch) {
            unset($viewData['hostsearches'][$key]['subdeployment']);
        }
        foreach ($viewData['nodetemplates'] as $key => $nodetemplate) {
            unset($viewData['nodetemplates'][$key]['subdeployment']);
        }
        foreach ($viewData['statichosts'] as $key => $statichostinfo) {
            unset($viewData['statichosts'][$key]['subdeployment']);
        }
    }
    // This sucks, we need to reverse some of the data manipulation that was made
    // lower in the stack... however, this is only to support legacy, so it is what it is...
    foreach ($viewData['contacttemplates'] as $key => $infoArray) {
        $inspect = array('host_notification_options','service_notification_options');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['contacttemplates'][$key][$ikey] = implode(',', $viewData['contacttemplates'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['contacts'] as $key => $infoArray) {
        $inspect = array('host_notification_options','service_notification_options');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['contacts'][$key][$ikey] = implode(',', $viewData['contacts'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['contactgroups'] as $key => $infoArray) {
        $inspect = array('contactgroup_members','members');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['contactgroups'][$key][$ikey] = implode(',', $viewData['contactgroups'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['hosttemplates'] as $key => $infoArray) {
        $inspect = array('notification_options','contact_groups','contacts');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['hosttemplates'][$key][$ikey] = implode(',', $viewData['hosttemplates'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['servicetemplates'] as $key => $infoArray) {
        $inspect = array('notification_options','contact_groups','contacts');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['servicetemplates'][$key][$ikey] = implode(',', $viewData['servicetemplates'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['services'] as $key => $infoArray) {
        $inspect = array('notification_options','contact_groups','contacts');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['services'][$key][$ikey] = implode(',', $viewData['services'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['serviceescalations'] as $key => $infoArray) {
        $inspect = array('contact_groups','contacts');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['serviceescalations'][$key][$ikey] = implode(',', $viewData['serviceescalations'][$key][$ikey]);
            }
        }
    }
    foreach ($viewData['servicedependencies'] as $key => $infoArray) {
        $inspect = array('execution_failure_criteria','notification_failure_criteria');
        foreach ($inspect as $ikey) {
            if ((isset($infoArray[$ikey])) && (!empty($infoArray[$ikey]))) {
                $viewData['servicedependencies'][$key][$ikey] = implode(',', $viewData['servicedependencies'][$key][$ikey]);
            }
        }
    }
    echo json_encode($viewData);
});

$app->map('/api/getNRPECfg/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    $nrpeCfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $deployrev);
    $plugins = RevDeploy::getCommonMergedDeploymentNRPEPluginsMetaData($deployment, $deployrev);
    $viewData = new ViewData();
    if ((empty($nrpeCfgInfo)) && (empty($plugins))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect nrpe config information for deployment: $deployment"
        );
        $app->halt(403, $apiResponse->returnJson());
    } elseif ((empty($nrpeCfgInfo)) && (!empty($plugins))) {
        foreach ($plugins as $plugin => $pArray) {
            unset($plugins[$plugin]['desc']);
            unset($plugins[$plugin]['deployment']);
        }
        $viewData->b64 = "";
        $viewData->location = "";
        $viewData->md5 = "";
        $viewData->plugins = $plugins;
    } elseif ((!empty($nrpeCfgInfo)) && (empty($plugins))) {
        $msg = NRPECreate::buildNRPEFile($deployment, $deployrev, $nrpeCfgInfo);
        $viewData->b64 = base64_encode($msg);
        $viewData->location = $nrpeCfgInfo['location'];
        $viewData->md5 = md5($msg);
        $viewData->plugins = array();
    } else {
        foreach ($plugins as $plugin => $pArray) {
            unset($plugins[$plugin]['desc']);
            unset($plugins[$plugin]['deployment']);
        }
        $msg = NRPECreate::buildNRPEFile($deployment, $deployrev, $nrpeCfgInfo);
        $viewData->b64 = base64_encode($msg);
        $viewData->location = $nrpeCfgInfo['location'];
        $viewData->md5 = md5($msg);
        $viewData->plugins = $plugins;
    }
    echo json_encode($viewData);
})->via('GET', 'POST');

$app->map('/api/getSupNRPECfg/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    $nrpeCfgInfo = RevDeploy::getDeploymentSupNRPECfg($deployment, $deployrev);
    $plugins = RevDeploy::getCommonMergedDeploymentSupNRPEPluginsMetaData($deployment, $deployrev);
    $viewData = new ViewData();
    if ((empty($nrpeCfgInfo)) && (empty($plugins))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect supplemental nrpe config information for deployment: $deployment"
        );
        $app->halt(403, $apiResponse->returnJson());
    } elseif ((empty($nrpeCfgInfo)) && (!empty($plugins))) {
        foreach ($plugins as $plugin => $pArray) {
            unset($plugins[$plugin]['desc']);
            unset($plugins[$plugin]['deployment']);
        }
        $viewData->b64 = "";
        $viewData->location = "";
        $viewData->md5 = "";
        $viewData->plugins = $plugins;
    } elseif ((!empty($nrpeCfgInfo)) && (empty($plugins))) {
        $msg = NRPECreate::buildSupNRPEFile($deployment, $deployrev, $nrpeCfgInfo);
        $viewData->b64 = base64_encode($msg);
        $viewData->location = $nrpeCfgInfo['location'];
        $viewData->md5 = md5($msg);
        $viewData->plugins = array();
    } else {
        foreach ($plugins as $plugin => $pArray) {
            unset($plugins[$plugin]['desc']);
            unset($plugins[$plugin]['deployment']);
        }
        $msg = NRPECreate::buildSupNRPEFile($deployment, $deployrev, $nrpeCfgInfo);
        $viewData->b64 = base64_encode($msg);
        $viewData->location = $nrpeCfgInfo['location'];
        $viewData->md5 = md5($msg);
        $viewData->plugins = $plugins;
    }
    echo json_encode($viewData);
})->via('GET', 'POST');

$app->map('/api/getNRPEPlugin/:deployment/:plugins', function($deployment, $plugins) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    if (preg_match('/,/', $plugins)) {
        $tmpplugins = preg_split('/,/', $plugins);
        $results = array();
        foreach ($tmpplugins as $plugin) {
            $plugindata = RevDeploy::getCommonMergedDeploymentNRPEPlugin($deployment, $plugin, $deployrev);
            if (empty($plugindata)) {
                continue;
            }
            unset($plugindata['deployment']);
            unset($plugindata['desc']);
            $results[$plugin] = $plugindata;
        }
        if (!empty($results)) {
            echo json_encode($results);
            return;
        }
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect specified nrpe plugin: $plugins"
        );
        $app->halt(403, $apiResponse->returnJson());
    } else {
        $plugindata = RevDeploy::getCommonMergedDeploymentNRPEPlugin($deployment, $plugins, $deployrev);
        if (empty($plugindata)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified nrpe plugin: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        unset($plugindata['deployment']);
        unset($plugindata['desc']);
        $results[$plugins] = $plugindata;
        echo json_encode($results);
        return;
    }
})->via('GET', 'POST');

$app->map('/api/getSupNRPEPlugin/:deployment/:plugins', function($deployment, $plugins) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    if (preg_match('/,/', $plugins)) {
        $tmpplugins = preg_split('/,/', $plugins);
        $results = array();
        foreach ($tmpplugins as $plugin) {
            $plugindata = RevDeploy::getCommonMergedDeploymentSupNRPEPlugin($deployment, $plugin, $deployrev);
            if (empty($plugindata)) {
                continue;
            }
            unset($plugindata['deployment']);
            unset($plugindata['desc']);
            $results[$plugin] = $plugindata;
        }
        if (!empty($results)) {
            echo json_encode($results);
            return;
        }
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect specified supplemental nrpe plugins: $plugins"
        );
        $app->halt(403, $apiResponse->returnJson());
    } else {
        $plugindata = RevDeploy::getCommonMergedDeploymentSupNRPEPlugin($deployment, $plugins, $deployrev);
        if (empty($plugindata)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect specified supplemental nrpe plugin: $plugins"
            );
            $app->halt(403, $apiResponse->returnJson());
        }
        unset($plugindata['deployment']);
        unset($plugindata['desc']);
        $results[$plugins] = $plugindata;
        echo json_encode($results);
        return;
    }
})->via('GET', 'POST');

$app->map('/api/getRouterVM/:zone', function($zone) use ($app) {
    $zone = strtoupper($zone);
    if (CDC_DS::isRouterZone($zone) === false) {
        $apiResponse = new APIViewData(1, false,
            "Unable to detect router vm zone specified: $zone"
        );
        $apiResponse->setExtraResponseData('zone', $zone);
        $app->halt(403, $apiResponse->returnJson());
    }
    $results = CDC_DS::getRouterInfo($zone);
    echo $results;
    return;
})->via('GET', 'POST');

$app->map('/api/getNagiosPlugin/:deployment/:plugins', function($deployment, $plugins) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    if (preg_match('/,/', $plugins)) {
        $tmpplugins = preg_split('/,\s?/', $plugins);
        $results = array();
        foreach ($tmpplugins as $plugin) {
            $plugindata = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugin, $deployrev);
            if (empty($plugindata)) {
                $commonrev = RevDeploy::getDeploymentRev('common');
                $plugindata = RevDeploy::getDeploymentNagiosPlugin('common', $plugin, $commonrev);
                if (empty($plugindata)) {
                    $results[$plugin] = array();
                    continue;
                }
            }
            unset($plugindata['deployment']);
            unset($plugindata['desc']);
            $results[$plugin] = $plugindata;
        }
        if (!empty($results)) {
            echo json_encode($results);
            return;
        }
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect specified nagios plugins: $plugins"
        );
        $app->halt(403, $apiResponse->returnJson());
    } else {
        $plugindata = RevDeploy::getDeploymentNagiosPlugin($deployment, $plugins, $deployrev);
        if (empty($plugindata)) {
            $commonrev = RevDeploy::getDeploymentRev('common');
            $plugindata = RevDeploy::getDeploymentNagiosPlugin('common', $plugins, $commonrev);
            if (empty($plugindata)) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect specified nagios plugin: $plugins"
                );
                $app->halt(403, $apiResponse->returnJson());
            }
        }
        unset($plugindata['deployment']);
        unset($plugindata['desc']);
        $results[$plugins] = $plugindata;
        echo json_encode($results);
        return;
    }
})->via('GET', 'POST');

$app->map('/api/getNagiosPlugins/:deployment', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $deployrev = RevDeploy::getDeploymentRev($deployment);
    $plugindata = RevDeploy::getCommonMergedDeploymentNagiosPluginsMetaData($deployment, $deployrev);
    if (empty($plugindata)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to fetch plugins for $deployment"
        );
        $app->halt(403, $apiResponse->returnJson());
    }
    foreach ($plugindata as $plugin => $pArray) {
        unset($plugindata[$plugin]['deployment']);
    }
    echo json_encode($plugindata);
    return;
})->via('GET', 'POST');

