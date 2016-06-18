<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Event Creation Routes
 */

$app->map('/sapi/event/nsca/:type/:deployment', function ($type, $deployment) use ($app) {
    check_deployment_exists($app, $deployment);
    check_auth($app, $deployment);
    $request = $app->request();
    $contentType = $request->headers('Content-Type');
    if ($contentType == 'application/json') {
        $eventInfo = $request->getBody();
        $eventInfo = json_decode($eventInfo,true);
    }
    elseif (preg_match("/form-(data|urlencoded)/", $contentType)) {
        $eventInfo['host'] = $request->params('host');
        $eventInfo['exitcode'] = $request->params('exitcode');
        $eventInfo['output'] = $request->params('output');
        $eventInfo['server'] = $request->params('server');
        if ($type == 'service') {
            $eventInfo['service'] = $request->params('service');
        }
    }
    // A bit of param validation
    if ((!isset($eventInfo['host'])) || (empty($eventInfo['host']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect host parameter (host to associate service event with)");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($eventInfo['exitcode'])) || ($eventInfo['exitcode'] === false)) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect exitcode parameter (service exit state [0,1,2,3])");
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ((!isset($eventInfo['output'])) || (empty($eventInfo['output']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect output parameter (service output msg)");
        $app->halt(404, $apiResponse->returnJson());
    }
    if ($type == 'service') {
        if ((!isset($eventInfo['service'])) || (empty($eventInfo['service']))) {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect service parameter (service to associate event with)");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
    // Broken off, so we can perform proper if / else against server var...
    if ((!isset($eventInfo['server'])) || (empty($eventInfo['server']))) {
        $apiResponse = new APIViewData(1, $deployment, "Unable to detect server parameter (nagios server to submit results too)");
        $app->halt(404, $apiResponse->returnJson());
    }
    else {
        $nagiosServer = $eventInfo['server'];
    }
    $msg = $eventInfo['host'] . ",";
    if ($type == 'service') {
        $msg .= $eventInfo['service'] . ",";
    }
    $msg .= $eventInfo['exitcode'] . "," . $eventInfo['output'];
    $eventSubmission = API_EVENT_SUBMISSION;
    if ( $eventSubmission == 'enqueue' ) {
        NagPhean::init(BEANSTALKD_SERVER, 'events', true);
        NagPhean::addJob('events',
            json_encode(
                array('server' => $nagiosServer, 'type' => 'nsca', 'data' => $msg)
            ),
            1024, 0, 10
        );
        $apiResponse = new APIViewData(0, $deployment, $msg);
        $apiResponse->setExtraResponseData('eventsubmission', $eventSubmission);
        $apiResponse->printJson();
    }
    else {
        $nscabin = '/usr/sbin/send_nsca';
        if (file_exists($nscabin)) {
            if (strtolower(DIST_TYPE) == 'debian') {
                shell_exec("echo $msg | $nscabin -H $nagiosServer -d , -c /etc/send_nsca.cfg");
            }
            else {
                shell_exec("echo $msg | $nscabin -H $nagiosServer -d , -c /etc/nagios/send_nsca.cfg");
            }
            $apiResponse = new APIViewData(0, $deployment, $msg);
            $apiResponse->setExtraResponseData('eventsubmission', $eventSubmission);
            $apiResponse->printJson();
        }
        else {
            $apiResponse = new APIViewData(1, $deployment, "Unable to detect /usr/sbin/send_nsca binary needed for communication");
            $app->halt(404, $apiResponse->returnJson());
        }
    }
})->via('GET', 'POST')->name('saigon-api-create-nsca-event')->conditions(array('type' => '(host|service)'));

