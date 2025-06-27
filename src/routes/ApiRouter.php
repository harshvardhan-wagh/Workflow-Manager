<?php

namespace WorkflowManager\Routes;

use WorkflowManager\Helpers\Response;
use WorkflowManager\Helpers\Request;

class ApiRouter
{
    public function dispatch()
    {
        $ip = $this->getClientIp();
        $postData = $_POST;
        $rawData = file_get_contents('php://input');
        $jsonData = json_decode($rawData, true) ?? [];
        $getData = $_GET;
        
        $input = !empty($postData) ? $postData : $jsonData;
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $normalizedUri = str_replace($scriptName, '', $uri);

        // Simple access logging
        $this->logApiAccess($ip, $input['user']['employee_id'] ?? 'unknown', $method, $normalizedUri);
        
        // Detailed request logging (only when needed)
        if ($this->shouldLogDetails($method, $normalizedUri)) {
            $this->logRequestDetails($ip, $input['user']['employee_id'] ?? 'unknown', $method, $normalizedUri, $input);
        }
       
        if (LdapLoginRoutes::handle($normalizedUri, $method)) return;
        if (WorkflowRoutes::handle($normalizedUri, $method)) return;
        if (WorkflowInstanceRoutes::handle($normalizedUri, $method)) return;

        Response::error('Route not found', 404);
    }

    /**
     * Simple access log - one line per request
     */
    private function logApiAccess($ip, $userEmpId, $method, $uri) {
        $logDir = $this->ensureLogDirectory();
        $time = date('Y-m-d H:i:s');
        $logLine = "[$time] $ip | $userEmpId | $method $uri\n";
        file_put_contents($logDir . '/access.log', $logLine, FILE_APPEND);
    }

    /**
     * Detailed request logging - separate file
     */
    private function logRequestDetails($ip, $userEmpId, $method, $uri, $inputData = null) {
        $logDir = $this->ensureLogDirectory();
        $time = date('Y-m-d H:i:s');
        $logFile = $logDir . '/requests.log';
        
        $logEntry = "\n" . str_repeat('-', 50) . "\n";
        $logEntry .= "[$time] $method $uri\n";
        $logEntry .= "IP: $ip | User: $userEmpId\n";
        
        // Only log essential headers
        $essentialHeaders = ['Content-Type', 'Authorization', 'User-Agent'];
        $headers = getallheaders();
        foreach ($essentialHeaders as $header) {
            if (isset($headers[$header])) {
                $logEntry .= "$header: {$headers[$header]}\n";
            }
        }
        
        // Log input data (sanitized)
        if ($inputData && !empty($inputData)) {
            $sanitizedInput = $this->sanitizeLogData($inputData);
            $logEntry .= "Input: " . json_encode($sanitizedInput, JSON_UNESCAPED_SLASHES) . "\n";
        }
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    /**
     * Error logging - separate file
     */
    public function logError($message, $context = []) {
        $logDir = $this->ensureLogDirectory();
        $time = date('Y-m-d H:i:s');
        $logLine = "[$time] ERROR: $message";
        if (!empty($context)) {
            $logLine .= " | Context: " . json_encode($context);
        }
        $logLine .= "\n";
        file_put_contents($logDir . '/errors.log', $logLine, FILE_APPEND);
    }

    /**
     * Determine if we should log detailed request info
     */
    private function shouldLogDetails($method, $uri) {
        // Only log details for:
        // 1. POST/PUT/PATCH requests
        // 2. Specific endpoints that need monitoring
        // 3. During development/debugging
        
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }
        
        // Log details for specific endpoints
        $detailedEndpoints = ['/api/workflow/create', '/api/workflow/update'];
        foreach ($detailedEndpoints as $endpoint) {
            if (strpos($uri, $endpoint) !== false) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Remove sensitive data from logs
     */
    private function sanitizeLogData($data) {
        $sensitiveKeys = ['password', 'token', 'api_key', 'secret', 'auth'];
        
        return $this->recursiveSanitize($data, $sensitiveKeys);
    }

    private function recursiveSanitize($data, $sensitiveKeys) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                foreach ($sensitiveKeys as $sensitiveKey) {
                    if (stripos($key, $sensitiveKey) !== false) {
                        $data[$key] = '[REDACTED]';
                        continue 2;
                    }
                }
                if (is_array($value)) {
                    $data[$key] = $this->recursiveSanitize($value, $sensitiveKeys);
                }
            }
        }
        return $data;
    }

    /**
     * Ensure log directory exists
     */
    private function ensureLogDirectory() {
        $logDir = __DIR__ . '/../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        return $logDir;
    }

    /**
     * Get client IP address
     */
    private function getClientIp() {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_X_REAL_IP'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    
        if ($ip === '::1') return '127.0.0.1';
    
        return $ip;
    }

    /**
     * Daily log rotation - call this periodically
     */
    public function rotateLogsIfNeeded() {
        $logDir = $this->ensureLogDirectory();
        $logFiles = ['access.log', 'requests.log', 'errors.log'];
        
        foreach ($logFiles as $logFile) {
            $filePath = $logDir . '/' . $logFile;
            if (file_exists($filePath) && filesize($filePath) > 10 * 1024 * 1024) { // 10MB
                $archiveName = $logDir . '/' . pathinfo($logFile, PATHINFO_FILENAME) . '_' . date('Y-m-d') . '.log';
                rename($filePath, $archiveName);
            }
        }
    }
}