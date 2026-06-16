<?php

require_once __DIR__ . '/env.php';

define('FAPSHI_USER', $_ENV['FAPSHI_USER'] ?? '');
define('FAPSHI_KEY', $_ENV['FAPSHI_KEY'] ?? '');
define('FAPSHI_SANDBOX', filter_var($_ENV['FAPSHI_SANDBOX'] ?? 'false', FILTER_VALIDATE_BOOLEAN));

define('FAPSHI_BASE_URL', FAPSHI_SANDBOX ? 'https://live.fapshi.com' : 'https://live.fapshi.com'); // Note: Adjust sandbox URL if Fapshi provides a specific one. Using live for both if sandbox works with same domain.

function callFapshi($endpoint, $payload = [], $method = 'POST') {
    $url = FAPSHI_BASE_URL . $endpoint;
    
    $headers = [
        'apiuser: ' . FAPSHI_USER,
        'apikey: ' . FAPSHI_KEY,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    } else {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    }
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // In production with real SSL, remove these or set to true
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode,
        'body' => json_decode($response, true) ?? $response
    ];
}
