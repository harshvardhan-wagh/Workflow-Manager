<?php

namespace WorkflowManager\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class AuthMiddleware
{
    public static function verify()
    {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        // Compatible version of starts_with
        if (!$authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            self::unauthorized('Missing or malformed Authorization header');
        }

        $token = trim(str_replace('Bearer ', '', $authHeader));

        $config = require __DIR__ . '/../Config/config.php';
        $secret = $config['jwt_secret'];

        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Save user info globally
            $GLOBALS['authenticated_user'] = (array) $decoded->user;
            return true;

        } catch (ExpiredException $e) {
            self::unauthorized('Token expired');
        } catch (\Throwable $e) {
            self::unauthorized('Invalid token: ' . $e->getMessage());
        }
    }

    public static function user()
    {
        if (isset($GLOBALS['authenticated_user'])) {
            return $GLOBALS['authenticated_user'];
        }

        self::unauthorized('User not authenticated');
    }

    private static function unauthorized($message)
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}
