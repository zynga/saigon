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
                $nodeTemplateInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev);
                if ((isset($nodeTemplateInfo['services'])) && (!empty($nodeTemplateInfo['services']))
                    && (preg_match('/,/', $nodeTemplateInfo['services']))) {
                        $nodeTemplateInfo['services'] = preg_split('/\s?,\s?/', $nodeTemplateInfo['services']);
                }
                elseif (empty($nodeTemplateInfo['services'])) {
                    // Do Nothing
                } 
                else {
                    $nodeTemplateInfo['services'] = array($nodeTemplateInfo['services']);
                }
                $results[$tmptemplate] = $nodeTemplateInfo;
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
            $nodeTemplateInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $template, $deployRev);
            if ((isset($nodeTemplateInfo['services'])) && (!empty($nodeTemplateInfo['services']))
                && (preg_match('/,/', $nodeTemplateInfo['services']))) {
                    $nodeTemplateInfo['services'] = preg_split('/\s?,\s?/', $nodeTemplateInfo['services']);
            }
            else {
                $nodeTemplateInfo['services'] = array($nodeTemplateInfo['services']);
            }
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('template', $nodeTemplateInfo);
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
                "Unable to detect any service or host template to apply to Standard Node Template"
            );
            $apiResponse->setDeployment($deployment);
            $app->halt(404, $apiResponse->returnJson());
    }
    if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
        if (preg_match('/,/', $templateInfo['services'])) {
            $templateInfo['services'] = preg_replace('/\s?,\s?/', ',', $templateInfo['services']);
        }
        elseif (is_array($templateInfo['services'])) {
            $templateInfo['services'] = implode(',', $templateInfo['services']);
        }
    }
    else {
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
                    $nodeTemplateInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $tmptemplate, $deployRev);
                    if ((isset($nodeTemplateInfo['services'])) && (!empty($nodeTemplateInfo['services']))
                        && (preg_match('/,/', $nodeTemplateInfo['services']))) {
                            $nodeTemplateInfo['services'] = preg_split('/\s?,\s?/', $nodeTemplateInfo['services']);
                    }
                    elseif (empty($nodeTemplateInfo['services'])) {
                        // Do Nothing
                    } 
                    else {
                        $nodeTemplateInfo['services'] = array($nodeTemplateInfo['services']);
                    }
                    if ((isset($nodeTemplateInfo['nservices'])) && (!empty($nodeTemplateInfo['nservices']))
                        && (preg_match('/,/', $nodeTemplateInfo['nservices']))) {
                            $nodeTemplateInfo['nservices'] = preg_split('/\s?,\s?/', $nodeTemplateInfo['nservices']);
                    }
                    elseif (empty($nodeTemplateInfo['nservices'])) {
                        // Do Nothing
                    } 
                    else {
                        $nodeTemplateInfo['nservices'] = array($nodeTemplateInfo['nservices']);
                    }
                    $results[$tmptemplate] = $nodeTemplateInfo;
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
                $nodeTemplateInfo = RevDeploy::getDeploymentNodeTemplate($deployment, $template, $deployRev);
                if ((isset($nodeTemplateInfo['services'])) && (!empty($nodeTemplateInfo['services']))
                    && (preg_match('/,/', $nodeTemplateInfo['services']))) {
                        $nodeTemplateInfo['services'] = preg_split('/\s?,\s?/', $nodeTemplateInfo['services']);
                }
                elseif (empty($nodeTemplateInfo['services'])) {
                    // Do Nothing
                } 
                else {
                    $nodeTemplateInfo['services'] = array($nodeTemplateInfo['services']);
                }
                if ((isset($nodeTemplateInfo['nservices'])) && (!empty($nodeTemplateInfo['nservices']))
                    && (preg_match('/,/', $nodeTemplateInfo['nservices']))) {
                        $nodeTemplateInfo['nservices'] = preg_split('/\s?,\s?/', $nodeTemplateInfo['nservices']);
                }
                elseif (empty($nodeTemplateInfo['nservices'])) {
                    // Do Nothing
                } 
                else {
                    $nodeTemplateInfo['nservices'] = array($nodeTemplateInfo['nservices']);
                }
            }
            else {
                $nodeTemplateInfo = RevDeploy::getDeploymentNodeTemplatewInfo($deployment, $template, $deployRev, true);
            }
            $apiResponse = new APIViewData(0, $deployment, false);
            $apiResponse->setExtraResponseData('template', $nodeTemplateInfo);
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
        $templateInfo['subdeployment'] = $request->post('subdeployment');
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
    if (((!isset($templateInfo['services'])) || (empty($templateInfo['services']))) &&
        ((!isset($templateInfo['hosttemplate'])) || (empty($templateInfo['hosttemplate']))) && 
        ((!isset($templateInfo['stdtemplate'])) || (empty($templateInfo['stdtemplate']))) && 
        ((!isset($templateInfo['hostgroup'])) || (empty($templateInfo['hostgroup'])))) {
            $apiResponse = new APIViewData(1, $deployment,
                "Unable to detect any appropriate parameter to apply to template ( services, hosttemplate, stdtemplate, hostgroup )"
            );
            $app->halt(404, $apiResponse->returnJson());
    }
    if ((isset($templateInfo['services'])) && (!empty($templateInfo['services']))) {
        if (preg_match('/,/', $templateInfo['services'])) {
            $templateInfo['services'] = preg_replace('/\s?,\s?/', ',', $templateInfo['services']);
        }
        elseif (is_array($templateInfo['services'])) {
            $templateInfo['services'] = implode(',', $templateInfo['services']);
        }
    }
    else {
        unset($templateInfo['services']);
    }
    if ((isset($templateInfo['nservices'])) && (!empty($templateInfo['nservices']))) {
        if (preg_match('/,/', $templateInfo['nservices'])) {
            $templateInfo['nservices'] = preg_replace('/\s?,\s?/', ',', $templateInfo['nservices']);
        }
        elseif (is_array($templateInfo['nservices'])) {
            $templateInfo['nservices'] = implode(',', $templateInfo['nservices']);
        }
    }
    else {
        unset($templateInfo['nservices']);
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
    if ((!isset($templateInfo['subdeployment'])) || (empty($templateInfo['subdeployment']))) {
        unset($templateInfo['subdeployment']);
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

