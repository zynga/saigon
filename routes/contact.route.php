<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Contact Routes
 */

function contact_rkeyMessage ($rkey) {
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

function contact_validate ($app, $deployment, $contactInfo) {
    foreach ($contactInfo as $key => $value) {
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
                $contactInfo[$key] = $opts;
                break;
            case "service_notification_options":
                $opts = validateOptions($app, $deployment, $key, $value, array('w','u','c','r','s','n'), true);
                $contactInfo[$key] = $opts;
                break;
            case "email":
                validateEmail($app, $deployment, $key, $value); break;
            case "pager":
                if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                    if (!preg_match("/^(?[2-9][0-8][0-9])?-[2-9][0-0]{2}-[0-9]{4}$/", $value)) {
                        $apiResponse = new APIViewData(1, $deployment,
                            "Unable use pager number provided, the value provided doesn't match the regex for pager or email address"
                        );
                        $apiResponse->setExtraResponseData('parameter', $key);
                        $apiResponse->setExtraResponseData('parameter-value', $value);
                        $apiResponse->setExtraResponseData('parameter-pager-regex', "/^(?[2-9][0-8][0-9])?-[2-9][0-0]{2}-[0-9]{4}$/");
                        $app->halt(404, $apiResponse->returnJson());
                    }
                }
                break;
            case "contactgroups":
                if (is_array($value)) {
                    foreach ($value as $subvalue) {
                        validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $subvalue);
                    }
                    break;
                }
                else {
                    validateForbiddenChars($app, $deployment, '/[^\w.-]/s', $key, $value); break;
                }
            default:
                break;
        }
    }
    return $contactInfo;
}

$app->get('/sapi/contacts/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
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
        $apiResponse->setExtraResponseData('contacts',
            RevDeploy::getCommonMergedDeploymentContacts($deployment, $deployRev)
        );
    }
    else {
        $apiResponse->setExtraResponseData('contacts',
            RevDeploy::getDeploymentContactswInfo($deployment, $deployRev)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-contacts');

$app->get('/sapi/contact/:deployment/:contact(/:staged)', function ($deployment, $contact, $staged = false) use ($app) {
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
    if (preg_match('/,/', $contact)) {
        $contacts = preg_split('/\s?,\s?/', $contact);
        $results = array();
        foreach ($contacts as $ctemp) {
            if (RevDeploy::existsDeploymentContact($deployment, $ctemp, $deployRev) === true) {
                $results[$ctemp] = RevDeploy::getDeploymentContact($deployment, $ctemp, $deployRev);
            }
        }
        if (empty($results)) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect contact s specified: $contact");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('contacts', $results);
        }
    }
    else {
        if (RevDeploy::existsDeploymentContact($deployment, $contact, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect contact specified: $contact");
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
           $contactInfo = RevDeploy::getDeploymentContact($deployment, $contact, $deployRev);
           $apiResponse = new APIViewData(0, $deployment, false);
           $apiResponse->setExtraResponseData('contact', $contactInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-contact');

$app->post('/sapi/contact/:deployment', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $contactInfo = $request->getBody();
        $contactInfo = json_decode($contactInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $keys = array(
            // 'contactgroups'   may add in later
            'contact_name','alias','use','retain_status_information','retain_nonstatus_information',
            'host_notifications_enabled','service_notifications_enabled','host_notification_period',
            'service_notification_period','host_notification_options','service_notification_options',
            'host_notification_commands','service_notification_commands','email','pager','can_submit_commands'
        );
        $contactInfo = array();
        foreach ($keys as $key) {
            $value = $request->post($key);
            if ($value !== null) {
                $contactInfo[$key] = $value;
            }
        }
    }
    $required_keys = array(
        'host_notifications_enabled','service_notifications_enabled','host_notification_period',
        'service_notification_period','host_notification_options','service_notification_options',
        'host_notification_commands','service_notification_commands'
    );
    if (empty($contactInfo)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect any contact information to process");
        $app->halt(404, $apiResponse->returnJson());
    }
    // A bit of param validation
    if ((!isset($contactInfo['contact_name'])) || (empty($contactInfo['contact_name']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect contact_name parameter"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-]/s', $contactInfo['contact_name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use contact contact_name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($contactInfo['alias'])) || (empty($contactInfo['alias']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect alias parameter (longer human readable information about contact, one simple line)"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^\w.-\s]/s', $contactInfo['alias'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use contact alias specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    // You get a pass if you have the use template key defined...
    if ((!isset($contactInfo['use'])) || (empty($contactInfo['use']))) {
        // Lets make sure we have the minimum required keys for defining a contact 
        foreach ($required_keys as $rkey) {
            if ((!isset($contactInfo[$rkey])) || (empty($contactInfo[$rkey]))) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Unable to detect required parameter $rkey " . contact_rkeyMessage($rkey)
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
    }
    else {
        // We still need to validate that we got an email or pager for contact purposes.
        if ((isset($contactInfo['email'])) && (!empty($contactInfo['email']))) {
            validateEmail($app, $deployment, $key, $value); break;
        }
        elseif ((isset($contactInfo['pager'])) && (!empty($contactInfo['pager']))) {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                if (!preg_match("/^(?[2-9][0-8][0-9])?-[2-9][0-0]{2}-[0-9]{4}$/", $value)) {
                    $apiResponse = new APIViewData(1, $deployment,
                        "Unable use pager number provided, the value provided doesn't match the regex for pager or email address"
                    );
                    $apiResponse->setExtraResponseData('parameter', $key);
                    $apiResponse->setExtraResponseData('parameter-value', $value);
                    $apiResponse->setExtraResponseData('parameter-pager-regex', "/^(?[2-9][0-8][0-9])?-[2-9][0-0]{2}-[0-9]{4}$/");
                    $app->halt(404, $apiResponse->returnJson());
                }
            }
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect an appropriate contact parameter, email or pager"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    $contactInfo = contact_validate($app, $deployment, $contactInfo);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentContact($deployment, $contactInfo['contact_name'], $deployRev) === true) {
        RevDeploy::modifyDeploymentContact($deployment, $contactInfo['contact_name'], $contactInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Modified Contact  " . $contactInfo['contact_name']
        );
    }
    else {
        RevDeploy::createDeploymentContact($deployment, $contactInfo['contact_name'], $contactInfo, $deployRev);
        $apiResponse = new APIViewData(0, $deployment,
            "Successfully Created Contact  " . $contactInfo['contact_name']
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-create-contact');

$app->delete('/sapi/contact/:deployment/:contact', function($deployment, $contact) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $contact)) {
        $contacts = preg_split('/\s?,\s?/', $contact);
        foreach ($contacts as $ctemp) {
            RevDeploy::deleteDeploymentContact($deployment, $ctemp, $deployRev);
        }
    }
    else {
        RevDeploy::deleteDeploymentContact($deployment, $contact, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Contact(s): $contact");
    $apiResponse->printJson();
})->name('saigon-api-delete-contact');

