<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

// Requires for includes of functions and definitions 
require_once dirname(dirname(__FILE__)).'/conf/saigon.inc.php';
// Lets load up the composer autoloader
require_once BASE_PATH. '/vendor/autoload.php';
// Lets load up the saigon autoloader
require_once BASE_PATH.'/lib/classLoader.class.php';
Saigon_ClassLoader::register();
// Kick the tires and light the fires
Controller::route();

