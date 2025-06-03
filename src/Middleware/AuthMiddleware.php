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

    public static function user(array $input)
    {
         // TODO Letter need to check user with session variable that user is logged in or not 

        // Check if the user is provided in the input
        if (isset($input['user'])) { 
            return $input['user'];
        }
      
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'User not authenticated']);
        exit;
    }
}
