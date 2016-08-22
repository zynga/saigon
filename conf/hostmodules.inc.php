<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/**
 * Host Module Information
 **/

/**
 * Define Host API Interface Modules
 *
 * Available DEPLOYMENT_MODULES: CDCRouterVMs, CMDBDatacenters, CMDBDeployments, CMDBParentChild,
 *                               CMDBRSDeployments, CMDBRSServerArrays, PCMDBDeployments,
 *                               PCMDBNodepools, PCMDBProductAreas, PCMDBRegions,
 *                               PCMDBServiceMappings, RightScale, ZSBDeployments
 *
 * Available INPUT_MODULES: CMDBDBAGlobs, CMDBDNGlobs, CMDBGlobs, ConsulGlob, PCMDBGlob, ZSBGlobs
 *
 **/
define('DEPLOYMENT_MODULES', '');
define('INPUT_MODULES', '');

/* CMDB Auth Info / Library Requirements */
define('CMDB_USER', 'user');
define('CMDB_PASS', 'password');
define('CMDB_LOC_PREFIX', 'CMDB');
/* ZSB Auth Info / Library Requirements */
define('ZSB_USER', 'user');
define('ZSB_PASS', 'password');
define('ZSB_LOC_PREFIX', 'ZSB');
/* RightScale Auth Info / Library Requirements */
define('RS_LOC_PREFIX', 'RS');
define('RS_BASE_APIURL', "https://my.rightscale.com/api");
define('RS_REGION_MAP', "shortname:api_account_number:api_account_key");
/* Pinterest CMDB Info */
define('PCMDB_URL', 'https://some.domain.com/');
/* Consul URL Info */
define('CONSUL_URL', 'http://localhost:port');
