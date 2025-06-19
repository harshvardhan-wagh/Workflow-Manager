<?php

// Handle CORS before any other processing
function handleCORS() {
    // Define allowed origins (customize as needed)
    $allowed_origins = [
        'http://localhost:3000',
        'http://localhost:8080',
        'https://yourdomain.com',
        // Add your frontend domains here
    ];
    
    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    
    // For development, you can temporarily allow all origins
    // Remove this line in production and use the specific origins above
    header("Access-Control-Allow-Origin: *");
    
    // For production, use this instead:
    /*
    if (in_array($origin, $allowed_origins)) {
        header("Access-Control-Allow-Origin: $origin");
    }
    */
    
    // Set other CORS headers
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept, Origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 3600");
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Apply CORS headers
handleCORS();

// Your normal bootstrap
require_once __DIR__ . '/../vendor/autoload.php';

use WorkflowManager\Routes\ApiRouter;

$router = new ApiRouter();
$router->dispatch();