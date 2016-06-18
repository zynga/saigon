<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

/*
 * Saigon API - Index
 */

// Lets load up the composer autoloader
require_once dirname(dirname(__FILE__)).'/conf/saigon.inc.php';
require_once BASE_PATH. '/vendor/autoload.php';
// Lets load up the saigon autoloader
require_once BASE_PATH.'/lib/classLoader.class.php';
Saigon_ClassLoader::register();

// Lets start up Slim...
$app = new \Slim\Slim();

\Slim\Route::setDefaultConditions(
    array(
        'deployment' => '[a-z0-9_-]{1,}',
        'staged'     => '[1]',
        'merged'     => '[1]'
    )
);

// Internal functions
function check_auth($app, $deployment) {
    $amodule = AUTH_MODULE;
    $authmodule = new $amodule();
    $return = $authmodule->checkAuth($deployment);
    if ($return === false) {
        $apiResponse = new APIViewData(1, $deployment, "Invalid Login or Invalid Credentials Supplied");
        $app->halt(401, $apiResponse->returnJson());
    }
    return true;
}

function check_revision_status($deployment) {
    $currRev = RevDeploy::getDeploymentRev($deployment);
    $nextRev = RevDeploy::getDeploymentNextRev($deployment);
    if ($currRev == $nextRev) {
        $incrRev = RevDeploy::incrDeploymentNextRev($deployment);
        CopyDeploy::copyDeploymentRevision($deployment, $currRev, $incrRev);
    }
    return true;
}

function check_deployment_exists($app, $deployment) {
    if (RevDeploy::existsDeployment($deployment) === false) {
        $apiResponse = new APIViewData(1, false, "Unable to detect deployment specified: $deployment");
        $app->halt(404, $apiResponse->returnJson());
    }
    return true;
}

function validateForbiddenChars($app, $deployment, $regex, $key, $value) {
    if (preg_match_all($regex, $value, $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter: $key , detected forbidden characters " . implode('', array_unique($forbidden[0]))
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $apiResponse->setExtraResponseData('parameter-regex', $regex);
        $app->halt(404, $apiResponse->returnJson());
    }
    return true;
}

function validateInterval($app, $deployment, $key, $value, $min, $max) {
    if ($value < $min) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter value specified, value is less than minimum value"
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $apiResponse->setExtraResponseData('minimum', $min);
        $apiResponse->setExtraResponseData('maximum', $max);
        $app->halt(404, $apiResponse->returnJson());
    }
    elseif ($value > $max) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter value specified, value is greater than maximum value"
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $apiResponse->setExtraResponseData('minimum', $min);
        $apiResponse->setExtraResponseData('maximum', $max);
        $app->halt(404, $apiResponse->returnJson());
    }
    return true;
}

function validateBinary($app, $deployment, $key, $value) {
    if (preg_match_all('/[^01]/s', $value, $forbidden)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter value specified, parameter respects binary value only [0/1]"
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $app->halt(404, $apiResponse->returnJson());
    }
    return true;
}

function validateOptions($app, $deployment, $key, $value, array $validOpts, $implodeResults = false) {
    if (preg_match('/,/', $value)) {
        $options = preg_split('/\s?,\s?/', $value);
    }
    elseif (!is_array($value)) {
        $options = array($value);
    }
    else {
        $options = $value;
    }
    $invalidOptions = array_diff($options, $validOpts);
    if (!empty($invalidOptions)) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter value specified, unknown option value passed in with values"
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $apiResponse->setExtraResponseData('parameter-options', implode(',', $validOpts));
        $apiResponse->setExtraResponseData('parameter-invalid-options', implode(',', $invalidOptions));
        $app->halt(404, $apiResponse->returnJson());
    }
    if ($implodeResults === true) {
        return implode(',', $options);
    }
    else {
        return true;
    }
}

function validateUrl($app, $deployment, $key, $value) {
    if (filter_var($value, FILTER_VALIDATE_URL) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter value specified, value doesn't seem to be a valid url"
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $app->halt(404, $apiResponse->returnJson());
    }
    return true;
}

function validateEmail($app, $deployment, $key, $value) {
    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        $apiResponse = new APIViewData(1, $deployment,
            "Unable to use parameter value specified, value doesn't seem to be a valid email"
        );
        $apiResponse->setExtraResponseData('parameter', $key);
        $apiResponse->setExtraResponseData('parameter-value', $value);
        $app->halt(404, $apiResponse->returnJson());
    }
    return true;
}

function validateContacts($app, $deployment, array $info) {
    $contacts = false;
    $contact_groups = false;
    if ((isset($info['contacts'])) && (!empty($info['contacts']))) {
        $contacts = true;
    }
    if ((isset($info['contact_groups'])) && (!empty($info['contact_groups']))) {
        $contact_groups = true;
    }
    if (($contacts === true) || ($contact_groups === true)) {
        return true;
    }
    $apiResponse = new APIViewData(1, $deployment,
        "Unable to detect either contacts or contact_group parameter"
    );
    $app->halt(404, $apiResponse->returnJson());
}

function httpCache($app, $sec = 30) {
    $app->response()->header('cache-control', 'private, max-age='.$sec);
    $app->response()->header('expires', date('r', time()+$sec));
    $app->response()->header('pragma', 'cache');
}

// Setup our application's environment
$app->config(
    array(
        'debug' => true,
    )
);

// Setup Lazy Loader for Routes
$app->hook('slim.before.router', function () use($app)
{
    $uri = $app->request()->getResourceUri();
    if (($k = strpos($uri, "/", 1)) === false) {
        $controller = $uri;
    } else {
        $controller = '/' . strtok($uri, '/');
        $controller .= '/' . strtok('/');
    }

    switch ($controller) {
        case "/sapi/configs":
            require_once(BASE_PATH . "/routes/configs.route.php"); break;
        case "/sapi/consumer":
            require_once(BASE_PATH . "/routes/consumer.route.php"); break;
        case "/sapi/commands":
        case "/sapi/command":
            require_once(BASE_PATH . "/routes/command.route.php"); break;
        case "/sapi/contacts":
        case "/sapi/contact":
            require_once(BASE_PATH . "/routes/contact.route.php"); break;
        case "/sapi/contactgroups":
        case "/sapi/contactgroup":
            require_once(BASE_PATH . "/routes/contactgroup.route.php"); break;
        case "/sapi/contacttemplates":
        case "/sapi/contacttemplate":
            require_once(BASE_PATH . "/routes/contacttemplate.route.php"); break;
        case "/sapi/deployment":
            require_once(BASE_PATH . "/routes/deployment.route.php"); break;
        case "/sapi/event":
            require_once(BASE_PATH . "/routes/event.route.php"); break;
        case "/sapi/hostgroups":
        case "/sapi/hostgroup":
            require_once(BASE_PATH . "/routes/hostgroup.route.php"); break;
        case "/sapi/hosttemplates":
        case "/sapi/hosttemplate":
            require_once(BASE_PATH . "/routes/hosttemplate.route.php"); break;
        case "/sapi/matrix":
            require_once(BASE_PATH . "/routes/matrix.route.php"); break;
        case "/sapi/nagiospluginsmeta":
        case "/sapi/nagiosplugins":
        case "/sapi/nagiosplugin":
            require_once(BASE_PATH . "/routes/nagiosplugin.route.php"); break;
        case "/sapi/nrpecfg":
            require_once(BASE_PATH . "/routes/nrpecfg.route.php"); break;
        case "/sapi/nrpecmds":
        case "/sapi/nrpecmd":
            require_once(BASE_PATH . "/routes/nrpecmd.route.php"); break;
        case "/sapi/nrpepluginsmeta":
        case "/sapi/nrpeplugins":
        case "/sapi/nrpeplugin":
            require_once(BASE_PATH . "/routes/nrpeplugin.route.php"); break;
        case "/sapi/supnrpecfg":
            require_once(BASE_PATH . "/routes/supnrpecfg.route.php"); break;
        case "/sapi/supnrpepluginsmeta":
        case "/sapi/supnrpeplugins":
        case "/sapi/supnrpeplugin":
            require_once(BASE_PATH . "/routes/supnrpeplugin.route.php"); break;
        case "/sapi/services":
        case "/sapi/service":
            require_once(BASE_PATH . "/routes/service.route.php"); break;
        case "/sapi/servicedependencies":
        case "/sapi/servicedependency":
            require_once(BASE_PATH . "/routes/servicedependency.route.php"); break;
        case "/sapi/serviceescalations":
        case "/sapi/serviceescalation":
            require_once(BASE_PATH . "/routes/serviceescalation.route.php"); break;
        case "/sapi/servicegroups":
        case "/sapi/servicegroup":
            require_once(BASE_PATH . "/routes/servicegroup.route.php"); break;
        case "/sapi/servicetemplates":
        case "/sapi/servicetemplate":
            require_once(BASE_PATH . "/routes/servicetemplate.route.php"); break;
        case "/sapi/timeperiodsmeta":
        case "/sapi/timeperiods":
        case "/sapi/timeperiod":
            require_once(BASE_PATH . "/routes/timeperiod.route.php"); break;
        case "/api/getMGCfg":
        case "/api/getNagiosCfg":
        case "/api/getNRPECfg":
        case "/api/getSupNRPECfg":
        case "/api/getNRPEPlugin":
        case "/api/getSupNRPEPlugin":
        case "/api/getRouterVM":
        case "/api/getNagiosPlugin":
        case "/api/getNagiosPlugins":
            require_once(BASE_PATH . "/routes/apiv1.route.php"); break;
        default:
            break;
    }
});

$app->contentType('application/json');

$app->notFound(function () use ($app) {
    $request = $app->request();
    $headers = $request->headers();
    $uri = $request->getResourceUri();
    $apiResponse = new APIViewData(
        1,
        false,
        "The page you are looking for could not be found. Check the address to ensure your URL is spelled correctly..."
    );
    $apiResponse->setExtraResponseData(
        'url',
        $request->headers('X_FORWARDED_PROTO') . '://' . $request->headers('HOST') . $request->getRootUri() . $request->getResourceUri()
    );
    $app->halt('404', $apiResponse->returnJson());
});

// Initial Dummy Routes

// Leave the / on the end of /sapi/ due to rewrite engine, otherwise requests to /sapi break
$app->get('/sapi/', function () use ($app) {
    $msg = "Welcome to /sapi/ ... What can we help you with?";
    $apiResponse = new APIViewData(0, false, $msg);
    $apiResponse->printJson();
})->name('saigon-api');

$app->get('/sapi/version', function () use ($app) {
    $apiResponse = new APIViewData(0, false, API_VERSION . " (alpha/beta/charlie/delta/echo/use at your own risk)");
    $apiResponse->printJson();
})->name('saigon-api-version');

$app->run();
