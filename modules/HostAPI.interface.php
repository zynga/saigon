<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

interface HostAPI
{
    // Return null if function is not used in your class 
    public function getList();
    public function getInput();
    public function getSearchResults($input);
}

