<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class ZSBReturn extends ZSBObserver {

    public function execute($response) {
        $jsonObj = json_decode($response);
        return $jsonObj;
    }

}
