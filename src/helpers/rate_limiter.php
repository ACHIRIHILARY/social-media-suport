<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/sanitize.php';

function checkRateLimit($action, $maxAttempts, $windowSeconds) {
    $pdo = getDbConnection();
    $ip = getIpAddress();
    
    // Clean up old records
    $stmt = $pdo->prepare("DELETE FROM rate_limit WHERE window_start < (NOW() - INTERVAL ? SECOND)");
    $stmt->execute([$windowSeconds]);
    
    // Check current count
    $stmt = $pdo->prepare("SELECT attempt_count FROM rate_limit WHERE ip_address = INET6_ATON(?) AND action = ?");
    $stmt->execute([$ip, $action]);
    $row = $stmt->fetch();
    
    if ($row && $row['attempt_count'] >= $maxAttempts) {
        http_response_code(429);
        die("Too many requests. Please try again later.");
    }
    
    // Increment or insert
    $stmt = $pdo->prepare("
        INSERT INTO rate_limit (ip_address, action, attempt_count) 
        VALUES (INET6_ATON(?), ?, 1)
        ON DUPLICATE KEY UPDATE attempt_count = attempt_count + 1
    ");
    $stmt->execute([$ip, $action]);
}
