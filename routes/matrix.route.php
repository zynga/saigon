<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Matrix Node Mapper Routes
 */

$app->get('/sapi/matrix/:deployment/gethosts', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $regex = $request->get('regex');
    $nregex = $request->get('nregex');
    $hosts = RevDeploy::getDeploymentHosts($deployment);
    if (empty($hosts)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect or retrieve hosts, empty results set detected");
        $app->halt(404, $apiResponse->returnJson());
    }
    $globalnegate = RevDeploy::getDeploymentGlobalNegate($deployment);
    $resulthosts = array();
    if ((isset($regex)) && (!empty($regex))) {
        foreach ($hosts as $host => $hArray) {
            if (($globalnegate !== false) && (preg_match("/$globalnegate/", $host))) continue;
            if (preg_match("/$regex/", $host)) {
                if ((isset($nregex)) && (!empty($nregex))) {
                    if (!preg_match("/$nregex/", $host)) {
                        array_push($resulthosts, $host);
                    }
                } else {
                    array_push($resulthosts, $host);
                }
            }
        }
    }
    elseif ((isset($nregex)) && (!empty($nregex))) {
        foreach ($hosts as $host => $hArray) {
            if (($globalnegate !== false) && (preg_match("/$globalnegate/", $host))) continue;
            if (!preg_match("/$nregex/", $host)) {
                array_push($resulthosts, $host);
            }
        }
    }
    elseif ($globalnegate !== false) {
        foreach ($hosts as $host => $hArray) {
            if (!preg_match("/$globalnegate/", $host)) {
                array_push($resulthosts, $host);
            }
        }
    }
    else {
        $resulthosts = array_keys($hosts);
    }
    sort($resulthosts);
    $apiResponse = new APIViewData(0, $deployment, false);
    $apiResponse->setExtraResponseData('hosts', $resulthosts);
    $apiResponse->printJson();
})->name('saigon-api-get-hosts');

$app->get('/sapi/matrix/:deployment(/:staged)', function ($deployment, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $merged = $request->get('merged');
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
    if ($merged == false) {
        $apiResponse->setExtraResponseData(
            'templates',
            RevDeploy::getDeploymentNodeTemplateswInfo($deployment, $deployRev)
        );
        if ($deployment != 'common') {
            $commonRepo = RevDeploy::getDeploymentCommonRepo($deployment);
            $commonRev = RevDeploy::getDeploymentRev($commonRepo);
            $apiResponse->setExtraResponseData('common_deployment', $commonRepo);
            $apiResponse->setExtraResponseData(
                'common_templates',
                RevDeploy::getDeploymentNodeTemplateswInfo($commonRepo, $commonRev)
            );
        }
    }
    else {
        $apiResponse->setExtraResponseData(
            'templates',
            RevDeploy::getDeploymentNodeTemplateswInfo($deployment, $deployRev, true)
        );
    }
    $apiResponse->printJson();
})->name('saigon-api-get-matrix');

$app->get('/sapi/matrix/:deployment/standard/:template(/:staged)', function ($deployment, $template, $staged = false) use ($app) {
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
    if (preg_match('/,/', $template)) {
        $results = array();
        $templates = preg_split('/\s?,\s?/', $template);
        foreach ($templates as $tmptemplate) {
            if (RevDeploy::existsDeploymentStandardTemplate($deployment, $tmptemplate, $deployRev) === true) {
                $results[$tmptemplate] = RevDeploy::getDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev);
            }
        }
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('templates', $results);
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect standard templates specified: $template"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        if (RevDeploy::existsDeploymentStandardTemplate($deployment, $template, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect standard template specified: $template"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            $nodeTemplateInfo[$template] = RevDeploy::getDeploymentNodeTemplate($deployment, $template, $deployRev);
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('templates', $nodeTemplateInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-standard-template');

$app->post('/sapi/matrix/:deployment/standard', function ($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $templateInfo = $request->getBody();
        $templateInfo = json_decode($templateInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $templateInfo = array();
        $templateInfo['name'] = $request->post('name');
        $templateInfo['services'] = $request->post('services');
        $templateInfo['hosttemplate'] = $request->post('hosttemplate');
    }
    $templateInfo['type'] = 'standard';
    if ((!isset($templateInfo['name'])) || (empty($templateInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect name parameter (name to call the matrix mapping)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^a-zA-Z0-9_-]/s', $templateInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use template name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $apiResponse->setDeployment($deployment);
        $app->halt(404, $apiResponse->returnJson());
    }
    if (((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) &&
        ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate'])))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect any services or host template to apply to Standard Node Template"
            );
            $apiResponse->setDeployment($deployment);
            $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) {
        unset($templateInfo['services']);
    }
    if ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) {
        unset($templateInfo['hosttemplate']);
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNodeTemplate($deployment, $templateInfo['name'], $deployRev) === true) {
        if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $templateInfo['name'], $deployRev, $templateInfo['type']) === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Template type specified doesn't match template type stored in data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        RevDeploy::modifyDeploymentNodeTemplate($deployment, $templateInfo['name'], $templateInfo, $deployRev);
        $msg = "Successfully Modified Standard Template: " . $templateInfo['name'];
    }
    else {
        RevDeploy::createDeploymentNodeTemplate($deployment, $templateInfo['name'], $templateInfo, $deployRev);
        $msg = "Successfully Created Standard Template: " . $templateInfo['name'];
    }
    $apiResponse = new APIViewData(0, $deployment, $msg);
    $apiResponse->printJson();
})->name('saigon-api-create-standard-template');

$app->delete('/sapi/matrix/:deployment/standard/:template', function ($deployment, $template) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $template)) {
        $results = array();
        $templates = preg_split('/\s?,\s?/', $template);
        foreach ($templates as $tmptemplate) {
            if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $tmptemplate, $deployRev, 'standard') === false) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Template type specified doesn't match template type stored in data store"
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
        foreach ($templates as $tmptemplate) {
            RevDeploy::deleteDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev);
        }
    }
    else {
        if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $template, $deployRev, 'standard') === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Template type specified doesn't match template type stored in data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        RevDeploy::deleteDeploymentNodeTemplate($deployment, $template, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Standard Template(s): $template");
    $apiResponse->printJson();
})->name('saigon-api-delete-standard-template');

$app->get('/sapi/matrix/:deployment/dynamic/:template(/:staged)', function($deployment, $template, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $merged = $request->get('merged');
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
    if (preg_match('/,/', $template)) {
        $results = array();
        $templates = preg_split('/\s?,\s?/', $template);
        if ($merged == false) {
            foreach ($templates as $tmptemplate) {
                if (RevDeploy::existsDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev) === true) {
                    $results[$tmptemplate] = RevDeploy::getDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev);
                }
            }
        }
        else {
            foreach($templates as $tmptemplate) {
                $results[$tmptemplate] = RevDeploy::getDeploymentNodeTemplatewInfo($deployment, $tmptemplate, $deployRev, true);
            }
        }
        if (!empty($results)) {
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('templates', $results);
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect dynamic templates specified: $template"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    else {
        if (RevDeploy::existsDeploymentNodeTemplate($deployment, $template, $deployRev) === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect dynamic template specified: $template"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        else {
            if ($merged == false ) {
                $nodeTemplateInfo[$template] = RevDeploy::getDeploymentNodeTemplate($deployment, $template, $deployRev);
            }
            else {
                $nodeTemplateInfo[$template] = RevDeploy::getDeploymentNodeTemplatewInfo($deployment, $template, $deployRev, true);
            }
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('templates', $nodeTemplateInfo);
        }
    }
    $apiResponse->printJson();
})->name('saigon-api-get-dynamic-template');

$app->post('/sapi/matrix/:deployment/dynamic', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $templateInfo = $request->getBody();
        $templateInfo = json_decode($templateInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $templateInfo = array();
        $templateInfo['name'] = $request->post('name');
        $templateInfo['services'] = $request->post('services');
        $templateInfo['hosttemplate'] = $request->post('hosttemplate');
        $templateInfo['regex'] = $request->post('regex');
        $templateInfo['nregex'] = $request->post('nregex');
        $templateInfo['hostgroup'] = $request->post('hostgroup');
        $templateInfo['stdtemplate'] = $request->post('stdtemplate');
        $templateInfo['nservices'] = $request->post('nservices');
        $templateInfo['contacts'] = $request->post('contacts');
        $templateInfo['contactgroups'] = $request->post('contactgroups');
        $templateInfo['svctemplate'] = $request->post('svctemplate');
        $templateInfo['svcescs'] = $request->post('svcescs');
        $templateInfo['priority'] = $request->post('priority');
    }
    $templateInfo['type'] = 'dynamic';
    if ((!isset($templateInfo['name'])) || (empty($templateInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect name parameter (name to call the matrix mapping)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^a-zA-Z0-9_-]/s', $templateInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use template name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $apiResponse->setDeployment($deployment);
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($templateInfo['regex'])) || (empty($templateInfo['regex']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect regex parameter (host regex used to apply information against"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($templateInfo['priority'])) || (empty($templateInfo['priority']))) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect usable priority parameter for matrix mapping [1,2,3,4,5]"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (!preg_match('/[1-5]/',$templateInfo['priority'])) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use priority parameter specified, expecting one of the following values [1,2,3,4,5]"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    if (((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) &&
        ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) && 
        ((!isset($templateInfo['stdtemplate'])) || (empty($templateInfo['stdtemplate']))) && 
        ((!isset($templateInfo['contacts'])) || (empty($templateInfo['contacts']))) && 
        ((!isset($templateInfo['contactgroups'])) || (empty($templateInfo['contactgroups']))) && 
        ((!isset($templateInfo['svctemplate'])) || (empty($templateInfo['svctemplate']))) && 
        ((!isset($templateInfo['hostgroup'])) || (empty($templateInfo['hostgroup'])))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect any appropriate parameter to apply to template ( services, hosttemplate, stdtemplate, hostgroup, contacts, contactgroups, svctemplate )"
            );
            $app->halt(404, $apiResponse->returnJson());
    }

    if ((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) {
        unset($templateInfo['services']);
    }
    if ((!isset($templateInfo['nservices'])) || (empty($templateInfo['nservices']))) {
        unset($templateInfo['nservices']);
    }
    if ((!isset($templateInfo['contacts'])) || (empty($templateInfo['contacts']))) {
        unset($templateInfo['contacts']);
    }
    if ((!isset($templateInfo['contactgroups'])) || (empty($templateInfo['contactgroups']))) {
        unset($templateInfo['contactgroups']);
    }
    if ((!isset($templateInfo['svcescs'])) || (empty($templateInfo['svcescs']))) {
        unset($templateInfo['svcescs']);
    }
    if ((!isset($templateInfo['stdtemplate'])) || (empty($templateInfo['stdtemplate']))) {
        unset($templateInfo['stdtemplate']);
    }
    if ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) {
        unset($templateInfo['hosttemplate']);
    }
    if ((!isset($templateInfo['hostgroup'])) || (empty($templateInfo['hostgroup']))) {
        unset($templateInfo['hostgroup']);
    }
    if ((!isset($templateInfo['nregex'])) || (empty($templateInfo['nregex']))) {
        unset($templateInfo['nregex']);
    }
    if ((!isset($templateInfo['svctemplate'])) || (empty($templateInfo['svctemplate']))) {
        unset($templateInfo['svctemplate']);
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNodeTemplate($deployment, $templateInfo['name'], $deployRev) === true) {
        if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $templateInfo['name'], $deployRev, $templateInfo['type']) === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Template type specified doesn't match template type stored in data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        RevDeploy::modifyDeploymentNodeTemplate($deployment, $templateInfo['name'], $templateInfo, $deployRev);
        $msg = "Successfully Modified Dynamic Template: " . $templateInfo['name'];
    }
    else {
        RevDeploy::createDeploymentNodeTemplate($deployment, $templateInfo['name'], $templateInfo, $deployRev);
        $msg = "Successfully Created Dynamic Template: " . $templateInfo['name'];
    }
    $apiResponse = new APIViewData(0, $deployment, $msg);
    $apiResponse->printJson();
})->name('saigon-api-create-dynamic-template');

$app->delete('/sapi/matrix/:deployment/dynamic/:template', function($deployment, $template) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (preg_match('/,/', $template)) {
        $results = array();
        $templates = preg_split('/\s?,\s?/', $template);
        foreach ($templates as $tmptemplate) {
            if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $tmptemplate, $deployRev, 'dynamic') === false) {
                $apiResponse = new APIViewData(1, $deployment,
                    "Template type specified doesn't match template type stored in data store"
                );
                $app->halt(404, $apiResponse->returnJson());
            }
        }
        foreach ($templates as $tmptemplate) {
            RevDeploy::deleteDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev);
        }
    }
    else {
        if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $template, $deployRev, 'dynamic') === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Template type specified doesn't match template type stored in data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        RevDeploy::deleteDeploymentNodeTemplate($deployment, $template, $deployRev);
    }
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Dynamic Template(s): $template");
    $apiResponse->printJson();
})->name('saigon-api-delete-dynamic-template');

$app->get('/sapi/matrix/:deployment/unclassified/:template(/:staged)', function($deployment, $template, $staged = false) use ($app) {
    check_deployment_exists($app, $deployment);
    $request = $app->request();
    $merged = $request->get('merged');
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
    if (RevDeploy::existsDeploymentNodeTemplate($deployment, $template, $deployRev) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to detect unclassified template specified: $template"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        if ($merged == false ) {
            $nodeTemplateInfo[$template] = RevDeploy::getDeploymentNodeTemplate($deployment, $template, $deployRev);
        }
        else {
            $nodeTemplateInfo[$template] = RevDeploy::getDeploymentNodeTemplatewInfo($deployment, $template, $deployRev, true);
        }
        $apiResponse = new APIViewData(0, $deployment, false);
        $apiResponse->setExtraResponseData('templates', $nodeTemplateInfo);
    }
    $apiResponse->printJson();
})->name('saigon-api-get-unclassified-template');

$app->post('/sapi/matrix/:deployment/unclassified', function($deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $templateInfo = $request->getBody();
        $templateInfo = json_decode($templateInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $templateInfo = array();
        $templateInfo['name'] = $request->post('name');
        $templateInfo['services'] = $request->post('services');
        $templateInfo['hosttemplate'] = $request->post('hosttemplate');
        $templateInfo['hostgroup'] = $request->post('hostgroup');
        $templateInfo['stdtemplate'] = $request->post('stdtemplate');
        $templateInfo['nservices'] = $request->post('nservices');
        $templateInfo['contacts'] = $request->post('contacts');
        $templateInfo['contactgroups'] = $request->post('contactgroups');
        $templateInfo['svctemplate'] = $request->post('svctemplate');
        $templateInfo['svcescs'] = $request->post('svcescs');
    }
    $templateInfo['type'] = 'unclassified';
    if ((!isset($templateInfo['name'])) || (empty($templateInfo['name']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect name parameter (name to call the matrix mapping)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif (preg_match_all('/[^a-zA-Z0-9_-]/s', $templateInfo['name'], $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use template name specified, detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $apiResponse->setDeployment($deployment);
        $app->halt(404, $apiResponse->returnJson());
    }
    if (((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) &&
        ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) && 
        ((!isset($templateInfo['stdtemplate'])) || (empty($templateInfo['stdtemplate']))) && 
        ((!isset($templateInfo['contacts'])) || (empty($templateInfo['contacts']))) && 
        ((!isset($templateInfo['contactgroups'])) || (empty($templateInfo['contactgroups']))) && 
        ((!isset($templateInfo['svctemplate'])) || (empty($templateInfo['svctemplate']))) && 
        ((!isset($templateInfo['hostgroup'])) || (empty($templateInfo['hostgroup'])))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect any appropriate parameter to apply to template ( services, hosttemplate, stdtemplate, hostgroup, contacts, contactgroups, svctemplate )"
            );
            $app->halt(404, $apiResponse->returnJson());
    }
    if ((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) {
        unset($templateInfo['services']);
    }
    if ((!isset($templateInfo['nservices'])) || (empty($templateInfo['nservices']))) {
        unset($templateInfo['nservices']);
    }
    if ((!isset($templateInfo['contacts'])) || (empty($templateInfo['contacts']))) {
        unset($templateInfo['contacts']);
    }
    if ((!isset($templateInfo['contactgroups'])) || (empty($templateInfo['contactgroups']))) {
        unset($templateInfo['contactgroups']);
    }
    if ((!isset($templateInfo['svcescs'])) || (empty($templateInfo['svcescs']))) {
        unset($templateInfo['svcescs']);
    }
    if ((!isset($templateInfo['stdtemplate'])) || (empty($templateInfo['stdtemplate']))) {
        unset($templateInfo['stdtemplate']);
    }
    if ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) {
        unset($templateInfo['hosttemplate']);
    }
    if ((!isset($templateInfo['hostgroup'])) || (empty($templateInfo['hostgroup']))) {
        unset($templateInfo['hostgroup']);
    }
    if ((!isset($templateInfo['nregex'])) || (empty($templateInfo['nregex']))) {
        unset($templateInfo['nregex']);
    }
    if ((!isset($templateInfo['svctemplate'])) || (empty($templateInfo['svctemplate']))) {
        unset($templateInfo['svctemplate']);
    }
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::existsDeploymentNodeTemplate($deployment, $templateInfo['name'], $deployRev) === true) {
        if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $templateInfo['name'], $deployRev, $templateInfo['type']) === false) {
            $apiResponse = new APIViewData(1, $deployment,
                "Template type specified doesn't match template type stored in data store"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
        RevDeploy::modifyDeploymentNodeTemplate($deployment, $templateInfo['name'], $templateInfo, $deployRev);
        $msg = "Successfully Modified Unclassified Template: " . $templateInfo['name'];
    }
    else {
        if (RevDeploy::existsDeploymentUnclassifiedTemplate($deployment, $deployRev) === false) {
            RevDeploy::createDeploymentNodeTemplate($deployment, $templateInfo['name'], $templateInfo, $deployRev);
            $msg = "Successfully Created Unclassified Template: " . $templateInfo['name'];
        }
        else {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to create template specified, an Unclassified template already exists"
            );
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    $apiResponse = new APIViewData(0, $deployment, $msg);
    $apiResponse->printJson();
})->name('saigon-api-create-unclassified-template');

$app->delete('/sapi/matrix/:deployment/unclassified/:template', function($deployment, $template) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    check_revision_status($deployment);
    $deployRev = RevDeploy::getDeploymentNextRev($deployment);
    if (RevDeploy::checkDeploymentNodeTemplateType($deployment, $template, $deployRev, 'unclassified') === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Template type specified doesn't match template type stored in data store"
        );
        $app->halt(404, $apiResponse->returnJson());
    }
    RevDeploy::deleteDeploymentNodeTemplate($deployment, $template, $deployRev);
    $apiResponse = new APIViewData(0, $deployment, "Successfully Removed Unclassified Template: $template");
    $apiResponse->printJson();
})->name('saigon-api-delete-unclassified-template');

