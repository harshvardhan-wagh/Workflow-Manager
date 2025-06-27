<?php

namespace WorkflowManager\Routes;
use WorkflowManager\ApiControllers\LdapLoginController;
use WorkflowManager\Services\LdapLoginService;
use WorkflowManager\Helpers\Request;    
use WorkflowManager\Helpers\Response;
use WorkflowManager\Middleware\AuthMiddleware;

class LdapLoginRoutes
{
    public static function handle($uri, $method)
    {
        if ($uri === '/api/ldap/login' && $method === 'POST') {

            // AuthMiddleware::verify();
            $input = Request::input();
            $user;
            foreach ($input as $key => $value) {
                $user[$key] = $value;
            }
            // $user  = AuthMiddleware::user($input);

            try {
                $ldapLoginService = new LdapLoginService();
                $controller = new LdapLoginController($ldapLoginService);
                $result = $controller->login($input);
                Response::success([
                    'message' => 'Login successful',
                    'user' => $result,
                ], 200);
            } catch (\Throwable $e) {
                Response::error('An error occurred during login: ' . $e->getMessage(), 500);
            }

            return true;
        }

        if ($uri === '/api/ldap/logout' && $method === 'POST') {
            AuthMiddleware::verify();
            $input = Request::input();
            $user  = AuthMiddleware::user($input);

            try {
                $ldapLoginService = new LdapLoginService();
                $controller = new LdapLoginController($ldapLoginService);
                $controller->logout();

                Response::success(['message' => 'Logout successful'], 200);
            } catch (\Throwable $e) {
                Response::error('An error occurred during logout: ' . $e->getMessage(), 500);
            }

            return true;
        }

        return false; // No route matched
    }
}