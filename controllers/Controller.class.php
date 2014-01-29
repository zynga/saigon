<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class Controller
{

    /**
     * __construct 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        /** Left Blank for future possible changes **/     
    }

    /**
     * getParam 
     * 
     * @param mixed $param name of parameter you want to return 
     *
     * @static
     * @access public
     * @return void
     */
    public static function getParam($param)
    {
        if (isset($_REQUEST[$param])) {
            $value = $_REQUEST[$param];
            if (is_array($value)) {
                return $value;
            }
            elseif (preg_match("/^0$/",$value)) {
                return $value;
            }
            elseif ((!empty($value)) && (strlen($value) != 0)) {
                return $value;
            }
            else {
                return false;
            }
        }
        return false;
    }

    /**
     * getRequestController 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getRequestController()
    {
        $controller = self::getParam('controller');
        //if you want your class to be publicly callable, name it fooController 
        if (!preg_match('/Controller$/i', $controller)) {
            $controller = $controller . 'Controller';
        }
        return $controller;
    }

    /**
     * getRequestAction 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getRequestAction()
    {
        $action = self::getParam('action');
        return $action;
    }

    /**
     * route 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function route()
    {
        $controller = self::getRequestController();
        if (!class_exists($controller)) {
            trigger_error("No such controller: ". self::getRequestController(), E_USER_ERROR);
        }
        $action = self::getRequestAction();
        $lifetime = 300;
        session_start();
        setcookie(session_name(), session_id(), time()+$lifetime);
        $controller = new $controller();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            trigger_error("Controller->action pair does not exist: ".self::getRequestController()."->$action", E_USER_ERROR);
        }
    }

    /**
     * sendResponse 
     * 
     * @param mixed $view     page we plan on loading up for viewing 
     * @param mixed $viewData parameters we are passing to the page we are loading
     *
     * @access public
     * @return void
     */
    public function sendResponse($view, $viewData)
    {
        ob_flush();
        include VIEW_DIRECTORY . $view . ".php";
        die();
    }

    /**
     * sendError 
     * 
     * @param mixed $view     page we plan on loading up for viewing 
     * @param mixed $viewData parameters we are passing to the page we are loading
     *
     * @access public
     * @return void
     */
    public function sendError($view, $viewData)
    {
        header('HTTP/1.1 404 Not Found');
        ob_flush();
        include VIEW_DIRECTORY . $view . ".php";
        die();
    }

    /**
     * deadResponse 
     * 
     * @access public
     * @return void
     */
    public function deadResponse()
    {
        die();
    }

    /**
     * checkGroupAuth 
     * 
     * @param mixed $deployment deployment we are checking auth for
     *
     * @access public
     * @return void
     */
    public function checkGroupAuth($deployment, $api = false)
    {
        $amodule = AUTH_MODULE;
        $authmodule = new $amodule();
        $return = $authmodule->checkAuth($deployment);
        if (($return === false) && ($api === false)) {
            $viewData = new ViewData();
            $viewData->header = $this->getErrorHeader('site_error');
            $viewData->error = 'Unable to process request; user lacks appropriate permissions to perform command';
            $this->sendError('generic_error', $viewData);
        }
        return $return;
    }

    /**
     * checkDeploymentRevStatus 
     * 
     * @param mixed $deployment deployment we are processing 
     *
     * @access public
     * @return void
     */
    public function checkDeploymentRevStatus($deployment)
    {
        $currRev = RevDeploy::getDeploymentRev($deployment);
        $nextRev = RevDeploy::getDeploymentNextRev($deployment);
        if (($currRev === false) && ($nextRev === false)) {
            return;
        }
        if ($currRev == $nextRev) {
            $incrRev = RevDeploy::incrDeploymentNextRev($deployment);
            CopyDeploy::copyDeploymentRevision($deployment, $currRev, $incrRev);
        }
    }

    /**
     * getDeployment 
     * 
     * @param mixed $redirect error page we are redirecting for 
     *
     * @access public
     * @return void
     */
    public function getDeployment($redirect = false)
    {
        $deployment = $this->getParam('deployment');
        if ($deployment === false) {
            $viewData = new ViewData();
            $viewData->error = 'Unable to determine specified deployment for processing';
            if ($redirect === false) {
                $viewData->header = $this->getErrorHeader('site_error');
                $this->sendError('generic_error', $viewData);
            } else {
                $viewData->header = $this->getErrorHeader($redirect);
                $this->sendError('generic_error', $viewData);
            }
        }
        return $deployment;
    }

    /**
     * getDeploymentsAvailToUser 
     * 
     * @access public
     * @return void
     */
    public function getDeploymentsAvailToUser()
    {
        $deployments = RevDeploy::getDeployments();
        $viewDeployments = array();
        foreach ($deployments as $deployment) {
            if (($return = $this->checkGroupAuth($deployment, true)) === true) {
                array_push($viewDeployments, $deployment);
            }
        }
        asort($viewDeployments);
        return $viewDeployments;
    }

    /**
     * getErrorHeader 
     * 
     * @param mixed $errorpage error page we are getting the header for 
     *
     * @access public
     * @return void
     */
    public function getErrorHeader($errorpage)
    {
        switch ($errorpage) {
            case "cgi_cfg_error":
                return "CGI Config Error Detected"; break;
            case "command_error":
                return "Command Error Detected"; break;
            case "contact_error":
                return "Contact Error Detected"; break;
            case "contact_group_error":
                return "Contact Group Error Detected"; break;
            case "contact_template_error":
                return "Contact Template Error Detected"; break;
            case "deployment_error":
                return "Deployment Error Detected"; break;
            case "host_group_error":
                return "Host Group Error Detected"; break;
            case "host_template_error":
                return "Host Template Error Detected"; break;
            case "modgearman_cfg_error":
                return "Modgearman Config Error Detected"; break;
            case "nagios_cfg_error":
                return "Nagios Config Error Detected"; break;
            case "nagios_plugin_error":
                return "Nagios Plugin Error Detected"; break;
            case "ngnt_error":
                return "Nagios Node Templatizer Error Detected"; break;
            case "nrpe_cfg_error":
                return "NRPE Config Error Detected"; break;
            case "nrpe_cmd_error":
                return "NRPE Command Error Detected"; break;
            case "nrpe_plugin_error":
                return "NRPE Plugin Error Detected"; break;
            case "resource_cfg_error":
                return "Resource Config Error Detected"; break;
            case "sup_nrpe_cfg_error":
                return "Supplemental NRPE Config Error Detected"; break;
            case "sup_nrpe_plugin_error":
                return "Supplemental NRPE Plugin Error Detected"; break;
            case "svc_error":
                return "Service Error Detected"; break;
            case "svc_dep_error":
                return "Service Dependency Error Detected"; break;
            case "svc_esc_error":
                return "Service Escalation Error Detected"; break;
            case "svc_group_error":
                return "Service Group Error Detected"; break;
            case "svc_template_error":
                return "Service Template Error Detected"; break;
            case "timeperiod_error":
                return "Timeperiod Error Detected"; break;
            default:
                return "Generic Site Error Detected"; break;
        }
    }

    /**
     * fetchUploadedFile 
     * 
     * @param mixed $input file input location name 
     *
     * @access public
     * @return void
     */
    public function fetchUploadedFile($input, $b64enc = false)
    {
        if ((isset($_FILES[$input]['error'])) && ($_FILES[$input]['error'] == 0)) {
            $handle = fopen($_FILES[$input]['tmp_name'], "r");
            $contents = fread($handle, filesize($_FILES[$input]['tmp_name']));
            fclose($handle);
            if ($b64enc === true) {
                return base64_encode($contents);
            }
            return $contents;
        }
        return false;
    }

}

