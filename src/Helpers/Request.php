<?php

namespace WorkflowManager\Helpers;

class Request
{
    public static function input(): array
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    public static function header(string $key): ?string
    {
        $headers = getallheaders();
        return $headers[$key] ?? null;
    }
    
}
