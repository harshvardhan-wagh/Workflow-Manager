<?php
namespace WorkflowManager\Helpers;

class Response
{
    public static function success($data = [], int $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data'   => $data,
        ]);
        exit;
    }

    public static function error($message, int $code = 400)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'error'  => [
                'code'    => $code,
                'message' => $message,
            ],
        ]);
        exit;
    }
}




