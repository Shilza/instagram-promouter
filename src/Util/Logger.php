<?php

namespace Util;

class Logger
{
    private static $filePath;
    private static $logToConcole = false;

    public static function setFilePath($filePath)
    {
        static::$filePath = $filePath;
    }

    public static function setLogToConsoleFlag($logToConsole){
        static::$logToConcole = $logToConsole;
    }

    public static function info($logInfo)
    {
        static::log("[INFO]", $logInfo);
    }

    public static function error($logInfo)
    {
        static::log("[ERROR]", $logInfo);
    }

    public static function trace($logInfo)
    {
        static::log("[TRACE]", $logInfo);
    }

    public static function debug($logInfo)
    {
        static::log("[DEBUG]", $logInfo);
    }

    private static function log($tag, $logInfo)
    {
        $logData = PHP_EOL . $tag . " " . date(DATE_RFC822) . " --> " . $logInfo;
        if (isset(static::$filePath))
            file_put_contents(
                static::$filePath . ".log",
                $logData,
                FILE_APPEND
            );

        if(static::$logToConcole)
            echo $logData;
    }
}