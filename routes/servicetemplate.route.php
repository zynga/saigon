<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Service Template Routes
 */

function servicetemplate_rkeyMessage ($rkey) {
    switch($rkey) {
        case "max_check_attempts":
            return "(max number of check attempts before taking some action)"; break;
        case "check_interval":
            return "(max amount of time between checks)"; break;
        case "retry_interval":
            return "(max amount of time between checks in a non-OK state)"; break;
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

function servicetemplate_validate ($app, $deployment, $serviceTemplateInfo) {
    foreach ($serviceTemplateInfo as $key => $value) {
        switch ($key) {
            case "use":
            case "check_period":
            case "event_handler":
            case "notification_period":
            case "icon_image":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "servicegroups":
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value);
                    }
                    break;
                }
                else {
                    validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
                }
            case "is_volatile":
            case "active_checks_enabled":
            case "passive_checks_enabled":
            case "obsess_over_service":
            case "check_freshness":
            case "event_handler_enabled":
            case "flap_detection_enabled":
            case "parallelize_check":
            case "process_perf_data":
            case "retain_status_information":
            case "retain_nonstatus_information":
            case "notifications_enabled":
                validateBinary($app, $deployment, $key, $value); break;
            case "check_command":
                validateForbiddenChars($app, $deployment, '/[^\w.-$\/]/s', $key, $value); break;
            case "initial_state":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','u','c'), true);
                $serviceTemplateInfo[$key] = $opts;
                break;
            case "max_check_attempts":
                validateInterval($app, $deployment, $key, $value, 1, 20); break;
            case "check_interval":
                validateInterval($app, $deployment, $key, $value, 1, 1440); break;
            case "retry_interval":
                validateInterval($app, $deployment, $key, $value, 1, 720); break;
            case "freshness_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 86400); break;
            case "low_flap_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 99); break;
            case "high_flap_threshold":
                validateInterval($app, $deployment, $key, $value, 0, 100); break;
            case "flap_detection_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','c','u'), true);
                $serviceTemplateInfo[$key] = $opts;
                break;
            case "notification_interval":
            case "first_notification_delay":
                validateInterval($app, $deployment, $key, $value, 0, 1440); break;
            case "notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('w','u','c','r','f','s'), true);
                $serviceTemplateInfo[$key] = $opts;
                break;
            case "contacts":
            case "contact_groups":
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $subvalue);
                    }
                    break;
                }
                else {
                    validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
                }
            case "stalking_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('o','w','u','c'), true);
                $serviceTemplateInfo[$key] = $opts;
                break;
            case "icon_image_alt":
                validateForbiddenChars($app, $deployment, '/[^\w.-\s]/s', $key, $value); break;
            default:
                break;
        }
    }
    // We never want to see single threaded checks running, force this...
    if ((!isset($serviceTemplateInfo['parallelize_check'])) || (empty($serviceTemplateInfo['parallelize_check']))) {
        $serviceTemplateInfo['parallelize_check'] = 1;
    }
    // Don't register since we are a template
    $serviceTemplateInfo['register'] = 0;
    return $serviceTemplateInfo;
}

$app->get('/sapi/servicetemplates/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('service_templates',
            RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('service_templates',
            RevDeploy::getDeploymentSvcTemplateswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-templates');

$app->get('/sapi/servicetemplate/:deployment/:servicetemplate(/:staged)', function ($deployment, $servicetemplate, $staged = false) use ($app) {
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
    if (preg_match('/,/', $servicetemplate)) {
        $servicetemplates = preg_split('/\s?,\s?/', $servicetemplate);
        $results = array();
        foreach ($servicetemplates as $stemp) {
            if (RevDeploy::existsDeploymentSvcTemplate($deployment, $stemp, $deployRev) === true) {
                $results[$stemp] = RevDeploy::getDeploymentSvcTemplate($deployment, $stemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service templates specified: $servicetemplate");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('service_templates', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentSvcTemplate($deployment, $servicetemplate, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service template specified: $servicetemplate");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $serviceTemplateInfo = RevDeploy::getDeploymentSvcTemplate($deployment, $servicetemplate, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('service_template', $serviceTemplateInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-service-template');

$app->post('/sapi/servicetemplate/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $serviceTemplateInfo = $request->getBody();
        $serviceTemplateInfo = json_decode($serviceTemplateInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','alias','use','servicegroups','is_volatile','check_command','initial_state','max_check_attempts',
            'check_interval','retry_interval','active_checks_enabled','passive_checks_enabled','check_period',
            'obsess_over_service','check_freshness','freshness_threshold','event_handler','event_handler_enabled',
            'low_flap_threshold','high_flap_threshold','flap_detection_enabled','flap_detection_options','process_perf_data',
            'retain_status_information','retain_nonstatus_information','notification_interval','first_notification_delay',
            'notification_period','notification_options','notifications_enabled','contacts','contact_groups',
            'stalking_options','icon_image','icon_image_alt','parallelize_check'
        );
        $serviceTemplateInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $serviceTemplateInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'max_check_attempts','check_interval', 'retry_interval',
        'check_period','notification_interval','notification_period'
    );
    if (empty($serviceTemplateInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any service template information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($serviceTemplateInfo['name'])) || (empty($serviceTemplateInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $serviceTemplateInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service template name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($serviceTemplateInfo['alias'])) || (empty($serviceTemplateInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about service, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $serviceTemplateInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use service template alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // You get a pass if you have the use template key defined...
    if ((!isset($serviceTemplateInfo['use'])) || (empty($serviceTemplateInfo['use']))) {
        // Lets make sure we have the minimum required keys for defining a service template
        validateContacts($app, $deployment, $serviceTemplateInfo);
        foreach ($required_keys as $rkey) {
            if ((!isset($serviceTemplateInfo[$rkey])) || (empty($serviceTemplateInfo[$rkey]))) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect required parameter $rkey " . servicetemplate_rkeyMessage($rkey)
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    $serviceTemplateInfo = servicetemplate_validate($app, $deployment, $serviceTemplateInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentSvcTemplate($deployment, $serviceTemplateInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentSvcTemplate($deployment, $serviceTemplateInfo['name'], $serviceTemplateInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Service Template " . $serviceTemplateInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentSvcTemplate($deployment, $serviceTemplateInfo['name'], $serviceTemplateInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Service Template " . $serviceTemplateInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-service-template');

$app->delete('/sapi/servicetemplate/:deployment/:servicetemplate', function($deployment, $servicetemplate) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $servicetemplate)) {
        $servicetemplates = preg_split('/\s?,\s?/', $servicetemplate);
        foreach ($servicetemplates as $stemp) {
            RevDeploy::deleteDeploymentSvcTemplate($deployment, $stemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentSvcTemplate($deployment, $servicetemplate, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Service Template(s): $servicetemplate");
    $apiResponse->printJson();
})->name('saigon-api-delete-service-template');

