<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Contact Template Routes
 */

function contacttemplate_rkeyMessage ($rkey) {
    switch($rkey) {
        case "host_notifications_enabled":
            return "(enable alerts for host problems)"; break;
        case "service_notifications_enabled":
            return "(enable alerts for service problems)"; break;
        case "host_notification_period":
            return "(time period for alerting about host problems)"; break;
        case "service_notification_period":
            return "(time period for alerting about service problems)"; break;
        case "host_notification_options":
            return "(host notification alerting options (d,u,r,s,n))"; break;
        case "service_notification_options":
            return "(service notification alerting options (w,u,c,r,s,n))"; break;
        case "host_notification_commands":
            return "(command to run for sending host notifications)"; break;
        case "service_notification_commands":
            return "(command to run for sending service notifications)"; break;
        default:
            break;
    }
}

function contacttemplate_validate ($app, $deployment, $contactTemplateInfo) {
    foreach ($contactTemplateInfo as $key => $value) {
        switch ($key) {
            case "use":
            case "host_notification_period":
            case "service_notification_period":
            case "host_notification_commands":
            case "service_notification_commands":
                validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
            case "retain_status_information":
            case "retain_nonstatus_information":
            case "host_notifications_enabled":
            case "service_notifications_enabled":
            case "can_submit_commands":
                validateBinary($app, $deployment, $key, $value); break;
            case "host_notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('d','u','r','s','n'), true);
                $contactTemplateInfo[$key] = $opts;
                break;
            case "service_notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('w','u','c','r','s','n'), true);
                $contactTemplateInfo[$key] = $opts;
                break;
            default:
                break;
        }
    }
    // Don't register since we are a template
    $contactTemplateInfo['register'] = 0;
    return $contactTemplateInfo;
}

$app->get('/sapi/contacttemplates/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('contact_templates',
            RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('contact_templates',
            RevDeploy::getDeploymentContactTemplateswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-contact-templates');

$app->get('/sapi/contacttemplate/:deployment/:contacttemplate(/:staged)', function ($deployment, $contacttemplate, $staged = false) use ($app) {
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
    if (preg_match('/,/', $contacttemplate)) {
        $contacttemplates = preg_split('/\s?,\s?/', $contacttemplate);
        $results = array();
        foreach ($contacttemplates as $cttemp) {
            if (RevDeploy::existsDeploymentContactTemplate($deployment, $cttemp, $deployRev) === true) {
                $results[$cttemp] = RevDeploy::getDeploymentContactTemplate($deployment, $cttemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect contact templates specified: $contacttemplate");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('contact_templates', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentContactTemplate($deployment, $contacttemplate, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect contact template specified: $contacttemplate");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $contactTemplateInfo = RevDeploy::getDeploymentContactTemplate($deployment, $contacttemplate, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('contact_template', $contactTemplateInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-contact-template');

$app->post('/sapi/contacttemplate/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $contactTemplateInfo = $request->getBody();
        $contactTemplateInfo = json_decode($contactTemplateInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            'name','alias','use','retain_status_information','retain_nonstatus_information',
            'host_notifications_enabled','service_notifications_enabled','host_notification_period',
            'service_notification_period','host_notification_options','service_notification_options',
            'host_notification_commands','service_notification_commands','can_submit_commands'
        );
        $contactTemplateInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $contactTemplateInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'host_notifications_enabled','service_notifications_enabled','host_notification_period',
        'service_notification_period','host_notification_options','service_notification_options',
        'host_notification_commands','service_notification_commands'
    );
    if (empty($contactTemplateInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any contact template information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($contactTemplateInfo['name'])) || (empty($contactTemplateInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $contactTemplateInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use contact template name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($contactTemplateInfo['alias'])) || (empty($contactTemplateInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about contact, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $contactTemplateInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use contact template alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // You get a pass if you have the use template key defined...
    if ((!isset($contactTemplateInfo['use'])) || (empty($contactTemplateInfo['use']))) {
        // Lets make sure we have the minimum required keys for defining a contact template
        foreach ($required_keys as $rkey) {
            if ((!isset($contactTemplateInfo[$rkey])) || (empty($contactTemplateInfo[$rkey]))) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect required parameter $rkey " . contacttemplate_rkeyMessage($rkey)
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    $contactTemplateInfo = contacttemplate_validate($app, $deployment, $contactTemplateInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentContactTemplate($deployment, $contactTemplateInfo['name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentContactTemplate($deployment, $contactTemplateInfo['name'], $contactTemplateInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Contact Template " . $contactTemplateInfo['name']
        );
    }
    else {
        RevDeploy::createDeploymentContactTemplate($deployment, $contactTemplateInfo['name'], $contactTemplateInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Contact Template " . $contactTemplateInfo['name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-contact-template');

$app->delete('/sapi/contacttemplate/:deployment/:contacttemplate', function($deployment, $contacttemplate) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $contacttemplate)) {
        $contacttemplates = preg_split('/\s?,\s?/', $contacttemplate);
        foreach ($contacttemplates as $cttemp) {
            RevDeploy::deleteDeploymentContactTemplate($deployment, $cttemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentContactTemplate($deployment, $contacttemplate, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Contact Template(s): $contacttemplate");
    $apiResponse->printJson();
})->name('saigon-api-delete-contact-template');

