<?php

namespace WorkflowManager\Helpers;

class Response
{
    /**
     * Return a success response.
     *
     * @param string $message Success message.
     * @param mixed $data Additional data (optional).
     * @param int $statusCode HTTP status code (default: 200).
     * @return void
     */
    public static function success($message, $data = [], $statusCode = 200)
    {
        $response = [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];

        self::jsonResponse($response, $statusCode);
    }

    /**
     * Return an error response.
     *
     * @param string $message Error message.
     * @param mixed $data Additional error details (optional).
     * @param int $statusCode HTTP status code (default: 400).
     * @return void
     */
    public static function error($message, $data = [], $statusCode = 400)
    {
        
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => $data,  
        ];

        // Send the JSON response with the appropriate status code
        self::jsonResponse($response, $statusCode);
    }

    /**
     * Send a JSON response with the specified HTTP status code.
     *
     * @param array $response Response data.
     * @param int $statusCode HTTP status code.
     * @return void
     */
    private static function jsonResponse($response, $statusCode)
    {
        // Set content-type header to application/json
        header('Content-Type: application/json');
        // Set the HTTP response code
        http_response_code($statusCode);

        // Output the response as a JSON object
        echo json_encode($response);
        exit;  // Ensures the script stops after sending the response
    }
}
