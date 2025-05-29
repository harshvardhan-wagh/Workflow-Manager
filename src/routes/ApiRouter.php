<?php

namespace WorkflowManager\Routes;

use WorkflowManager\Helpers\Response;
use WorkflowManager\Helpers\Request;

class ApiRouter
{
    public function dispatch()
    {

        $ip = $this->getClientIp();
         // Get input directly
        $postData = $_POST;
        $rawData = file_get_contents('php://input');
        $jsonData = json_decode($rawData, true) ?? [];
        $getData = $_GET;
        
        // Choose which input to use
        $input = !empty($postData) ? $postData : $jsonData;
        // $input = Request::input();
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $normalizedUri = str_replace($scriptName, '', $uri);

        // Enhanced logging with input data
        $this->logApiRequest($ip, $input['user']['employee_id'] ?? 'unknown', $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'], $input);
       
        if (WorkflowRoutes::handle($normalizedUri, $method)) return;
        if (WorkflowInstanceRoutes::handle($normalizedUri, $method)) return;

        // Route not found
        Response::error('Route not found', 404);
    }

    // function logApiRequest($ip, $userEmpId, $method, $uri) {
      
    //     $time = date('Y-m-d H:i:s');
    //     $logLine = "[$time] IP: $ip | $method $uri | User: $userEmpId\n";
    //     file_put_contents(__DIR__ . '/../logs/api.log', $logLine, FILE_APPEND);
    // }

    function logApiRequest($ip, $userEmpId, $method, $uri, $inputData = null) {
        // Create logs directory if it doesn't exist
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $time = date('Y-m-d H:i:s');
        $logFile = $logDir . '/api.log';
        
        // Start log entry
        $logEntry = "\n" . str_repeat('=', 100) . "\n";
        $logEntry .= "[$time] API REQUEST\n";
        $logEntry .= "IP: $ip\n";
        $logEntry .= "Method: $method\n";
        $logEntry .= "URI: $uri\n";
        $logEntry .= "User ID: $userEmpId\n";
        
        // Log headers
        $headers = getallheaders();
        if ($headers) {
            $logEntry .= "Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";
        }
        
        // Log content type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? 'not set';
        $logEntry .= "Content-Type: $contentType\n";
        
        // Log different types of input data
        if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
            // POST form data
            if (!empty($_POST)) {
                $logEntry .= "POST Form Data: " . json_encode($_POST, JSON_PRETTY_PRINT) . "\n";
            }
            
            // Raw request body
            $rawData = file_get_contents('php://input');
            if (!empty($rawData)) {
                $logEntry .= "Raw Request Body: " . $rawData . "\n";
            }
            
            // Processed input data
            if ($inputData !== null && !empty($inputData)) {
                $logEntry .= "Processed Input: " . json_encode($inputData, JSON_PRETTY_PRINT) . "\n";
            }
        }
        
        // Log GET parameters
        if (!empty($_GET)) {
            $logEntry .= "GET Parameters: " . json_encode($_GET, JSON_PRETTY_PRINT) . "\n";
        }
        
        $logEntry .= str_repeat('=', 100) . "\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    function getClientIp() {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_REAL_IP'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
        if ($ip === '::1') return '127.0.0.1'; // normalize localhost IPv6
    
        return $ip;
    }
    
    
}
