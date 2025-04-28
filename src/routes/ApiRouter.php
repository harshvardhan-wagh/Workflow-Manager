<?php

namespace WorkflowManager\Routes;

use WorkflowManager\Helpers\Response;
use WorkflowManager\Helpers\Request;

class ApiRouter
{
    public function dispatch()
    {

        $ip =$this->getClientIp();
        $input = Request::input();
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $normalizedUri = str_replace($scriptName, '', $uri);

        $this->logApiRequest($ip , $input['user']['employee_id'], $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

        if (WorkflowRoutes::handle($normalizedUri, $method)) return;
        if (WorkflowInstanceRoutes::handle($normalizedUri, $method)) return;

        // Route not found
        Response::error('Route not found', 404);
    }

    function logApiRequest($ip, $userEmpId, $method, $uri) {
      
        $time = date('Y-m-d H:i:s');
        $logLine = "[$time] IP: $ip | $method $uri | User: $userEmpId\n";
        file_put_contents(__DIR__ . '/../logs/api.log', $logLine, FILE_APPEND);
    }

    function getClientIp() {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_REAL_IP'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
        if ($ip === '::1') return '127.0.0.1'; // normalize localhost IPv6
    
        return $ip;
    }
    
    

    
    
}
