<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class APIViewData
{

    public $status;
    public $deployment;
    public $data;

    public function __construct($status, $deployment, $response) {
        $this->_setStatus($status);
        $this->_setDeployment($deployment);
        if ($response !== false) {
            $this->setResponse($response);
        }
    }

    private function _setStatus($status) {
        switch($status) {
            case 0:
                $this->status = "success";
                break;
            case 1:
                $this->status = "error";
                break;
            case 2:
                $this->status = "processing";
                break;
            default:
                break;
        }
    }

    private function _setDeployment($deployment) {
        if ($deployment === false) return;
        $this->deployment = $deployment;
    }

    public function printJson() {
        echo json_encode($this);
    }

    public function returnJson() {
        return json_encode($this);
    }

    public function setExtraResponseData($key, $value) {
        if (!isset($this->data)) $this->data = new stdClass;
        $this->data->$key = $value;
    }

    public function setResponse($msg) {
        $this->setExtraResponseData('response', $msg);
    }

}

