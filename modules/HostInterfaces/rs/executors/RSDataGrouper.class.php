<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class RSDataGrouper extends RSObserver {

    public function execute($response) {
        $jsonObj = json_decode($response);
        if (!is_array($jsonObj)) return;
        foreach ($jsonObj as $tmpObj) {
            RSDataGrouperCache::addItem($tmpObj);
        }
    }

}
