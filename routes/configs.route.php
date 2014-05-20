<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Config Routes
 */

/*
 * Show Revision Routes
 */

$app->get('/sapi/configs/:deployment/show/:revision(/:subdeployment)', function($deployment, $revision, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    if (RevDeploy::existsDeploymentRev($deployment, $revision) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect deployment revision specified in datastore"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $islocked = NagTester::getDeploymentBuildLock($deployment, $subdeployment, $revision);
    if ($islocked === false) {
        $deploymentResults = NagTester::getDeploymentBuildInfo($deployment, $subdeployment, $revision);
        if (empty($deploymentResults)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect configuration files, change request type to POST to initiate creation"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $configs = json_decode($deploymentResults['configs'],true);
        foreach ($configs as $key => $value) {
            $configs[$key] = base64_encode($value);
        }
        $deploymentResults['endtime'] = $deploymentResults['timestamp'];
        unset($deploymentResults['timestamp']);
        $deploymentResults['servertime'] = time();
        unset($deploymentResults['configs']);
        $deploymentResults['configs'] = $configs;
        $apiResponse->setExtraResponseData('configs', $deploymentResults);
    }
    else {
        $apiResponse = new APIViewData(2, $deployment,
            "Job is still processing, please reload the page in 5 seconds"
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-configs-get-show');

$app->post('/sapi/configs/:deployment/show/:revision(/:subdeployment)', function($deployment, $revision, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    if (RevDeploy::existsDeploymentRev($deployment, $revision) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect deployment revision specified in datastore"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $showInfo = $request->getBody();
        $showInfo = json_decode($diffInfo,true);
        if ((!isset($showInfo['shard'])) || (empty($showInfo['shard']))) {
            $showInfo['shard'] = false;
        }
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $showInfo = array();
        $showInfo['shard'] = $request->post('shard');
    }
    $islocked = NagTester::getDeploymentBuildLock($deployment, $subdeployment, $revision);
    if ($islocked === false) {
        $force = $app->request()->post('force');
        $deploymentResults = NagTester::getDeploymentBuildInfo($deployment, $subdeployment, $revision);
        if ($force !== null) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'revision' => $revision,
                        'type' => 'build',
                        'subdeployment' => $subdeployment,
                        'shard' => $showInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            $apiResponse = new APIViewData(0, $deployment,
                "Build process has been initiated, please change request type to GET to fetch results."
            );
        }
        elseif (empty($deploymentResults)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'revision' => $revision,
                        'type' => 'build',
                        'subdeployment' => $subdeployment,
                        'shard' => $showInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            $apiResponse = new APIViewData(0, $deployment,
                "Build process has been initiated, please change request type to GET to fetch results."
            );
        }
        elseif ((isset($deploymentResults['timestamp'])) && ($deploymentResults['timestamp'] < time() - 60)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'revision' => $revision,
                        'type' => 'build',
                        'subdeployment' => $subdeployment,
                        'shard' => $showInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            $apiResponse = new APIViewData(0, $deployment,
                "Build process has been initiated, please change request type to GET to fetch results."
            );
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Build process initiation failed, unknown reasons at this time."
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $apiResponse = new APIViewData(2, $deployment,
            "Job is still processing, change request type to GET to fetch results."
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-configs-create-show');

/*
 * Test Revision Routes
 */

$app->get('/sapi/configs/:deployment/test/:revision(/:subdeployment)', function($deployment, $revision, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    if (RevDeploy::existsDeploymentRev($deployment, $revision) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect deployment revision specified in datastore"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $islocked = NagTester::getDeploymentTestLock($deployment, $subdeployment, $revision);
    if ($islocked === false) {
        $deploymentResults = NagTester::getDeploymentTestInfo($deployment, $subdeployment, $revision);
        if (empty($deploymentResults)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect configuration files, change request type to POST to initiate creation"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $deploymentResults['endtime'] = $deploymentResults['timestamp'];
        unset($deploymentResults['timestamp']);
        $deploymentResults['servertime'] = time();
        $apiResponse->setExtraResponseData('configs', $deploymentResults);
    }
    else {
        $apiResponse = new APIViewData(2, $deployment,
            "Job is still processing, please reload the page in 5 seconds"
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-configs-get-test');

$app->post('/sapi/configs/:deployment/test/:revision(/:subdeployment)', function($deployment, $revision, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    if (RevDeploy::existsDeploymentRev($deployment, $revision) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect deployment revision specified in datastore"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $testInfo = $request->getBody();
        $testInfo = json_decode($testInfo,true);
        if ((!isset($testInfo['shard'])) || (empty($testInfo['shard']))) {
            $testInfo['shard'] = false;
        }
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $testInfo = array();
        $testInfo['shard'] = $request->post('shard');
    }
    $islocked = NagTester::getDeploymentTestLock($deployment, $subdeployment, $revision);
    if ($islocked === false) {
        $force = $app->request()->post('force');
        $deploymentResults = NagTester::getDeploymentTestInfo($deployment, $subdeployment, $revision);
        if ($force !== null) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'revision' => $revision,
                        'type' => 'test',
                        'subdeployment' => $subdeployment,
                        'shard' => $testInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            $apiResponse = new APIViewData(0, $deployment,
                "Build process has been initiated, please change request type to GET to fetch results."
            );
        }
        elseif (empty($deploymentResults)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'revision' => $revision,
                        'type' => 'test',
                        'subdeployment' => $subdeployment,
                        'shard' => $testInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            $apiResponse = new APIViewData(0, $deployment,
                "Build process has been initiated, please change request type to GET to fetch results."
            );
        }
        elseif ((isset($deploymentResults['timestamp'])) && ($deploymentResults['timestamp'] < time() - 60)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'revision' => $revision,
                        'type' => 'test',
                        'subdeployment' => $subdeployment,
                        'shard' => $testInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            $apiResponse = new APIViewData(0, $deployment,
                "Build process has been initiated, please change request type to GET to fetch results."
            );
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Build process initiation failed, unknown reasons at this time."
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $apiResponse = new APIViewData(2, $deployment,
            "Job is still processing, change request type to GET to fetch results."
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-configs-create-test');

/*
 * Diff Config Routes
 */

$app->get('/sapi/configs/:deployment/diff(/:subdeployment)', function($deployment, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $islocked = NagTester::getDeploymentDiffLock($deployment, $subdeployment);
    if ($islocked === false) {
        $diffResults = NagTester::getDeploymentDiffInfo($deployment, $subdeployment);
        if (empty($diffResults)) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect diff results, change request type to POST to initiate creation"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $configdata = json_decode($diffResults['configs'], true);
        $confdiff = $results = array();
        $confdiff['nagios'] = NagDiff::diff($configdata['nagiosconfs']['from'], $configdata['nagiosconfs']['to']);
        $confdiff['nagiosplugins'] = NagDiff::diff($configdata['plugins']['nagios']['from'], $configdata['plugins']['nagios']['to']);
        $confdiff['nrpe'] = NagDiff::diff($configdata['plugins']['nrpe']['core']['from'], $configdata['plugins']['nrpe']['core']['to']);
        $confdiff['supnrpe'] = NagDiff::diff($configdata['plugins']['nrpe']['sup']['from'], $configdata['plugins']['nrpe']['sup']['to']);
        foreach (array('nagios','nagiosplugins','nrpe','supnrpe') as $mkey) {
            foreach ($confdiff[$mkey] as $dKey => $dObj) {
                $val = $dObj->getGroupedOpcodes();
                if (empty($val)) continue;
                $renderer = new Diff_Renderer_Text_Unified();
                $results[$mkey][$dKey] = base64_encode(htmlspecialchars($dObj->render($renderer)));
            }
        }
        $apiResponse->setExtraResponseData('diff', $results);
        unset($diffResults['configs']); // no need to pass raw data back to client
        $apiResponse->setExtraResponseData('meta', $diffResults, true);
    }
    else {
        $apiResponse = new APIViewData(2, $deployment,
            "Job is still processing, please reload the page in 5 seconds"
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-configs-get-diff');

$app->post('/sapi/configs/:deployment/diff(/:subdeployment)', function($deployment, $subdeployment = false) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $diffInfo = $request->getBody();
        $diffInfo = json_decode($diffInfo,true);
        if ((!isset($diffInfo['shard'])) || (empty($diffInfo['shard']))) {
            $diffInfo['shard'] = false;
        }
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $diffInfo = array();
        $diffInfo['to'] = $request->post('to');
        $diffInfo['from'] = $request->post('from');
        $diffInfo['shard'] = $request->post('shard');
    }
    foreach (array('from','to') as $revKey) {
        if ((!isset($diffInfo[$revKey])) || (empty($diffInfo[$revKey]))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect necessary parameter for diff job creation: $revKey"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        validateForbiddenChars($app, $deployment, '/[^0-9]/s', $revKey, $diffInfo[$revKey]);
        if (RevDeploy::existsDeploymentRev($deployment, $diffInfo[$revKey]) === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect deployment revision specified in datastore for $revKey / " . $diffInfo[$revKey]
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    $islocked = NagTester::getDeploymentDiffLock($deployment, $subdeployment);
    if ($islocked === false) {
        $force = $request->post('force');
        $diffResults = NagTester::getDeploymentDiffInfo($deployment, $subdeployment);
        if ($force !== null) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'type' => 'diff',
                        'subdeployment' => $subdeployment,
                        'fromrev' => $diffInfo['from'],
                        'torev' => $diffInfo['to'],
                        'shard' => $diffInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            if ($diffInfo['to'] < $diffInfo['from']) {
                $apiResponse = new APIViewData(0, $deployment,
                    "Reverse diff process has been initiated, please change request type to GET to fetch results."
                );
            }
            else {
                $apiResponse = new APIViewData(0, $deployment,
                    "Diff process has been initiated, please change request type to GET to fetch results."
                );
            }
        }
        elseif (empty($diffResults)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'type' => 'diff',
                        'subdeployment' => $subdeployment,
                        'fromrev' => $diffInfo['from'],
                        'torev' => $diffInfo['to'],
                        'shard' => $diffInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            if ($diffInfo['to'] < $diffInfo['from']) {
                $apiResponse = new APIViewData(0, $deployment,
                    "Reverse diff process has been initiated, please change request type to GET to fetch results."
                );
            }
            else {
                $apiResponse = new APIViewData(0, $deployment,
                    "Diff process has been initiated, please change request type to GET to fetch results."
                );
            }
        }
        elseif ((isset($diffResults['timestamp'])) && ($diffResults['timestamp'] < time() - 60)) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'type' => 'diff',
                        'subdeployment' => $subdeployment,
                        'fromrev' => $diffInfo['from'],
                        'torev' => $diffInfo['to'],
                        'shard' => $diffInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            if ($diffInfo['to'] < $diffInfo['from']) {
                $apiResponse = new APIViewData(0, $deployment,
                    "Reverse diff process has been initiated, please change request type to GET to fetch results."
                );
            }
            else {
                $apiResponse = new APIViewData(0, $deployment,
                    "Diff process has been initiated, please change request type to GET to fetch results."
                );
            }
        }
        elseif ((isset($diffResults['fromrev'])) && ($diffResults['fromrev'] != $diffInfo['from'])) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'type' => 'diff',
                        'subdeployment' => $subdeployment,
                        'fromrev' => $diffInfo['from'],
                        'torev' => $diffInfo['to'],
                        'shard' => $diffInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            if ($diffInfo['to'] < $diffInfo['from']) {
                $apiResponse = new APIViewData(0, $deployment,
                    "Reverse diff process has been initiated, please change request type to GET to fetch results."
                );
            }
            else {
                $apiResponse = new APIViewData(0, $deployment,
                    "Diff process has been initiated, please change request type to GET to fetch results."
                );
            }
        }
        elseif ((isset($diffResults['torev'])) && ($diffResults['torev'] != $diffInfo['to'])) {
            NagPhean::init(BEANSTALKD_SERVER, BEANSTALKD_TUBE, true);
            NagPhean::addJob(BEANSTALKD_TUBE,
                json_encode(
                    array(
                        'deployment' => $deployment,
                        'type' => 'diff',
                        'subdeployment' => $subdeployment,
                        'fromrev' => $diffInfo['from'],
                        'torev' => $diffInfo['to'],
                        'shard' => $diffInfo['shard'],
                        )
                    ),
                1024, 0, 900
            );
            if ($diffInfo['to'] < $diffInfo['from']) {
                $apiResponse = new APIViewData(0, $deployment,
                    "Reverse diff process has been initiated, please change request type to GET to fetch results."
                );
            }
            else {
                $apiResponse = new APIViewData(0, $deployment,
                    "Diff process has been initiated, please change request type to GET to fetch results."
                );
            }
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Diff process initiation failed, revisions match what was asked for less than a minutes ago, add force=1 to params to initiate job forcefully."
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        $apiResponse = new APIViewData(2, $deployment,
            "Job is still processing, change request type to GET to fetch results."
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-configs-create-diff');

/*
 * Resource Config Routes
 */

$app->get('/sapi/configs/:deployment/resource(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
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
    $apiResponse = new APIViewData(0, $deployment, false);
    if (RevDeploy::existsDeploymentResourceCfg($deployment, $deployRev) === true) {
        $apiResponse->setExtraResponseData(
            'resource_config',
            RevDeploy::getDeploymentResourceCfg($deployment, $deployRev)
        );
    } else {
        $apiResponse->setExtraResponseData(
            'resource_config',
            array('USER1' => 'L3Vzci9sb2NhbC9uYWdpb3MvbGliZXhlYw==')
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-resource-config');

$app->post('/sapi/configs/:deployment/resource', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $tmpConfigInfo = $request->getBody();
        $tmpConfigInfo = json_decode($tmpConfigInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $tmpConfigInfo = array();
        for ($i=1;$i<=32;$i++) {
            $value = $request->post('user' . $i);
            if ($value !== null) {
                if (($b64enc = base64_decode($value, true)) === false) {
                    $tmpConfigInfo['USER' . $i] = base64_encode($value);
                }
                else {
                    $tmpConfigInfo['USER' . $i] = $value;
                }
            }
        }
    }
    // A bit of param validation??
    $resourceConfigInfo = array();
    foreach ($tmpConfigInfo as $key => $value) {
        if (preg_match("/^user(\d+)$/", strtolower($key), $matches)) {
            if ($matches[1] > 32) {
                $apiResponse = new APIViewData(1, $deployment, "Unable to use parameter specified");
                $apiResponse->setExtraResponseData('parameter', $key);
                $apiResponse->setExtraResponseData('parameter-value', $value);
                $app->halt(404, $apiResponse->returnJson());
            }
            $resourceConfigInfo['USER' . $matches[1]] = base64_encode($value);
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to use parameter specified");
            $apiResponse->setExtraResponseData('parameter', $key);
            $apiResponse->setExtraResponseData('parameter-value', $value);
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentResourceCfg($deployment, $deployRev) === true) {
        RevDeploy::modifyDeploymentResourceCfg($deployment, $resourceConfigInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Resource Config"
        );
    }
    else {
        RevDeploy::createDeploymentResourceCfg($deployment, $resourceConfigInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Resource Config"
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-resource-config');

$app->delete('/sapi/configs/:deployment/resource', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    RevDeploy::deleteDeploymentResourceCfg($deployment, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Resource Config");
    $apiResponse->printJson();
})->name('saigon-api-delete-resource-config');

/*
 * CGI Config Routes
 */

$app->get('/sapi/configs/:deployment/cgi(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
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
    $apiResponse = new APIViewData(0, $deployment, false);
    if (RevDeploy::existsDeploymentCgiCfg($deployment, $deployRev) === true) {
        $apiResponse->setExtraResponseData(
            'cgi_config',
            RevDeploy::getDeploymentCgiCfg($deployment, $deployRev)
        );
    } else {
        $apiResponse->setExtraResponseData(
            'cgi_config',
            array(
                'main_config_file' => base64_encode('/usr/local/nagios/etc/nagios.cfg'),
                'physical_html_path' => base64_encode('/usr/local/nagios/share'),
                'url_html_path' => base64_encode('/nagios'),
                'show_context_help' => 0,
                'use_pending_states' => 1,
                'use_authentication' => 1,
                'use_ssl_authentication' => 0,
                'authorized_for_system_information' => "*",
                'authorized_for_configuration_information' => "*",
                'authorized_for_system_commands' => "*",
                'authorized_for_all_services' => "*",
                'authorized_for_all_hosts' => "*",
                'authorized_for_all_service_commands' => "*",
                'authorized_for_all_host_commands' => "*",
                'authorized_for_read_only' => "",
                'default_statusmap_layout' => 5,
                'default_statuswrl_layout' => 4,
                'ping_syntax' => base64_encode('/bin/ping -n -U -c 5 $HOSTADDRESS$'),
                'refresh_rate' => 90,
                'escape_html_tags' => 1,
                'action_url_target' => '_blank',
                'notes_url_target' => '_blank',
                'lock_author_names' => 1,
                'enable_splunk_integration' => 0,
                'splunk_url' => base64_encode('http://127.0.0.1:8000/'),
                'result_limit' => 0,
            )
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-cgi-config');


$app->post('/sapi/configs/:deployment/cgi', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $keys = array(
        'main_config_file','physical_html_path','url_html_path','show_context_help','use_pending_states',
        'use_authentication','use_ssl_authentication','result_limit','authorized_for_system_information',
        'authorized_for_configuration_information','authorized_for_system_commands','authorized_for_all_services',
        'authorized_for_all_hosts','authorized_for_all_service_commands','authorized_for_all_host_commands',
        'authorized_for_read_only','default_statusmap_layout','default_statuswrl_layout','ping_syntax','refresh_rate',
        'escape_html_tags','action_url_target','notes_url_target','lock_author_names','enable_splunk_integration',
        'splunk_url'
    );
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $cgiConfigInfo = $request->getBody();
        $cgiConfigInfo = json_decode($cgiConfigInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $cgiConfigInfo = array();
        foreach ($keys as $key) {
            $cgiConfigInfo[$key] = $request->post($key);
        }
    }
    // A bit of param validation
    foreach ($keys as $key) {
        if (($key != "splunk_url") && ($key != "enable_splunk_integration") && ($key != "authorized_for_read_only")) {
            if ((isset($cgiConfigInfo[$key])) && (empty($cgiConfigInfo[$key])) && ($cgiConfigInfo[$key] != 0)) {
                $apiResponse = new APIViewData(1, $deployment, "Unable to detect required parameter: $key");
                $app->halt(404, $apiResponse->returnJson());
            }
        }
        switch ($key) {
            case "main_config_file":
            case "physical_html_path":
            case "url_html_path":
            case "ping_syntax":
            case "splunk_url":
                if (($value = base64_decode($cgiConfigInfo[$key], true)) === false) {
                    $cgiConfigInfo[$key] = base64_encode($cgiConfigInfo[$key]);
                }
                break;
            case "show_context_help":
            case "use_pending_states":
            case "use_authentication":
            case "use_ssl_authentication":
            case "enable_splunk_integration":
            case "lock_author_names":
            case "escape_html_tags":
                validateBinary($app, $deployment, $key, $cgiConfigInfo[$key]); break;
            case "refresh_rate":
                validateInterval($app, $deployment, $key, $cgiConfigInfo[$key], 5, 300); break;
            case "result_limit":
                validateInterval($app, $deployment, $key, $cgiConfigInfo[$key], 0, 5000); break;
            case "authorized_for_system_information":
            case "authorized_for_configuration_information":
            case "authorized_for_system_commands":
            case "authorized_for_all_services":
            case "authorized_for_all_hosts":
            case "authorized_for_all_service_commands":
            case "authorized_for_all_host_commands":
            case "authorized_for_read_only":
                if (is_array($cgiConfigInfo[$key])) $cgiConfigInfo[$key] = implode(',', $cgiConfigInfo[$key]);
                validateForbiddenChars($app, $deployment, '/[^\w-\*]/s', $key, $cgiConfigInfo[$key]); break;
            default:
                break;
        }
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentCgiCfg($deployment, $deployRev) === true) {
        RevDeploy::modifyDeploymentCgiCfg($deployment, $cgiConfigInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified CGI Config"
        );
    }
    else {
        RevDeploy::createDeploymentCgiCfg($deployment, $cgiConfigInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created CGI Config"
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-cgi-config');

$app->delete('/sapi/configs/:deployment/cgi', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    RevDeploy::deleteDeploymentCgiCfg($deployment, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed CGI Config");
    $apiResponse->printJson();
})->name('saigon-api-delete-cgi-config');

/*
 * Nagios Config Routes
 */

$app->get('/sapi/configs/:deployment/nagios(/:staged)', function($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
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
    $apiResponse = new APIViewData(0, $deployment, false);
    if (RevDeploy::existsDeploymentNagiosCfg($deployment, $deployRev) === true) {
        $apiResponse->setExtraResponseData(
            'nagios_config',
            RevDeploy::getDeploymentNagiosCfg($deployment, $deployRev)
        );
    } else {
        $apiResponse->setExtraResponseData(
            'nagios_config',
            array(
            	'accept_passive_host_checks' => 1,
            	'accept_passive_service_checks' => 1,
            	'cached_host_check_horizon' => 15,
            	'cached_service_check_horizon' => 15,
            	'cfg_dir' => base64_encode('/usr/local/nagios/etc/objects'),
            	'check_external_commands' => 1,
            	'check_for_orphaned_hosts' => 1,
            	'check_for_orphaned_services' => 1,
            	'check_host_freshness' => 1,
            	'check_result_path' => base64_encode('/usr/local/nagios/var/spool/checkresults'),
            	'check_result_reaper_frequency' => 10,
            	'check_service_freshness' => 1,
            	'command_check_interval' => -1,
            	'command_file' => base64_encode('/usr/local/nagios/var/rw/nagios.cmd'),
            	'enable_event_handlers' => 1,
            	'enable_notifications' => 1,
            	'enable_predictive_host_dependency_checks' => 1,
            	'enable_predictive_service_dependency_checks' => 1,
            	'event_broker_options' => -1,
            	'event_handler_timeout' => 30,
            	'execute_host_checks' => 1,
            	'execute_service_checks' => 1,
            	'external_command_buffer_slots' => 4096,
            	'host_check_timeout' => 30,
            	'host_freshness_check_interval' => 60,
            	'illegal_macro_output_chars' => base64_encode('`~$&|\'"<>'),
            	'illegal_object_name_chars' => base64_encode('`~!$%^&*|\'"<>?,()='),
            	'lock_file' => base64_encode('/usr/local/nagios/var/nagios.lock'),
            	'log_archive_path' => base64_encode('/usr/local/nagios/var/archives'),
            	'log_event_handlers' => 1,
            	'log_external_commands' => 1,
            	'log_file' => base64_encode('/usr/local/nagios/var/nagios.log'),
            	'log_host_retries' => 1,
            	'log_initial_states' => 0,
            	'log_notifications' => 1,
            	'log_passive_checks' => 1,
            	'log_rotation_method' => 'd',
            	'log_service_retries' => 1,
            	'max_check_result_file_age' => 3600,
            	'max_check_result_reaper_time' => 30,
            	'nagios_group' => 'nagios',
            	'nagios_user' => 'nagios',
            	'notification_timeout' => 30,
            	'object_cache_file' => base64_encode('/usr/local/nagios/var/objects.cache'),
            	'precached_object_file' => base64_encode('/usr/local/nagios/var/objects.precache'),
            	'resource_file' => base64_encode('/usr/local/nagios/etc/resource.cfg'),
            	'retain_state_information' => 1,
            	'retention_update_interval' => 60,
            	'service_check_timeout' => 60,
            	'service_freshness_check_interval' => 60,
            	'soft_state_dependencies' => 0,
            	'state_retention_file' => base64_encode('/usr/local/nagios/var/retention.dat'),
            	'status_file' => base64_encode('/usr/local/nagios/var/status.dat'),
            	'status_update_interval' => 10,
            	'temp_file' => base64_encode('/usr/local/nagios/var/nagios.tmp'),
            	'temp_path' => base64_encode('/tmp'),
            	'use_large_installation_tweaks' => 0,
            	'use_retained_program_state' => 1,
            	'use_retained_scheduling_info' => 1,
            	'use_syslog' => 1,
            	'additional_freshness_latency' => 15,
            	'admin_email' => 'nagios@localhost.com',
            	'admin_pager' => 'pagenagios@localhost.com',
            	'auto_reschedule_checks' => 0,
            	'auto_rescheduling_interval' => 30,
            	'auto_rescheduling_window' => 180,
            	'bare_update_check' => 0,
            	'check_for_updates' => 0,  // Not Default
            	'daemon_dumps_core' => 0,
            	'date_format' => 'us',
            	'debug_file' => base64_encode('/usr/local/nagios/var/nagios.debug'),
            	'debug_level' => 0,
            	'debug_verbosity' => 1,
            	'enable_embedded_perl' => 1,
            	'enable_environment_macros' => 0,  // Not Default
            	'enable_flap_detection' => 0,  // Not Default
            	'high_host_flap_threshold' => 20.0,
            	'high_service_flap_threshold' => 20.0,
            	'host_inter_check_delay_method' => 's',
            	'interval_length' => 60,
            	'low_host_flap_threshold' => 5.0,
            	'low_service_flap_threshold' => 5.0,
            	'max_concurrent_checks' => 0,
            	'max_host_check_spread' => 30,
            	'max_service_check_spread' => 30,
            	'max_debug_file_size' => 1000000,
            	'obsess_over_hosts' => 0,
            	'obsess_over_services' => 0,
            	'ocsp_timeout' => 5,
            	'ochp_timeout' => 5,
            	'ocsp_command' => base64_encode('ocsp_command'),
            	'ochp_command' => base64_encode('ochp_command'),
            	'p1_file' => base64_encode('/usr/local/nagios/bin/p1.pl'),
            	'passive_host_checks_are_soft' => 0,
            	'retained_contact_host_attribute_mask' => 0,
            	'retained_contact_service_attribute_mask' => 0,
            	'retained_host_attribute_mask' => 0,
            	'retained_process_host_attribute_mask' => 0,
            	'retained_process_service_attribute_mask' => 0,
            	'retained_service_attribute_mask' => 0,
            	'service_inter_check_delay_method' => 's',
            	'service_interleave_factor' => 's',
            	'sleep_time' => 0.25,
            	'translate_passive_host_checks' => 0,
            	'use_aggressive_host_checking' => 0,
            	'use_embedded_perl_implicitly' => 1,
            	'use_regexp_matching' => 0,
            	'use_true_regexp_matching' => 0,
            	'process_performance_data' => 0,
            	'perfdata_timeout' => 5,
            	'host_perfdata_command' => base64_encode('process-host-perfdata'),
            	'service_perfdata_command' => base64_encode('process-service-perfdata'),
            	'host_perfdata_file' => base64_encode('/tmp/host-perfdata'),
            	'service_perfdata_file' => base64_encode('/tmp/service-perfdata'),
            	'host_perfdata_file_template' => base64_encode('[HOSTPERFDATA]\t$TIMET$\t$HOSTNAME$\t$HOSTEXECUTIONTIME$\t$HOSTOUTPUT$\t$HOSTPERFDATA$'),
            	'service_perfdata_file_template' => base64_encode('[SERVICEPERFDATA]\t$TIMET$\t$HOSTNAME$\t$SERVICEDESC$\t$SERVICEEXECUTIONTIME$\t$SERVICELATENCY$\t$SERVICEOUTPUT$\t$SERVICEPERFDATA$'),
            	'host_perfdata_file_mode' => 'a',
            	'service_perfdata_file_mode' => 'a',
            	'host_perfdata_file_processing_interval' => 0,
            	'service_perfdata_file_processing_interval' => 0,
            	'host_perfdata_file_processing_command' => base64_encode('process-host-perfdata-file'),
            	'service_perfdata_file_processing_command' => base64_encode('process-service-perfdata-file'),
            )
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-nagios-config');


$app->post('/sapi/configs/:deployment/nagios', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $keys = array(
        'accept_passive_host_checks','accept_passive_service_checks','cached_host_check_horizon',
        'cached_service_check_horizon','cfg_dir','check_external_commands','check_for_orphaned_hosts',
        'check_for_orphaned_services','check_host_freshness','check_result_path','check_result_reaper_frequency',
        'check_service_freshness','command_check_interval','command_file','enable_event_handlers','enable_notifications',
        'enable_predictive_host_dependency_checks','enable_predictive_service_dependency_checks','event_broker_options',
        'event_handler_timeout','execute_host_checks','execute_service_checks','external_command_buffer_slots',
        'host_check_timeout','host_freshness_check_interval','illegal_macro_output_chars','illegal_object_name_chars',
        'lock_file','log_archive_path','log_event_handlers','log_external_commands','log_file','log_host_retries',
        'log_initial_states','log_notifications','log_passive_checks','log_rotation_method','log_service_retries',
        'max_check_result_file_age','max_check_result_reaper_time','nagios_group','nagios_user','notification_timeout',
        'object_cache_file','precached_object_file','resource_file','retain_state_information','retention_update_interval',
        'service_check_timeout','service_freshness_check_interval','soft_state_dependencies','state_retention_file',
        'status_file','status_update_interval','temp_file','temp_path','use_large_installation_tweaks',
        'use_retained_program_state','use_retained_scheduling_info','use_syslog','additional_freshness_latency','admin_email',
        'admin_pager','auto_reschedule_checks','auto_rescheduling_interval','auto_rescheduling_window','bare_update_check',
        'check_for_updates','daemon_dumps_core','date_format','debug_file','debug_level','debug_verbosity',
        'enable_embedded_perl','enable_environment_macros','enable_flap_detection','high_host_flap_threshold',
        'high_service_flap_threshold','host_inter_check_delay_method','interval_length','low_host_flap_threshold',
        'low_service_flap_threshold','max_concurrent_checks','max_host_check_spread','max_service_check_spread',
        'max_debug_file_size','obsess_over_hosts','obsess_over_services','ocsp_timeout','ochp_timeout','ocsp_command',
        'ochp_command','p1_file','passive_host_checks_are_soft','retained_contact_host_attribute_mask',
        'retained_contact_service_attribute_mask','retained_host_attribute_mask','retained_process_host_attribute_mask',
        'retained_process_service_attribute_mask','retained_service_attribute_mask','service_inter_check_delay_method',
        'service_interleave_factor','sleep_time','translate_passive_host_checks','use_aggressive_host_checking',
        'use_embedded_perl_implicitly','use_regexp_matching','use_true_regexp_matching','process_performance_data',
        'perfdata_timeout','host_perfdata_command','service_perfdata_command','host_perfdata_file','service_perfdata_file',
        'host_perfdata_file_template','service_perfdata_file_template','host_perfdata_file_mode','service_perfdata_file_mode',
        'host_perfdata_file_processing_interval','service_perfdata_file_processing_interval',
        'host_perfdata_file_processing_command','service_perfdata_file_processing_command','broker_modules'
    );
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $nagiosConfigInfo = $request->getBody();
        $nagiosConfigInfo = json_decode($nagiosConfigInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $nagiosConfigInfo = array();
        foreach ($keys as $key) {
            $nagiosConfigInfo[$key] = $request->post($key);
        }
    }
    // A bit of param validation
    foreach ($keys as $key) {
        $skipKeys = array(
            'global_host_event_handler','global_service_event_handler','host_perfdata_command','service_perfdata_command',
            'host_perfdata_file','service_perfdata_file','host_perfdata_file_template','service_perfdata_file_template',
            'host_perfdata_file_mode','service_perfdata_file_mode','host_perfdata_file_processing_interval',
            'service_perfdata_file_processing_interval','host_perfdata_file_processing_command',
            'service_perfdata_file_processing_command','ocsp_command','ochp_command','broker_modules'
        );
        if (!in_array($key, $skipKeys)) {
            if ((isset($nagiosConfigInfo[$key])) && (empty($nagiosConfigInfo[$key])) && ($nagiosConfigInfo[$key] != 0)) {
                $apiResponse = new APIViewData(1, $deployment, "Unable to detect required parameter: $key");
                $app->halt(404, $apiResponse->returnJson());
            }
        }
        elseif ($key == 'broker_modules') {
            // check for broker modules / build up appropriate info to save / return.
            if ((!isset($nagiosConfigInfo[$key])) || (empty($nagiosConfigInfo[$key]))) {
                unset($nagiosConfigInfo[$key]);
                continue;
            }
            if (!is_array($nagiosConfigInfo[$key])) {
                if (preg_match('/,/', $nagiosConfigInfo[$key])) {
                    // Did someone submit csv b64 values??
                    $b64s = preg_split('/\s?,\s?/', $nagiosConfigInfo[$key]);
                    $i = 0;
                    foreach ($b64s as $b64) {
                        if (($value = base64_decode($b64, true)) !== false) {
                            $nagiosConfigInfo['broker_module_'.$i] = $b64;
                            $i++;
                        }
                    }
                }
                elseif (($value = base64_decode($nagiosConfigInfo[$key]) !== false)) {
                    // maybe they sent us one b64 encoded module to use??
                    $nagiosConfigInfo['broker_module_0'] = $nagiosConfigInfo[$key];
                }
            }
            elseif (is_array($nagiosConfigInfo[$key])) {
                $i = 0;
                foreach ($nagiosConfigInfo[$key] as $b64) {
                    if (($value = base64_decode($b64, true)) !== false) {
                        $nagiosConfigInfo['broker_module_'.$i] = $b64;
                        $i++;
                    }
                }
            }
            unset($nagiosConfigInfo[$key]);
            continue;
        }
        else {
            if ((!isset($nagiosConfigInfo[$key])) || (empty($nagiosConfigInfo[$key]))) {
                // set to nothingness, so UI doesn't break atm...
                $nagiosConfigInfo[$key] = "";
            }
            continue;
        }
        switch ($key) {
            case "cfg_dir":
            case "check_result_path":
            case "command_file":
            case "lock_file":
            case "log_archive_path":
            case "log_file":
            case "object_cache_file":
            case "precached_object_file":
            case "resource_file":
            case "state_retention_file":
            case "status_file":
            case "temp_file":
            case "temp_path":
            case "debug_file":
            case "ocsp_command":
            case "ochp_command":
            case "p1_file":
            case "host_perfdata_command":
            case "service_perfdata_command":
            case "host_perfdata_file":
            case "service_perfdata_file":
            case "host_perfdata_file_template":
            case "service_perfdata_file_template":
            case "host_perfdata_file_processing_command":
            case "service_perfdata_file_processing_command":
            case "illegal_macro_output_chars":
            case "illegal_object_name_chars":
                if (($value = base64_decode($nagiosConfigInfo[$key], true)) === false) {
                    $nagiosConfigInfo[$key] = base64_encode($nagiosConfigInfo[$key]);
                }
                break;
            case "accept_passive_host_checks":
            case "accept_passive_service_checks":
            case "check_external_commands":
            case "check_for_orphaned_hosts":
            case "check_for_orphaned_services":
            case "check_host_freshness":
            case "check_service_freshness":
            case "enable_event_handlers":
            case "enable_notifications":
            case "enable_predictive_host_dependency_checks":
            case "enable_predictive_service_dependency_checks":
            case "execute_host_checks":
            case "execute_service_checks":
            case "log_event_handlers":
            case "log_external_commands":
            case "log_host_retries":
            case "log_initial_states":
            case "log_notifications":
            case "log_passive_checks":
            case "log_service_retries":
            case "retain_state_information":
            case "soft_state_dependencies":
            case "use_large_installation_tweaks":
            case "use_retained_program_state":
            case "use_retained_scheduling_info":
            case "use_syslog":
            case "auto_reschedule_checks":
            case "bare_update_check":
            case "check_for_updates":
            case "daemon_dumps_core":
            case "enable_embedded_perl":
            case "enable_environment_macros":
            case "enable_flap_detection":
            case "max_concurrent_checks":
            case "obsess_over_hosts":
            case "obsess_over_services":
            case "passive_host_checks_are_soft":
            case "retained_contact_host_attribute_mask":
            case "retained_contact_service_attribute_mask":
            case "retained_host_attribute_mask":
            case "retained_service_attribute_mask":
            case "retained_process_host_attribute_mask":
            case "retained_process_service_attribute_mask":
            case "translate_passive_host_checks":
            case "use_aggressive_host_checking":
            case "use_embedded_perl_implicitly":
            case "use_regexp_matching":
            case "use_true_regexp_matching":
            case "process_performance_data":
                validateBinary($app, $deployment, $key, $nagiosConfigInfo[$key]); break;
            case "host_perfdata_file_processing_interval":
            case "service_perfdata_file_processing_interval":
            case "max_check_result_file_age":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 86400); break;
            case "cached_host_check_horizon":
            case "cached_service_check_horizon":
            case "host_freshness_check_interval":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 3600); break;
            case "check_result_reaper_frequency":
            case "event_handler_timeout":
            case "host_check_timeout":
            case "max_check_result_reaper_time":
            case "notification_timeout":
            case "retention_update_interval":
            case "service_check_timeout":
            case "service_freshness_check_interval":
            case "status_update_interval":
            case "additional_freshness_latency":
            case "auto_rescheduling_interval":
            case "interval_length":
            case "ocsp_timeout":
            case "ochp_timeout":
            case "perfdata_timeout":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 300); break;
            case "auto_rescheduling_window":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 900); break;
            case "command_check_interval":
                validateForbiddenChars($app, $deployment, '/[^\dhsm\-]/s', $key, $nagiosConfigInfo[$key]); break;
            case "event_broker_options":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], -1, 1048575); break;
            case "external_command_buffer_slots":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 1048575); break;
            case "log_rotation_method":
                validateOptions($app, $deployment, $key, $nagiosConfigInfo[$key], array('n','h','d','w','m')); break;
            case "nagios_user":
            case "nagios_group":
                validateForbiddenChars($app, $deployment, '/[^\w\d]/s', $key, $nagiosConfigInfo[$key]); break;
            case "admin_email":
            case "admin_pager":
                validateEmail($app, $deployment, $key, $nagiosConfigInfo[$key]); break;
            case "date_format":
                validateForbiddenChars($app, $deployment, '/[^\w]/s', $key, $nagiosConfigInfo[$key]); break;
            case "debug_level":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], -1, 4098); break;
            case "debug_verbosity":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 2); break;
            case "high_host_flap_threshold":
            case "high_service_flap_threshold":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 1, 100); break;
            case "low_host_flap_threshold":
            case "low_service_flap_threshold":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 99); break;
            case "host_inter_check_delay_method":
            case "service_inter_check_delay_method":
                validateForbiddenChars($app, $deployment, '/[^\dnds]/s', $key, $nagiosConfigInfo[$key]); break;
            case "max_host_check_spread":
            case "max_service_check_spread":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 1, 1440); break;
            case "max_debug_file_size":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 1, 104857600); break;
            case "service_interleave_factor":
                validateForbiddenChars($app, $deployment, '/[^\ds]/s', $key, $nagiosConfigInfo[$key]); break;
            case "sleep_time":
                validateInterval($app, $deployment, $key, $nagiosConfigInfo[$key], 0, 5); break;
            case "host_perfdata_file_mode":
            case "service_perfdata_file_mode":
                validateOptions($app, $deployment, $key, $nagiosConfigInfo[$key], array('w','a','p')); break;
            default:
                break;
        }
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNagiosCfg($deployment, $deployRev) === true) {
        RevDeploy::modifyDeploymentNagiosCfg($deployment, $nagiosConfigInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Nagios Config"
        );
    }
    else {
        RevDeploy::createDeploymentNagiosCfg($deployment, $nagiosConfigInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Nagios Config"
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-nagios-config');

$app->delete('/sapi/configs/:deployment/nagios', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    RevDeploy::deleteDeploymentNagiosCfg($deployment, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Nagios Config");
    $apiResponse->printJson();
})->name('saigon-api-delete-nagios-config');

