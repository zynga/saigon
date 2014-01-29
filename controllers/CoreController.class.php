<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CoreController extends Controller
{

    /**
     * input - display input selection section with deployments user
     *      has access too.
     * 
     * @access public
     * @return void
     */
    public function input()
    {
        $viewData = new ViewData();
        $deployments = RevDeploy::getDeployments();
        if (empty($deployments)) {
            RevDeploy::createCommonDeployment();
            $viewDeployments = RevDeploy::getDeployments();
        } else {
            $viewDeployments = array();
            foreach ($deployments as $deployment) {
                if (($return = $this->checkGroupAuth($deployment, true)) === true) {
                    array_push($viewDeployments, $deployment);
                }
            }
        }
        asort($viewDeployments);
        $viewData->deployments = $viewDeployments;
        $viewData->superuser = $this->checkGroupAuth(SUPERMEN, true);
        $this->sendResponse('site_input', $viewData);
    }

    /**
     * menu - display menu of site based on deployment specified
     * 
     * @access public
     * @return void
     */
    public function menu()
    {
        $viewData = new ViewData();
        $deployment = $this->getParam('deployment');
        if ($deployment === false) {
            $viewData->header = $this->getErrorHeader('site_error');
            $viewData->error = 'Unable to detect deployment to present menu for selected option '.$deployment;
            $this->sendError('generic_error', $viewData);
        } else if ($deployment == '----') {
            exit();
        } else if ($deployment == 'common') {
            if (($return = $this->checkGroupAuth(SUPERMEN, true)) === false) {
                $viewData->header = $this->getErrorHeader('site_error');
                $viewData->error = 'Access Prohibited: Unable to display deployment information for common.';
                $this->sendError('generic_error', $viewData);
            }
        } else {
            if (($return = $this->checkGroupAuth($deployment, true)) === false) {
                $viewData->header = $this->getErrorHeader('site_error');
                $viewData->error = 'Access Prohibited: Unable to display deployment information for '.$deployment;
                $this->sendError('generic_error', $viewData);
            }
        }
        $viewData->superuser = $this->checkGroupAuth(SUPERMEN, true);
        $viewData->deployment = $deployment;
        $viewData->deploysettings = RevDeploy::getDeploymentMiscSettings($deployment);
        $this->sendResponse('site_menu', $viewData);
    }

    /**
     * getheader - display header of site 
     * 
     * @access public
     * @return void
     */
    public function getheader()
    {
        $viewData = new ViewData();
        $this->sendResponse('site_header', $viewData);
    }

}

