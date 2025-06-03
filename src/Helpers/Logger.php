<?php

namespace WorkflowManager\Helpers;

class Logger
{
    public static function error($message, $context = [])
    {
        $logFile = __DIR__ . '/../logs/error.log';

        // Make sure the logs directory exists
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $contextJson = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $entry = "[{$timestamp}] ERROR: {$message} | Context: {$contextJson}" . PHP_EOL;

        error_log($entry, 3, $logFile);
    }
}
