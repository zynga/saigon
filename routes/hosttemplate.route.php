<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Host Template Routes
 */

function hosttemplate_rkeyMessage ($rkey) {
    switch($rkey) {
        case "max_check_attempts":
            return "(max number of check attempts before taking some action)"; break;
        case "check_period":
            return "(timeperiod to use for checking this host)"; break;
        case "notification_interval":
            return "(max amount of time between sending of notifications)"; break;
        case "notification_period":
            return "(timeperiod to use for sending notifications about this host)"; break;
        default:
            break;
    }
}

function hosttemplate_validate ($app, $deployment, $hostTemplateInfo) {
    foreach ($hostTemplateInfo as $key => $value) {
        switch ($key) {
            case "check_command":
                validateForbiddenChars($app, $deployment, '/[^\w.-$\/]/s', $key, $value); break;
            case "initial_state":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','d','u'), true);
                $hostTemplateInfo[$key] = $opts;
                break;
            case "max_check_attempts":
                validateInterval($app, $deployment, $key, $value, 1, 20); break;
            case "check_interval":
            case "notification_interval":
            case "first_notification_delay":
                validateInterval($app, $deployment, $key, $value, 1, 1440); break;
            case "retry_interval":
                validateInterval($app, $deployment, $key, $value, 1, 720); break;
            case "active_checks_enabled":
            case "passive_checks_enabled":
            case "obsess_over_host":
            case "check_freshness":
            case "event_handler_enabled":
            case "flap_detection_enabled":
            case "process_perf_data":
            case "retain_status_information":
            case "retain_nonstatus_information":
            case "notifications_enabled":
                validateBinary($app, $deployment, $key, $value); break;
            case "check_period":
            case "event_handler":
            case "notification_period":
            case "icon_image":
            case "vrml_image":
            case "statusmap_image":
            case "use":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "freshness_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 86400); break;
            case "low_flap_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 99); break;
            case "high_flap_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 100); break;
            case "flap_detection_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','d','u'), true);
                $hostTemplateInfo[$key] = $opts;
                break;
            case "notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('d','u','r','f','s'), true);
                $hostTemplateInfo[$key] = $opts;
                break;
            case "stalking_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','d','u'), true);
                $hostTemplateInfo[$key] = $opts;
                break;
            case "notes_url":
            case "action_url":
                validateUrl($app, $deployment, $key, $value); break;
            case "icon_image_alt":
            case "notes":
                validateForbiddenChars($app, $deployment, '/[^\w.-\s]/s', $key, $value); break;
            case "hostgroups":
            case "parents":
                if (is_array($value)) $value = implode(',', $value);
                validateForbiddenChars($app, $deployment, '/[^\w.-,]/s', $key, $value); break;
            default:
                break;
        }
    }
    // Don't register since we are a template
    $hostTemplateInfo['register'] = 0;
    return $hostTemplateInfo;
}

$app->get('/sapi/hosttemplates/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $commonMerge = $request->get('common');
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
        $apiResponse->setExtraResponseData('host_templates',
            RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('host_templates',
            RevDeploy::getDeploymentHostTemplateswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-host-templates');

$app->get('/sapi/hosttemplate/:deployment/:hosttemplate(/:staged)', function ($deployment, $hosttemplate, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
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
    if (preg_match('/,/', $hosttemplate)) {
        $hosttemplates = preg_split('/\s?,\s?/', $hosttemplate);
        $results = array();
        foreach ($hosttemplates as $httemp) {
            if (RevDeploy::existsDeploymentHostTemplate($deployment, $httemp, $deployRev) === true) {
                $results[$httemp] = RevDeploy::getDeploymentHostTemplate($deployment, $httemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect host templates specified: $hosttemplate");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('host_templates', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentHostTemplate($deployment, $hosttemplate, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect host template specified: $hosttemplate");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $hostTemplateInfo = RevDeploy::getDeploymentHostTemplate($deployment, $hosttemplate, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('host_template', $hostTemplateInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-host-template');

$app->post('/sapi/hosttemplate/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $hostTemplateInfo = $request->getBody();
        $hostTemplateInfo = json_decode($hostTemplateInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','alias','hostgroups','check_command','initial_state','max_check_attempts','check_interval',
            'retry_interval','active_checks_enabled','passive_checks_enabled','check_period','obsess_over_host',
            'check_freshness','freshness_threshold','event_handler','event_handler_enabled','low_flap_threshold',
            'high_flap_threshold','flap_detection_enabled','flap_detection_options','process_perf_data',
            'retain_status_information','retain_nonstatus_information','contacts','contact_groups',
            'notification_interval','first_notification_delay','notification_period','notification_options',
            'notifications_enabled','stalking_options','notes','notes_url','action_url','icon_image','icon_image_alt',
            'vrml_image','statusmap_image','2d_coords','3d_coords','use','parents'
        );
        $hostTemplateInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $hostTemplateInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'max_check_attempts','check_period','notification_interval','notification_period'
    );
    if (empty($hostTemplateInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any host template information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($hostTemplateInfo['name'])) || (empty($hostTemplateInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $hostTemplateInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use host template name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($hostTemplateInfo['alias'])) || (empty($hostTemplateInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about host, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $hostTemplateInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use host template alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // You get a pass if you have the use template key defined...
    if ((!isset($hostTemplateInfo['use'])) || (empty($hostTemplateInfo['use']))) {
        // Lets make sure we have the minimum required keys for defining a host template
        validateContacts($app, $deployment, $hostTemplateInfo);
        foreach ($required_keys as $rkey) {
            if ((!isset($hostTemplateInfo[$rkey])) || (empty($hostTemplateInfo[$rkey]))) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect required parameter $rkey " . hosttemplate_rkeyMessage($rkey)
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    $hostTemplateInfo = hosttemplate_validate($app, $deployment, $hostTemplateInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentHostTemplate($deployment, $hostTemplateInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentHostTemplate($deployment, $hostTemplateInfo['name'], $hostTemplateInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Host Template " . $hostTemplateInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentHostTemplate($deployment, $hostTemplateInfo['name'], $hostTemplateInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Host Template " . $hostTemplateInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-host-template');

$app->delete('/sapi/hosttemplate/:deployment/:hosttemplate', function($deployment, $hosttemplate) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $hosttemplate)) {
        $hosttemplates = preg_split('/\s?,\s?/', $hosttemplate);
        foreach ($hosttemplates as $httemp) {
            RevDeploy::deleteDeploymentHostTemplate($deployment, $httemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentHostTemplate($deployment, $hosttemplate, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Host Template(s): $hosttemplate");
    $apiResponse->printJson();
})->name('saigon-api-delete-host-template');

