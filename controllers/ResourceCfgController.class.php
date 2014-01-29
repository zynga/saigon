<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ResourceCfgController extends Controller
{

    /**
     * stage - load up resource config stage view
     * 
     * @access public
     * @return void
     */
    public function stage()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('resource_cfg_error');
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        if (RevDeploy::existsDeploymentResourceCfg($deployment, $modrevision) === true) {
            $viewData->rcfg = RevDeploy::getDeploymentResourceCfg($deployment, $modrevision);
        } else {
            $viewData->rcfg = array('USER1' => 'L3Vzci9sb2NhbC9uYWdpb3MvbGliZXhlYw==');
        }
        $viewData->deployment = $deployment;
        $this->sendResponse('resource_cfg_stage', $viewData);
    }

    /**
     * write - write resource config information to datastore 
     * 
     * @access public
     * @return void
     */
    public function write()
    {
        $viewData = new ViewData();
        $deployment = $this->getDeployment('resource_cfg_error');
        $this->checkGroupAuth($deployment);
        $this->checkDeploymentRevStatus($deployment);
        $modrevision = RevDeploy::getDeploymentNextRev($deployment);
        $cfgDelete = $this->getParam('delete');
        if ($cfgDelete == 1) {
            RevDeploy::deleteDeploymentResourceCfg($deployment, $modrevision);
            $viewData->deployment = $deployment;
            $this->sendResponse('resource_cfg_delete', $viewData);
        }
        $resources = array();
        for ($i=1; $i<=32; $i++) {
            $key = "USER" . $i;
            $value = $this->getParam($key);
            if ($value !== false) {
                $resources[$key] = base64_encode($value);
            }
        }
        RevDeploy::writeDeploymentResourceCfg($deployment, $resources, $modrevision);
        $viewData->deployment = $deployment;
        $this->sendResponse('resource_cfg_write', $viewData);
    }

}

