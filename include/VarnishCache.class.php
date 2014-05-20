<?php
//
// Copyright (c) 2014, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class VarnishCache
{

    public static function invalidate($deployment, $type = false, array $urls = array())
    {
        // Return immediately if the varnish cache module is disabled
        if (VARNISH_CACHE_ENABLED !== true) {
            return;
        }
        elseif ($type === false) {
            $type = 'purge';
        }
        elseif ( ($type != 'purge') || ($type != 'ban') ) {
            return;
        }
        // Build array for host(s) we may need to contact
        $vcHosts = array();
        if (preg_match("/,/", VARNISH_CACHE_HOSTS)) {
            $vcHosts = preg_split("/\s?,\s?/", VARNISH_CACHE_HOSTS);
        }
        else {
            $host = VARNISH_CACHE_HOSTS;
            array_push($vcHosts, $host);
        }
        // We got fed the urls to call up
        if (!empty($urls)) {
            $urlCount = count($urls);
            $hostCount = count($vcHosts);
            if ($urlCount > 1) {
                self::multiCurl($vcHosts, $type, $urls);
            }
            elseif ( ($urlCount == 1) && ($hostCount > 1) ) {
                self::multiCurl($vcHosts, $type, $urls);
            }
            else {
                self::singleCurl($vcHosts[0], $type, $urls[0]);
            }
        }
        else {
            $urls = array(
                'purge' => array(
                    '/api/getMGCfg/','/api/getNRPECfg/','/api/getNagiosCfg/',
                    '/api/getSupNRPECfg/','/api/getNagiosPlugins/',
                    '/sapi/consumer/nrpeconfig/','/sapi/consumer/supnrpeconfig/',
                    '/sapi/consumer/nagiosplugins/','/sapi/consumer/modgearmanconfig/',
                ),
                'ban' => array(
                    '/api/getNagiosCfg/','/api/getNRPEPlugin/',
                    '/api/getSupNRPEPlugin/','/api/getNagiosPlugin/',
                    '/sapi/consumer/saigoninfo/','/sapi/consumer/nrpeplugin/',
                    '/sapi/consumer/supnrpeplugin/','/sapi/consumer/nagiosplugin/',
                ),
            );
            foreach( array('purge', 'ban') as $key) {
                $formattedUrls = self::buildFormattedUrls($deployment, $key, $urls[$key]);
                self::multiCurl($vcHosts, $key, $formattedUrls);
            }
        }
        return;
    }

    private static function buildFormattedUrls($deployment, $type, array $urls = array())
    {
        $returnUrls = array();
        foreach ($urls as $url) {
            if ( $type == 'purge' ) {
                array_push($returnUrls, $url . $deployment);
            }
            else {
                array_push($returnUrls, $url . $deployment . "/");
            }
        }
        return $returnUrls;
    }

    private static function multiCurl($vcHosts, $type, array $urls = array())
    {
        $interface = NagMisc::getInterface();
        $hostCount = count($vcHosts);
        $urlCount = count($urls);
        $type = strtoupper($type);
        if ($hostCount > 1) {
            // We are going to invalidate each url on all of the hosts at the same time
            foreach($urls as $urlid => $tmpUrl) {
                $curls = array();
                $running = null;
                $curl_mh = curl_multi_init();

                foreach($vcHosts as $id => $host) {
                    $curls[$id] = curl_init();
                    $fullUrl = "http://" . $host . $tmpUrl;
                    curl_setopt($curls[$id], CURLOPT_HTTPHEADER, array('Host: '. VARNISH_CACHE_HOSTNAME));
                    curl_setopt($curls[$id], CURLOPT_NOBODY, true);
                    curl_setopt($curls[$id], CURLOPT_CUSTOMREQUEST, $type);
                    curl_setopt($curls[$id], CURLOPT_URL, $fullUrl);
                    curl_setopt($curls[$id], CURLOPT_USERAGENT, 'Saigon Cache Invalidator/'.VERSION."/$interface");
                    curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curls[$id], CURLOPT_TIMEOUT, 10);
                    curl_multi_add_handle($curl_mh, $curls[$id]);
                }

                do {
                    curl_multi_exec($curl_mh, $running);
                } while ($running > 0);

                foreach($curls as $id => $conn) {
                    curl_multi_remove_handle($curl_mh, $conn);
                }
            
                curl_multi_close($curl_mh);
            }
        }
        else {
            // We are going to invalidate all the urls at the same time
            $curls = array();
            $running = null;
            $curl_mh = curl_multi_init();

            foreach($urls as $id => $tmpUrl) {
                $curls[$id] = curl_init();
                $fullUrl = "http://" . $vcHosts[0] . $tmpUrl;
                curl_setopt($curls[$id], CURLOPT_HTTPHEADER, array('Host: '. VARNISH_CACHE_HOSTNAME));
                curl_setopt($curls[$id], CURLOPT_NOBODY, true);
                curl_setopt($curls[$id], CURLOPT_CUSTOMREQUEST, $type);
                curl_setopt($curls[$id], CURLOPT_URL, $fullUrl);
                curl_setopt($curls[$id], CURLOPT_USERAGENT, 'Saigon Cache Invalidator/'.VERSION."/$interface");
                curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curls[$id], CURLOPT_TIMEOUT, 10);
                curl_multi_add_handle($curl_mh, $curls[$id]);
            }

            do {
                curl_multi_exec($curl_mh, $running);
            } while ($running > 0);

            foreach($curls as $id => $conn) {
                curl_multi_remove_handle($curl_mh, $conn);
            }
            
            curl_multi_close($curl_mh);
        }
        return;
    }

    private static function singleCurl($host, $type, $url)
    {
        $interface = NagMisc::getInterface();
        $fullUrl = "http://" . $host. $url;
        $type = strtoupper($type);
        /* Initialize Curl and Issue Request */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: '. VARNISH_CACHE_HOSTNAME));
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_URL, $fullUrl);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Saigon Cache Invalidator/'.VERSION."/$interface");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        /* Response or No Response ? */
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
            curl_close($ch);
        } else {
            curl_close($ch);
        }
        return true;
    }

}
