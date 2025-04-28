<?php

namespace WorkflowManager\Helpers;

class Response
{
    public static function json($data, int $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function error($message, int $code = 400)
    {
        self::json(['status' => 'error', 'message' => $message], $code);
    }
}
