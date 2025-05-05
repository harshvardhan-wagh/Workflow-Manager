<?php

use PHPUnit\Framework\TestCase;;

class RouteTest extends TestCase
{
    public function testInvalidRouteReturn404()
    {
        $ch = curl_init('http://localhost/api/invalid-route');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); 
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(404, $httpCode, 'Expected 404 Not Found for invalid route');

    }

    // public function testValidRouteReturn200()
    // {
    //     $ch = curl_init('http://localhost/api/invalid-route');
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //     curl_setopt($ch, CURLOPT_HEADER, true);
    //     curl_setopt($ch, CURLOPT_NOBODY, true);
    //     curl_exec($ch);
    //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    //     curl_close($ch);

    //     $this->assertEquals(200, $httpCode);

    // }
}