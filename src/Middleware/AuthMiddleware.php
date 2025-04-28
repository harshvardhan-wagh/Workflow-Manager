<?php

namespace WorkflowManager\Middleware;

class AuthMiddleware
{
    public static function verify()
    {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? '';

        $config = require __DIR__ . '/../Config/config.php';
        $expected = 'Bearer ' . $config['auth_token'];

        if ($auth !== $expected) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
    }
}
