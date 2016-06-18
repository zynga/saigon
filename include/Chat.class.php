<?php

class Chat
{

    protected static $init = false;
    protected static $module;

    public static function init()
    {
        if (CHAT_INTEGRATION === true) {
            $cmodule = CHAT_MODULE;
            $chat_module = new $cmodule();
            self::$module = $chat_module;
            self::$init = true;
        }
    }

    /*
     * This is used by the Web/API and Saigon Tester only, otherwise use messageByRooms
     */
    public static function messageByDeployment(
        $deployment, $message, $color = 'yellow', $notify = false
    ) {
        if (CHAT_INTEGRATION === false) return;
        if (self::$init === false) self::init();
        if ((isset($_SERVER['PHP_AUTH_USER'])) && (!empty($_SERVER['PHP_AUTH_USER']))) {
            $message = "User: " . $_SERVER['PHP_AUTH_USER'] . " $message";
        }
        $miscDeploymentInfo = RevDeploy::getDeploymentMiscSettings($deployment);
        if (
            (isset($miscDeploymentInfo['chat_rooms']))
            && (!empty($miscDeploymentInfo['chat_rooms']))
        ) {
            self::$module->messageByRooms(
                $miscDeploymentInfo['chat_rooms'], $message, $color, $notify
            );
        }
    }

    public static function messageByRooms($rooms, $message, $color = 'yellow', $notify = false)
    {
        if (CHAT_INTEGRATION === false) return;
        if (self::$init === false) self::init();
        if ((isset($_SERVER['PHP_AUTH_USER'])) && (!empty($_SERVER['PHP_AUTH_USER']))) {
            $message = "User: " . $_SERVER['PHP_AUTH_USER'] . " $message";
        }
        self::$module->messageByRooms($rooms, $message, $color, $notify);
    }

}

